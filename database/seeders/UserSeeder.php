<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\DeviceToken;
use App\Models\Provider;
use App\Models\ProviderDocument;
use App\Models\ProviderGallery;
use App\Models\ProviderSubCategory;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('user_type', 'admin')->exists()) {
            return;
        }

        $password = Hash::make('password');

        User::create([
            'name' => 'Admin Khidma',
            'phone' => '0500000001',
            'email' => 'admin@khidmanow.com',
            'password' => $password,
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        $this->seedCustomers($password);
        $this->seedProviders($password);
    }

    private function seedCustomers(string $password): void
    {
        $customers = [
            ['Sara Al-Otaibi', 'Riyadh'],
            ['Mohammed Al-Harbi', 'Riyadh'],
            ['Nora Al-Qahtani', 'Riyadh'],
            ['Faisal Al-Dossari', 'Jeddah'],
            ['Layla Al-Ghamdi', 'Jeddah'],
            ['Abdullah Al-Zahrani', 'Jeddah'],
            ['Huda Al-Shehri', 'Dammam'],
            ['Khalid Al-Malki', 'Dammam'],
            ['Reem Al-Subaie', 'Dammam'],
            ['Yousef Al-Amri', 'Riyadh'],
        ];

        $coords = [
            'Riyadh' => [24.7136, 46.6753],
            'Jeddah' => [21.4858, 39.1925],
            'Dammam' => [26.4207, 50.0888],
        ];

        foreach ($customers as $i => [$name, $cityName]) {
            $index = $i + 1;
            $user = User::create([
                'name' => $name,
                'phone' => '05' . str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                'email' => 'customer' . $index . '@khidmanow.com',
                'password' => $password,
                'user_type' => 'customer',
                'status' => 'active',
                'latitude' => $coords[$cityName][0],
                'longitude' => $coords[$cityName][1],
                'address' => $cityName . ', Saudi Arabia',
            ]);

            UserAddress::create([
                'user_id' => $user->id,
                'title' => 'Home',
                'address' => $cityName . ' - ' . fake()->streetAddress(),
                'latitude' => $coords[$cityName][0] + fake()->randomFloat(5, -0.05, 0.05),
                'longitude' => $coords[$cityName][1] + fake()->randomFloat(5, -0.05, 0.05),
                'is_default' => true,
            ]);

            if ($index % 3 === 0) {
                UserAddress::create([
                    'user_id' => $user->id,
                    'title' => 'Work',
                    'address' => $cityName . ' - ' . fake()->streetAddress(),
                    'latitude' => $coords[$cityName][0] + fake()->randomFloat(5, -0.05, 0.05),
                    'longitude' => $coords[$cityName][1] + fake()->randomFloat(5, -0.05, 0.05),
                    'is_default' => false,
                ]);
            }

            DeviceToken::create([
                'user_id' => $user->id,
                'token' => 'demo-token-customer-' . $index . '-' . fake()->uuid(),
                'platform' => $index % 2 === 0 ? 'android' : 'ios',
                'is_active' => true,
            ]);
        }
    }

    private function seedProviders(string $password): void
    {
        $cities = City::pluck('id', 'name_en');

        $providers = [
            [
                'name' => 'Sparkle Home Services', 'city' => 'Riyadh', 'years' => 6, 'verified' => true,
                'subs' => ['Deep Cleaning', 'Sofa & Carpet Cleaning'],
                'description' => 'Trusted home cleaning teams for apartments, villas, and offices.',
            ],
            [
                'name' => 'FastFix Plumbing', 'city' => 'Riyadh', 'years' => 9, 'verified' => true,
                'subs' => ['Leak Repair', 'Pipe Installation'],
                'description' => '24/7 emergency plumbing repairs and installations.',
            ],
            [
                'name' => 'Bright Spark Electric', 'city' => 'Jeddah', 'years' => 5, 'verified' => true,
                'subs' => ['Wiring & Installation', 'Lighting Installation'],
                'description' => 'Licensed electricians for residential and commercial wiring.',
            ],
            [
                'name' => 'CoolAir AC Services', 'city' => 'Jeddah', 'years' => 7, 'verified' => true,
                'subs' => ['AC Maintenance', 'Fridge Repair'],
                'description' => 'AC installation, maintenance, and appliance repair specialists.',
            ],
            [
                'name' => 'Speedy Movers', 'city' => 'Dammam', 'years' => 4, 'verified' => true,
                'subs' => ['Home Moving', 'Furniture Assembly'],
                'description' => 'Careful and fast home moving and furniture assembly.',
            ],
            [
                'name' => 'CrystalClean Co.', 'city' => 'Dammam', 'years' => 3, 'verified' => false,
                'subs' => ['Window Cleaning', 'Deep Cleaning'],
                'description' => 'New cleaning company specializing in windows and deep cleans.',
            ],
            [
                'name' => 'PowerFix Electrical', 'city' => 'Riyadh', 'years' => 8, 'verified' => true,
                'subs' => ['Appliance Repair', 'Wiring & Installation'],
                'description' => 'Electrical appliance repair and rewiring services.',
            ],
            [
                'name' => 'QuickCourier', 'city' => 'Jeddah', 'years' => 2, 'verified' => true,
                'subs' => ['Courier Delivery', 'Home Moving'],
                'description' => 'Same-day courier and small delivery service.',
            ],
        ];

        $availability = ['online', 'offline', 'busy'];

        foreach ($providers as $i => $data) {
            $index = $i + 1;
            $user = User::create([
                'name' => $data['name'] . ' Owner',
                'phone' => '05' . str_pad((string) (20000000 + $index), 8, '0', STR_PAD_LEFT),
                'email' => 'provider' . $index . '@khidmanow.com',
                'password' => $password,
                'user_type' => 'provider',
                'status' => 'active',
            ]);

            $provider = Provider::create([
                'user_id' => $user->id,
                'city_id' => $cities[$data['city']],
                'business_name' => $data['name'],
                'description' => $data['description'],
                'experience_years' => $data['years'],
                'availability_status' => $availability[$index % count($availability)],
                'is_verified' => $data['verified'],
            ]);

            foreach ($data['subs'] as $subName) {
                $subCategoryId = SubCategory::where('name_en', $subName)->value('id');
                if ($subCategoryId) {
                    ProviderSubCategory::create([
                        'provider_id' => $provider->id,
                        'sub_category_id' => $subCategoryId,
                    ]);
                }
            }

            ProviderDocument::create([
                'provider_id' => $provider->id,
                'type' => 'id',
                'document_url' => 'providers/documents/id_' . $index . '.jpg',
                'status' => $data['verified'] ? 'approved' : 'pending',
            ]);
            ProviderDocument::create([
                'provider_id' => $provider->id,
                'type' => 'commercial_register',
                'document_url' => 'providers/documents/cr_' . $index . '.jpg',
                'status' => $data['verified'] ? 'approved' : 'pending',
                'rejection_reason' => $data['verified'] ? null : 'Document image is blurry, please re-upload.',
            ]);

            for ($g = 1; $g <= 3; $g++) {
                ProviderGallery::create([
                    'provider_id' => $provider->id,
                    'media_path' => 'https://picsum.photos/seed/provider' . $index . 'g' . $g . '/640/480',
                ]);
            }

            Wallet::create(['user_id' => $user->id, 'balance' => 0]);

            DeviceToken::create([
                'user_id' => $user->id,
                'token' => 'demo-token-provider-' . $index . '-' . fake()->uuid(),
                'platform' => $index % 2 === 0 ? 'android' : 'ios',
                'is_active' => true,
            ]);
        }
    }
}
