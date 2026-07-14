<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Provider;
use App\Models\Rate;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestAttachment;
use App\Models\ServiceRequestTrack;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ServiceRequestSeeder extends Seeder
{
    private float $commissionRate;

    public function run(): void
    {
        if (ServiceRequest::exists()) {
            return;
        }

        $this->commissionRate = (float) (Setting::where('key', 'commission_rate')->value('value') ?? 15);

        $scenarios = [
            [0, 'Sparkle Home Services', 'Deep Cleaning', 'Full Apartment Deep Clean', 'Need a thorough deep clean before Eid visitors arrive.', 350, 'pending'],
            [1, 'FastFix Plumbing', 'Leak Repair', 'Kitchen Sink Leak', 'Water has been leaking under the kitchen sink for two days.', 180, 'pending'],
            [9, 'PowerFix Electrical', 'Wiring & Installation', 'New Wiring for Home Office', 'Need extra power outlets wired in the home office.', 420, 'pending'],
            [2, 'Sparkle Home Services', 'Sofa & Carpet Cleaning', 'Living Room Carpet Cleaning', 'Large living room carpet needs steam cleaning.', 220, 'approved_unpaid'],
            [3, 'Bright Spark Electric', 'Lighting Installation', 'Outdoor Garden Lighting', 'Install LED lighting around the garden.', 500, 'approved_unpaid'],
            [4, 'CoolAir AC Services', 'AC Maintenance', 'Split AC Annual Service', 'Two split units need annual maintenance and gas check.', 300, 'approved_paid'],
            [5, 'Bright Spark Electric', 'Wiring & Installation', 'Office Wiring Upgrade', 'Upgrade wiring for a small home office.', 600, 'in_progress'],
            [6, 'Speedy Movers', 'Home Moving', '3-Bedroom Villa Move', 'Moving from Dammam to a new villa, need packing help.', 1200, 'in_progress'],
            [7, 'CrystalClean Co.', 'Window Cleaning', 'Villa Window Cleaning', 'All exterior and interior windows, two floors.', 260, 'completed'],
            [8, 'Speedy Movers', 'Furniture Assembly', 'New Bedroom Set Assembly', 'Assemble a new bedroom set delivered yesterday.', 150, 'completed'],
            [0, 'FastFix Plumbing', 'Pipe Installation', 'Bathroom Pipe Replacement', 'Replace old galvanized pipes in the main bathroom.', 480, 'completed'],
            [1, 'Sparkle Home Services', 'Deep Cleaning', 'Move-out Deep Clean', 'Deep clean before handing over a rented apartment.', 380, 'confirmed_pending_payout'],
            [3, 'CoolAir AC Services', 'Fridge Repair', 'Fridge Not Cooling', 'Fridge stopped cooling properly, needs repair.', 260, 'confirmed_processing_payout'],
            [6, 'QuickCourier', 'Courier Delivery', 'Furniture Store Delivery', 'Deliver a dining table across the city.', 140, 'confirmed_paid_payout'],
            [9, 'PowerFix Electrical', 'Appliance Repair', 'Washing Machine Wiring Fix', 'Washing machine keeps tripping the breaker.', 210, 'confirmed_pending_payout'],
            [2, 'FastFix Plumbing', 'Leak Repair', 'Bathroom Wall Leak', 'Persistent leak inside the bathroom wall.', 300, 'rejected'],
            [4, 'Bright Spark Electric', 'Wiring & Installation', 'Rewire Old Apartment', 'Old apartment wiring needs a full rewire.', 900, 'rejected'],
            [5, 'CrystalClean Co.', 'Deep Cleaning', 'Weekly Deep Clean', 'Recurring weekly deep clean requested.', 300, 'cancelled'],
            [8, 'Speedy Movers', 'Home Moving', 'Studio Move', 'Small studio move within the same city.', 400, 'cancelled'],
        ];

        foreach ($scenarios as $i => $scenario) {
            $this->buildRequest($i, ...$scenario);
        }
    }

    private function buildRequest(int $seq, int $customerIndex, string $businessName, string $subCategoryName, string $title, string $description, float $price, string $target): void
    {
        $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
        $provider = Provider::where('business_name', $businessName)->with('user')->firstOrFail();

        $createdAt = Carbon::now()->subDays(30 - $seq)->setTime(9, 0);

        $serviceRequest = ServiceRequest::create([
            'user_id' => $customer->id,
            'provider_id' => $provider->id,
            'source' => 'direct',
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'latitude' => $customer->latitude,
            'longitude' => $customer->longitude,
            'address' => $customer->address,
            'scheduled_at' => $createdAt->copy()->addDays(2),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $this->track($serviceRequest, null, 'pending', $customer, $createdAt);

        if ($seq % 2 === 0) {
            ServiceRequestAttachment::create([
                'service_request_id' => $serviceRequest->id,
                'url' => 'https://picsum.photos/seed/sr' . $serviceRequest->id . '/640/480',
                'type' => 'image',
            ]);
        }

        Notification::create([
            'user_id' => $provider->user_id,
            'title' => 'New Service Request',
            'body' => 'You have received a new service request from ' . $customer->name,
            'type' => 'service_request',
            'type_id' => $serviceRequest->id,
            'is_read' => true,
        ]);

        match ($target) {
            'pending' => null,
            'approved_unpaid' => $this->advanceToApproved($serviceRequest, $customer, $createdAt),
            'approved_paid' => $this->advanceToApprovedPaid($serviceRequest, $customer, $createdAt),
            'in_progress' => $this->advanceToInProgress($serviceRequest, $customer, $createdAt),
            'completed' => $this->advanceToCompleted($serviceRequest, $customer, $createdAt),
            'confirmed_pending_payout' => $this->advanceToConfirmed($serviceRequest, $customer, $createdAt, 'pending'),
            'confirmed_processing_payout' => $this->advanceToConfirmed($serviceRequest, $customer, $createdAt, 'processing'),
            'confirmed_paid_payout' => $this->advanceToConfirmed($serviceRequest, $customer, $createdAt, 'paid'),
            'rejected' => $this->track($serviceRequest->fresh(), 'pending', 'rejected', $customer, $createdAt->copy()->addHours(3), ['status' => 'rejected']),
            'cancelled' => $this->track($serviceRequest->fresh(), 'pending', 'cancelled', $customer, $createdAt->copy()->addHours(1), ['status' => 'cancelled']),
            default => null,
        };
    }

    private function advanceToApproved(ServiceRequest $sr, User $customer, Carbon $createdAt): ServiceRequest
    {
        $sr->update(['status' => 'approved']);
        $this->track($sr, 'pending', 'approved', $customer, $createdAt->copy()->addHours(2));

        return $sr;
    }

    private function advanceToApprovedPaid(ServiceRequest $sr, User $customer, Carbon $createdAt): ServiceRequest
    {
        $this->advanceToApproved($sr, $customer, $createdAt);
        $this->pay($sr, $customer, $createdAt->copy()->addHours(4));

        return $sr;
    }

    private function advanceToInProgress(ServiceRequest $sr, User $customer, Carbon $createdAt): ServiceRequest
    {
        $this->advanceToApprovedPaid($sr, $customer, $createdAt);
        $sr->update(['status' => 'in_progress']);
        $this->track($sr, 'approved', 'in_progress', $customer, $createdAt->copy()->addHours(6));

        return $sr;
    }

    private function advanceToCompleted(ServiceRequest $sr, User $customer, Carbon $createdAt): ServiceRequest
    {
        $this->advanceToInProgress($sr, $customer, $createdAt);
        $sr->update(['status' => 'completed']);
        $this->track($sr, 'in_progress', 'completed', $customer, $createdAt->copy()->addDay());

        return $sr;
    }

    private function advanceToConfirmed(ServiceRequest $sr, User $customer, Carbon $createdAt, string $payoutStatus): ServiceRequest
    {
        $this->advanceToCompleted($sr, $customer, $createdAt);
        $confirmedAt = $createdAt->copy()->addDays(2);
        $sr->update(['status' => 'confirmed']);
        $this->track($sr, 'completed', 'confirmed', $customer, $confirmedAt);

        $sr->refresh();
        $price = (float) $sr->price;
        $commission = round($price * ($this->commissionRate / 100), 2);
        $netAmount = round($price - $commission, 2);
        $provider = $sr->provider;

        $payout = Payout::create([
            'provider_id' => $provider->id,
            'service_request_id' => $sr->id,
            'amount' => $netAmount,
            'commission' => $commission,
            'status' => $payoutStatus,
            'paid_at' => $payoutStatus === 'paid' ? $confirmedAt->copy()->addDay() : null,
        ]);

        $wallet = Wallet::firstOrCreate(['user_id' => $provider->user_id]);
        $wallet->increment('balance', $price);
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => $price,
            'source_type' => 'payment',
            'source_id' => $sr->id,
        ]);

        if ($commission > 0) {
            $wallet->decrement('balance', $commission);
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $commission,
                'source_type' => 'commission',
                'source_id' => $sr->id,
            ]);
        }

        if ($payoutStatus === 'paid') {
            $wallet->decrement('balance', $payout->amount);
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $payout->amount,
                'source_type' => 'payout',
                'source_id' => $payout->id,
            ]);
        }

        $customerRate = Rate::create([
            'service_request_id' => $sr->id,
            'rater_id' => $customer->id,
            'ratee_id' => $provider->user_id,
            'rating_type' => 'provider',
            'rate' => fake()->randomElement([4.0, 4.5, 5.0, 5.0]),
            'feedback' => fake()->randomElement([
                'Great service, very professional and on time.',
                'Excellent work, would book again.',
                'Good job overall, minor delay but handled well.',
            ]),
        ]);

        $providerRate = Rate::create([
            'service_request_id' => $sr->id,
            'rater_id' => $provider->user_id,
            'ratee_id' => $customer->id,
            'rating_type' => 'customer',
            'rate' => fake()->randomElement([4.5, 5.0, 5.0]),
            'feedback' => 'Clear instructions and easy to work with.',
        ]);

        $this->recalculateRating($provider->user_id);
        $this->recalculateRating($customer->id);

        Notification::create([
            'user_id' => $provider->user_id,
            'title' => 'Service Request Confirmed',
            'body' => 'The service request "' . $sr->title . '" status has been updated to confirmed.',
            'type' => 'service_request',
            'type_id' => $sr->id,
            'is_read' => fake()->boolean(70),
        ]);

        return $sr;
    }

    private function pay(ServiceRequest $sr, User $customer, Carbon $paidAt): void
    {
        $payment = Payment::create([
            'user_id' => $customer->id,
            'service_request_id' => $sr->id,
            'amount' => $sr->price,
            'payment_method' => fake()->randomElement(['card', 'cash']),
            'status' => 'pending',
            'transaction_ref' => 'MOCK-' . strtoupper(fake()->bothify('##########')),
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ]);

        $payment->update(['status' => 'paid', 'paid_at' => $paidAt]);
        $sr->update(['payment_status' => 'paid']);

        Notification::create([
            'user_id' => $sr->provider->user_id,
            'title' => 'Payment Confirmed',
            'body' => 'Payment of ' . $sr->price . ' has been confirmed for service request: "' . $sr->title . '".',
            'type' => 'payment',
            'type_id' => $payment->id,
            'is_read' => fake()->boolean(60),
        ]);
    }

    private function track(ServiceRequest $sr, ?string $from, string $to, User $changedBy, Carbon $dateTime, array $extraUpdate = []): ServiceRequest
    {
        if ($extraUpdate) {
            $sr->update($extraUpdate);
        }

        ServiceRequestTrack::create([
            'service_request_id' => $sr->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $changedBy->id,
            'date_time' => $dateTime,
        ]);

        return $sr;
    }

    private function recalculateRating(int $userId): void
    {
        $stats = Rate::where('ratee_id', $userId)
            ->selectRaw('COUNT(*) as count, AVG(rate) as average')
            ->first();

        User::where('id', $userId)->update([
            'average_rating' => round($stats->average ?? 0.0, 1),
            'ratings_count' => $stats->count ?? 0,
        ]);
    }
}
