<?php

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\ChatbotMessage;
use App\Models\ChatbotMessageSuggestion;
use App\Models\ChatbotRoom;
use App\Models\City;
use App\Models\Faq;
use App\Models\Provider;
use App\Models\SubCategory;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatbotService
{
    private const DIRECTIONS = ['general', 'rfq', 'providers'];
    private const MAX_HISTORY = 20;
    private const MAX_TOOL_ROUNDS = 3;

    private array $collectedProviderIds = [];
    private ?int $createdQuotationId = null;

    public function __construct(
        private readonly OpenAiClient $openAi,
        private readonly QuotationService $quotationService,
    ) {
    }

    /**
     * Handle one user turn: classify direction, act on it, and persist the bot's reply.
     *
     * @return array{message: ChatbotMessage, requires_auth: bool}
     */
    public function reply(ChatbotRoom $room, string $userMessage, ?User $user): array
    {
        $this->collectedProviderIds = [];
        $this->createdQuotationId = null;

        ChatbotMessage::create([
            'chatbot_room_id' => $room->id,
            'role' => 'user',
            'message' => $userMessage,
        ]);

        $systemPrompt = $this->buildSystemPrompt($user);
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$this->historyForOpenAi($room),
        ];

        try {
            $direction = $this->classifyDirection($messages);
        } catch (Throwable $e) {
            Log::error('Chatbot direction classification failed', ['error' => $e->getMessage()]);

            return $this->fail($room, 'Sorry, the assistant is temporarily unavailable. Please try again shortly.');
        }

        $requiresAuth = $direction === 'rfq' && !$user;

        try {
            if ($requiresAuth) {
                $finalContent = $this->replyText($messages);
            } else {
                $finalContent = $this->runToolLoop($messages, $direction, $user);
            }
        } catch (Throwable $e) {
            Log::error('Chatbot reply generation failed', ['error' => $e->getMessage()]);

            return $this->fail($room, 'Sorry, the assistant is temporarily unavailable. Please try again shortly.');
        }

        $botMessage = ChatbotMessage::create([
            'chatbot_room_id' => $room->id,
            'role' => 'bot',
            'direction' => $direction,
            'message' => $finalContent,
            'quotation_id' => $this->createdQuotationId,
        ]);

        foreach (array_unique($this->collectedProviderIds) as $providerId) {
            ChatbotMessageSuggestion::create([
                'chatbot_message_id' => $botMessage->id,
                'provider_id' => $providerId,
            ]);
        }

        $room->update(['direction' => $direction]);

        return ['message' => $botMessage, 'requires_auth' => $requiresAuth];
    }

    private function fail(ChatbotRoom $room, string $message): array
    {
        $botMessage = ChatbotMessage::create([
            'chatbot_room_id' => $room->id,
            'role' => 'bot',
            'direction' => $room->direction,
            'message' => $message,
        ]);

        return ['message' => $botMessage, 'requires_auth' => false];
    }

    private function historyForOpenAi(ChatbotRoom $room): array
    {
        return $room->messages()
            ->latest('id')
            ->take(self::MAX_HISTORY)
            ->get()
            ->reverse()
            ->map(fn (ChatbotMessage $m) => [
                'role' => $m->role === 'bot' ? 'assistant' : 'user',
                'content' => (string) $m->message,
            ])
            ->values()
            ->all();
    }

    /**
     * Round 1: force the model to classify the current direction for this turn.
     */
    private function classifyDirection(array &$messages): string
    {
        $response = $this->openAi->chat($messages, [$this->setDirectionTool()], [
            'type' => 'function',
            'function' => ['name' => 'set_direction'],
        ]);

        $direction = 'general';
        $toolCall = $response['tool_calls'][0] ?? null;

        if ($toolCall) {
            $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];
            $direction = in_array($arguments['direction'] ?? null, self::DIRECTIONS, true)
                ? $arguments['direction']
                : 'general';

            $messages[] = [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => [$toolCall],
            ];
            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content' => json_encode(['direction' => $direction]),
            ];
        }

        return $direction;
    }

    /**
     * A plain, tool-free reply (used for the "please log in" guest/RFQ case).
     */
    private function replyText(array $messages): string
    {
        $response = $this->openAi->chat($messages, [], 'none');

        return $response['content'] ?? '';
    }

    /**
     * Round 2+: let the model act (search providers / create an RFQ) or just answer directly.
     */
    private function runToolLoop(array $messages, string $direction, ?User $user): string
    {
        $tools = match (true) {
            $direction === 'providers' => [$this->searchProvidersTool()],
            $direction === 'rfq' && $user && $user->user_type === 'customer' => [$this->createRfqTool()],
            default => [],
        };

        for ($round = 0; $round < self::MAX_TOOL_ROUNDS; $round++) {
            $response = $this->openAi->chat($messages, $tools, 'auto');

            $toolCalls = $response['tool_calls'] ?? [];
            if (empty($toolCalls)) {
                return $response['content'] ?? '';
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => $toolCalls,
            ];

            foreach ($toolCalls as $toolCall) {
                $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];
                $result = match ($toolCall['function']['name']) {
                    'search_providers' => $this->handleSearchProviders($arguments),
                    'create_rfq' => $this->handleCreateRfq($arguments, $user),
                    default => ['error' => 'Unknown tool.'],
                };

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content' => json_encode($result),
                ];
            }
        }

        return $this->replyText($messages);
    }

    private function handleSearchProviders(array $args): array
    {
        $query = Provider::query()
            ->where('is_verified', true)
            ->with(['user', 'city', 'subCategories.subCategory']);

        if (!empty($args['sub_category_id'])) {
            $query->whereHas('subCategories', fn ($q) => $q->where('sub_category_id', $args['sub_category_id']));
        }

        if (!empty($args['category_id'])) {
            $query->whereHas('subCategories.subCategory', fn ($q) => $q->where('category_id', $args['category_id']));
        }

        if (!empty($args['city_id'])) {
            $query->where('city_id', $args['city_id']);
        }

        if (!empty($args['min_rating'])) {
            $query->whereHas('user', fn ($q) => $q->where('average_rating', '>=', $args['min_rating']));
        }

        if (!empty($args['keywords'])) {
            $keywords = $args['keywords'];
            $query->where(function ($q) use ($keywords) {
                $q->where('business_name', 'like', "%{$keywords}%")
                    ->orWhere('description', 'like', "%{$keywords}%");
            });
        }

        $providers = $query->orderByDesc(
            User::select('average_rating')->whereColumn('users.id', 'providers.user_id')
        )->take(5)->get();

        $this->collectedProviderIds = [
            ...$this->collectedProviderIds,
            ...$providers->pluck('id')->all(),
        ];

        return [
            'providers' => $providers->map(fn (Provider $p) => [
                'id' => $p->id,
                'business_name' => $p->business_name,
                'description' => $p->description,
                'city' => $p->city?->name_en,
                'rating' => (float) ($p->user->average_rating ?? 0),
                'availability_status' => $p->availability_status,
            ])->all(),
        ];
    }

    private function handleCreateRfq(array $args, ?User $user): array
    {
        if (!$user || $user->user_type !== 'customer') {
            return ['error' => 'Only authenticated customers can create a request for quotation.'];
        }

        if (empty($args['sub_category_id']) || !SubCategory::whereKey($args['sub_category_id'])->exists()) {
            return ['error' => 'A valid sub_category_id is required.'];
        }

        $subCategory = SubCategory::find($args['sub_category_id']);

        $quotation = $this->quotationService->create($user, [
            'category_id' => $args['category_id'] ?? $subCategory->category_id,
            'sub_category_id' => $subCategory->id,
            'title' => $args['title'] ?? null,
            'description' => $args['description'] ?? null,
            'address' => $args['address'] ?? null,
            'scheduled_at' => $args['scheduled_at'] ?? null,
            'price' => $args['price'] ?? null,
        ]);

        $this->createdQuotationId = $quotation->id;

        return [
            'status' => 'created',
            'quotation_id' => $quotation->id,
            'title' => $quotation->title,
        ];
    }

    private function buildSystemPrompt(?User $user): string
    {
        $categories = Category::with('subCategories')->where('is_active', true)->get()
            ->map(fn (Category $c) => sprintf(
                '- [%d] %s / %s: %s',
                $c->id,
                $c->name_en,
                $c->name_ar,
                $c->subCategories->map(fn (SubCategory $s) => "[{$s->id}] {$s->name_en}/{$s->name_ar}")->implode(', ')
            ))->implode("\n");

        $cities = City::where('is_active', true)->get()
            ->map(fn (City $c) => "[{$c->id}] {$c->name_en}/{$c->name_ar}")->implode(', ');

        $faqs = Faq::where('is_active', true)->orderBy('order')->get()
            ->map(fn (Faq $f) => "Q(en): {$f->question_en}\nA(en): {$f->answer_en}\nQ(ar): {$f->question_ar}\nA(ar): {$f->answer_ar}")
            ->implode("\n\n");

        $authState = $user
            ? "The user IS authenticated (name: {$user->name}, account type: {$user->user_type})."
            : 'The user is a GUEST (not authenticated).';

        return <<<PROMPT
            You are the KhidmaNow support assistant, a chat concierge for a services marketplace connecting
            customers with verified service providers (cleaning, electrical, delivery, etc.).

            On every user turn you must classify it into exactly one of three directions:
            1. "general" - general questions about the platform, how it works, policies, or anything answerable from the FAQ list below. Public, no login needed.
            2. "rfq" - the user wants help creating a Request For Quotation (RFQ) so providers can bid on a job. Requires the user to be logged in (a bearer token).
            3. "providers" - the user is looking for a service provider to hire directly. Public, no login needed.

            The current resolved direction is NOT locked - re-evaluate it every turn; the user may change topic.

            Behavior rules:
            - Always reply in the same language the user just wrote in (Arabic or English). Keep replies short, warm, and conversational.
            - Ask ONE clarifying question at a time until you have enough detail, then act.
            - Never invent providers, prices, or facts. Only state provider details returned by the search_providers tool, and only state an RFQ was created after the create_rfq tool succeeds.
            - For "general": answer using the FAQ list below as ground truth. If not covered, answer briefly and helpfully without inventing platform-specific policy.
            - For "providers": ask enough to know what service/category and (if relevant) city the user needs, then call search_providers. Summarize the returned providers briefly; do not list more than what was returned.
            - For "rfq": if the user is a GUEST, do not ask further questions - politely explain that creating a request for quotation requires signing in first, and stop there. If the user is authenticated as a customer, gather: what service (map to a sub-category below), a clear description of the problem/need, and optionally address/schedule/budget, then call create_rfq. If the authenticated user is not a customer account, explain that only customer accounts can create requests.

            Authentication state: {$authState}

            Service categories and sub-categories (use these ids when calling tools):
            {$categories}

            Cities (use these ids when calling tools):
            {$cities}

            FAQ knowledge base:
            {$faqs}
            PROMPT;
    }

    private function setDirectionTool(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'set_direction',
                'description' => 'Classify which of the three support directions this user turn belongs to.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'direction' => [
                            'type' => 'string',
                            'enum' => self::DIRECTIONS,
                        ],
                    ],
                    'required' => ['direction'],
                ],
            ],
        ];
    }

    private function searchProvidersTool(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'search_providers',
                'description' => 'Search verified service providers matching the customer\'s need.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'sub_category_id' => ['type' => 'integer'],
                        'category_id' => ['type' => 'integer'],
                        'city_id' => ['type' => 'integer'],
                        'min_rating' => ['type' => 'number'],
                        'keywords' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    private function createRfqTool(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'create_rfq',
                'description' => 'Create a Request For Quotation for the authenticated customer once enough detail has been gathered.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'sub_category_id' => ['type' => 'integer', 'description' => 'Required. Id from the sub-category list.'],
                        'category_id' => ['type' => 'integer'],
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string', 'description' => 'Required. Full description of what the customer needs.'],
                        'address' => ['type' => 'string'],
                        'scheduled_at' => ['type' => 'string', 'description' => 'ISO date/time if the customer gave a preferred schedule.'],
                        'price' => ['type' => 'number', 'description' => 'Customer budget if mentioned.'],
                    ],
                    'required' => ['sub_category_id', 'description'],
                ],
            ],
        ];
    }
}
