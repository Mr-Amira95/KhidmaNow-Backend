<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        if (ChatRoom::exists()) {
            return;
        }

        $pairs = [
            [0, 'Sparkle Home Services'],
            [1, 'FastFix Plumbing'],
            [2, 'Sparkle Home Services'],
            [3, 'Bright Spark Electric'],
            [4, 'CoolAir AC Services'],
            [5, 'Bright Spark Electric'],
            [6, 'Speedy Movers'],
            [7, 'CrystalClean Co.'],
            [8, 'QuickCourier'],
            [9, 'PowerFix Electrical'],
        ];

        $script = [
            ['customer', "Hi, is your team available this week?"],
            ['provider', "Hello! Yes, we have availability. What service do you need?"],
            ['customer', "I'd like a quote for the job I posted."],
            ['provider', "Sure, we can send someone over to inspect and give you a firm price."],
            ['customer', "Great, thank you! Please let me know the earliest slot."],
            ['provider', "We can do tomorrow morning at 10 AM if that works for you."],
        ];

        foreach ($pairs as $i => [$customerIndex, $businessName]) {
            $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
            $provider = Provider::where('business_name', $businessName)->with('user')->firstOrFail();

            $chatRoom = ChatRoom::create([
                'user_id' => $customer->id,
                'provider_id' => $provider->id,
            ]);

            $start = Carbon::now()->subDays(15 - $i)->setTime(11, 0);
            $lastMessageAt = $start;

            foreach ($script as $j => [$speaker, $text]) {
                $sender = $speaker === 'customer' ? $customer : $provider->user;
                $sentAt = $start->copy()->addMinutes($j * 7);
                $lastMessageAt = $sentAt;

                Message::create([
                    'chat_id' => $chatRoom->id,
                    'sender_id' => $sender->id,
                    'message' => $text,
                    'media_type' => 'text',
                    'is_read' => $j < count($script) - 1,
                    'created_at' => $sentAt,
                    'updated_at' => $sentAt,
                ]);
            }

            $chatRoom->update(['last_message_at' => $lastMessageAt]);

            if ($i === 0) {
                Call::create([
                    'chat_id' => $chatRoom->id,
                    'initiated_by' => $customer->id,
                    'call_type' => 'audio',
                    'agora_channel' => 'demo-channel-' . Str::random(8),
                    'status' => 'ended',
                    'started_at' => $lastMessageAt->copy()->addMinutes(10),
                    'ended_at' => $lastMessageAt->copy()->addMinutes(15),
                    'duration_seconds' => 300,
                ]);
            }

            if ($i === 1) {
                Call::create([
                    'chat_id' => $chatRoom->id,
                    'initiated_by' => $provider->user_id,
                    'call_type' => 'video',
                    'agora_channel' => 'demo-channel-' . Str::random(8),
                    'status' => 'ongoing',
                    'started_at' => Carbon::now()->subMinutes(5),
                ]);
            }
        }
    }
}
