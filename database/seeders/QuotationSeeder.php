<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Provider;
use App\Models\Quotation;
use App\Models\QuotationBid;
use App\Models\QuotationTrack;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        if (Quotation::exists()) {
            return;
        }

        $this->build(2, 'Deep Cleaning', 'Deep Clean Before Housewarming', 'Need a full deep clean for a 3-bedroom apartment.', 400,
            [['Sparkle Home Services', 350], ['CrystalClean Co.', 380]], 'accept:0');

        $this->build(3, 'Wiring & Installation', 'Full Villa Rewiring Quote', 'Looking for quotes to rewire an older villa.', 700,
            [['Bright Spark Electric', 650], ['PowerFix Electrical', 690]], 'accept:0');

        $this->build(9, 'Leak Repair', 'Recurring Leak Investigation', 'Need a plumber to trace a recurring ceiling leak.', 250,
            [['FastFix Plumbing', 230]], 'accept:0');

        $this->build(6, 'Home Moving', 'Villa to Villa Move Quote', 'Need quotes for moving a 4-bedroom villa across the city.', 1000,
            [['Speedy Movers', 950], ['QuickCourier', 980]], 'open');

        $this->build(7, 'AC Maintenance', 'Central AC Maintenance Quote', 'Need a quote for annual central AC maintenance.', 350,
            [['CoolAir AC Services', 320]], 'open');

        $this->build(8, 'Courier Delivery', 'Recurring Courier Contract', 'Requested a quote for a weekly courier contract, no longer needed.', 200,
            [['QuickCourier', 190]], 'cancelled');
    }

    private function build(int $customerIndex, string $subCategoryName, string $title, string $description, float $budget, array $bidders, string $outcome): void
    {
        $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
        $subCategory = SubCategory::where('name_en', $subCategoryName)->firstOrFail();
        $createdAt = Carbon::now()->subDays(20)->setTime(10, 0);

        $quotation = Quotation::create([
            'user_id' => $customer->id,
            'category_id' => $subCategory->category_id,
            'sub_category_id' => $subCategory->id,
            'title' => $title,
            'description' => $description,
            'budget' => $budget,
            'latitude' => $customer->latitude,
            'longitude' => $customer->longitude,
            'address' => $customer->address,
            'scheduled_at' => $createdAt->copy()->addDays(3),
            'status' => 'open',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $bids = [];
        foreach ($bidders as $i => [$businessName, $price]) {
            $provider = Provider::where('business_name', $businessName)->firstOrFail();

            $bids[] = QuotationBid::create([
                'quotation_id' => $quotation->id,
                'provider_id' => $provider->id,
                'price' => $price,
                'note' => 'We can start within 2 days of confirmation.',
                'status' => 'pending',
                'created_at' => $createdAt->copy()->addHours($i + 1),
                'updated_at' => $createdAt->copy()->addHours($i + 1),
            ]);

            Notification::create([
                'user_id' => $customer->id,
                'title' => 'New Bid Received',
                'body' => 'Provider ' . $businessName . ' has submitted a bid of ' . $price . ' on your quotation "' . $title . '".',
                'type' => 'service_request',
                'type_id' => $quotation->id,
                'is_read' => fake()->boolean(50),
            ]);
        }

        if (str_starts_with($outcome, 'accept:')) {
            $winningIndex = (int) explode(':', $outcome)[1];
            $this->acceptBid($quotation, $bids, $winningIndex, $customer, $createdAt);
        } elseif ($outcome === 'cancelled') {
            $quotation->update(['status' => 'cancelled']);
            QuotationTrack::create([
                'quotation_id' => $quotation->id,
                'from_status' => 'open',
                'to_status' => 'cancelled',
                'changed_by' => $customer->id,
                'date_time' => $createdAt->copy()->addDay(),
            ]);
        }
    }

    private function acceptBid(Quotation $quotation, array $bids, int $winningIndex, User $customer, Carbon $createdAt): void
    {
        $winningBid = $bids[$winningIndex];
        $winningBid->update(['status' => 'accepted']);

        foreach ($bids as $i => $bid) {
            if ($i !== $winningIndex) {
                $bid->update(['status' => 'rejected']);
            }
        }

        $quotation->update([
            'status' => 'closed',
            'accepted_bid_id' => $winningBid->id,
        ]);

        QuotationTrack::create([
            'quotation_id' => $quotation->id,
            'from_status' => 'open',
            'to_status' => 'closed',
            'changed_by' => $customer->id,
            'date_time' => $createdAt->copy()->addDays(1),
        ]);

        $winningBid->loadMissing('provider');

        $serviceRequest = ServiceRequest::create([
            'user_id' => $quotation->user_id,
            'provider_id' => $winningBid->provider_id,
            'sub_category_id' => $quotation->sub_category_id,
            'quotation_id' => $quotation->id,
            'source' => 'quotation',
            'title' => $quotation->title,
            'description' => $quotation->description,
            'price' => $winningBid->price,
            'status' => 'approved',
            'payment_status' => 'unpaid',
            'latitude' => $quotation->latitude,
            'longitude' => $quotation->longitude,
            'address' => $quotation->address,
            'scheduled_at' => $quotation->scheduled_at,
            'created_at' => $createdAt->copy()->addDays(1),
            'updated_at' => $createdAt->copy()->addDays(1),
        ]);

        ServiceRequestTrack::create([
            'service_request_id' => $serviceRequest->id,
            'from_status' => null,
            'to_status' => 'approved',
            'changed_by' => $customer->id,
            'date_time' => $createdAt->copy()->addDays(1),
        ]);

        Notification::create([
            'user_id' => $winningBid->provider->user_id,
            'title' => 'Bid Approved',
            'body' => 'Your bid of ' . $winningBid->price . ' for "' . $quotation->title . '" has been approved.',
            'type' => 'service_request',
            'type_id' => $serviceRequest->id,
            'is_read' => fake()->boolean(60),
        ]);
    }
}
