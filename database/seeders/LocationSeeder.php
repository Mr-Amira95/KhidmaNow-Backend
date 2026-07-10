<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        if (Country::exists()) {
            return;
        }

        $country = Country::create([
            'flag' => '🇸🇦',
            'name_ar' => 'المملكة العربية السعودية',
            'name_en' => 'Saudi Arabia',
            'iso' => 'SAU',
            'phone_code' => '+966',
            'currency_code' => 'SAR',
            'currency_value' => 1.0000,
            'is_active' => true,
        ]);

        $cities = [
            'Riyadh' => ['name_ar' => 'الرياض', 'areas' => [
                ['name_ar' => 'العليا', 'name_en' => 'Al Olaya'],
                ['name_ar' => 'الملز', 'name_en' => 'Al Malaz'],
            ]],
            'Jeddah' => ['name_ar' => 'جدة', 'areas' => [
                ['name_ar' => 'الروضة', 'name_en' => 'Al Rawdah'],
                ['name_ar' => 'الحمراء', 'name_en' => 'Al Hamra'],
            ]],
            'Dammam' => ['name_ar' => 'الدمام', 'areas' => [
                ['name_ar' => 'الشاطئ', 'name_en' => 'Al Shati'],
                ['name_ar' => 'الفيصلية', 'name_en' => 'Al Faisaliyah'],
            ]],
        ];

        foreach ($cities as $nameEn => $data) {
            $city = City::create([
                'country_id' => $country->id,
                'name_ar' => $data['name_ar'],
                'name_en' => $nameEn,
                'is_active' => true,
            ]);

            foreach ($data['areas'] as $area) {
                Area::create([
                    'city_id' => $city->id,
                    'name_ar' => $area['name_ar'],
                    'name_en' => $area['name_en'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
