<?php

namespace Database\Seeders;

use App\Models\ChatbotMessage;
use App\Models\ChatbotMessageSuggestion;
use App\Models\ChatbotRoom;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        if (ChatbotRoom::exists()) {
            return;
        }

        $sessions = [
            [2, 'Sparkle Home Services', [
                ['user', 'I need someone to deep clean my apartment this weekend.'],
                ['bot', 'I found a few highly rated cleaning providers available this weekend.'],
                ['user', 'Which one has the best rating?'],
                ['bot', 'Sparkle Home Services has strong reviews for deep cleaning. Want me to suggest them?'],
            ], ['Sparkle Home Services', 'CrystalClean Co.']],
            [3, 'Bright Spark Electric', [
                ['user', 'My lights keep flickering, who can help?'],
                ['bot', 'That sounds like an electrical issue. Here are providers who handle wiring and lighting.'],
                ['user', 'Can someone come today?'],
                ['bot', 'Bright Spark Electric usually responds within the hour and covers Jeddah.'],
            ], ['Bright Spark Electric', 'PowerFix Electrical']],
            [8, 'QuickCourier', [
                ['user', 'I need to send a package across town quickly.'],
                ['bot', 'Got it, here are couriers available for same-day delivery.'],
                ['user', 'How fast can they pick it up?'],
                ['bot', 'QuickCourier typically picks up within 2 hours.'],
            ], ['QuickCourier']],
        ];

        foreach ($sessions as [$customerIndex, $businessName, $messages, $suggestionBusinessNames]) {
            $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
            $provider = Provider::where('business_name', $businessName)->firstOrFail();

            $room = ChatbotRoom::create([
                'user_id' => $customer->id,
                'provider_id' => $provider->id,
            ]);

            $lastBotMessage = null;
            foreach ($messages as [$role, $text]) {
                $message = ChatbotMessage::create([
                    'chatbot_room_id' => $room->id,
                    'role' => $role,
                    'message' => $text,
                ]);

                if ($role === 'bot') {
                    $lastBotMessage = $message;
                }
            }

            foreach ($suggestionBusinessNames as $suggestedBusinessName) {
                $suggestedProvider = Provider::where('business_name', $suggestedBusinessName)->first();
                if ($suggestedProvider && $lastBotMessage) {
                    ChatbotMessageSuggestion::create([
                        'chatbot_message_id' => $lastBotMessage->id,
                        'provider_id' => $suggestedProvider->id,
                    ]);
                }
            }
        }
    }
}
