<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        if (Category::exists()) {
            return;
        }

        $data = [
            [
                'name_ar' => 'تنظيف المنازل', 'name_en' => 'Home Cleaning',
                'description_ar' => 'خدمات تنظيف شاملة للمنازل والشقق.',
                'description_en' => 'Full home and apartment cleaning services.',
                'icon' => 'icons/categories/cleaning.png',
                'subs' => [
                    ['ar' => 'تنظيف عميق', 'en' => 'Deep Cleaning'],
                    ['ar' => 'تنظيف الكنب والسجاد', 'en' => 'Sofa & Carpet Cleaning'],
                    ['ar' => 'تنظيف النوافذ', 'en' => 'Window Cleaning'],
                ],
            ],
            [
                'name_ar' => 'السباكة', 'name_en' => 'Plumbing',
                'description_ar' => 'إصلاح وتركيب أنظمة السباكة والمياه.',
                'description_en' => 'Plumbing and water system repair & installation.',
                'icon' => 'icons/categories/plumbing.png',
                'subs' => [
                    ['ar' => 'إصلاح التسريبات', 'en' => 'Leak Repair'],
                    ['ar' => 'تركيب المواسير', 'en' => 'Pipe Installation'],
                    ['ar' => 'إصلاح سخانات المياه', 'en' => 'Water Heater Repair'],
                ],
            ],
            [
                'name_ar' => 'الكهرباء', 'name_en' => 'Electrical',
                'description_ar' => 'خدمات كهربائية للمنازل والمكاتب.',
                'description_en' => 'Electrical services for homes and offices.',
                'icon' => 'icons/categories/electrical.png',
                'subs' => [
                    ['ar' => 'تمديدات وتركيبات كهربائية', 'en' => 'Wiring & Installation'],
                    ['ar' => 'إصلاح الأجهزة الكهربائية', 'en' => 'Appliance Repair'],
                    ['ar' => 'تركيب الإضاءة', 'en' => 'Lighting Installation'],
                ],
            ],
            [
                'name_ar' => 'صيانة التكييف والأجهزة', 'name_en' => 'AC & Appliance Maintenance',
                'description_ar' => 'صيانة وإصلاح المكيفات والأجهزة المنزلية.',
                'description_en' => 'Maintenance and repair of AC units and home appliances.',
                'icon' => 'icons/categories/ac.png',
                'subs' => [
                    ['ar' => 'صيانة المكيفات', 'en' => 'AC Maintenance'],
                    ['ar' => 'إصلاح الثلاجات', 'en' => 'Fridge Repair'],
                    ['ar' => 'إصلاح الغسالات', 'en' => 'Washing Machine Repair'],
                ],
            ],
            [
                'name_ar' => 'النقل والتوصيل', 'name_en' => 'Moving & Delivery',
                'description_ar' => 'خدمات نقل الأثاث والتوصيل السريع.',
                'description_en' => 'Furniture moving and fast delivery services.',
                'icon' => 'icons/categories/moving.png',
                'subs' => [
                    ['ar' => 'نقل العفش', 'en' => 'Home Moving'],
                    ['ar' => 'تركيب الأثاث', 'en' => 'Furniture Assembly'],
                    ['ar' => 'توصيل الطرود', 'en' => 'Courier Delivery'],
                ],
            ],
        ];

        foreach ($data as $entry) {
            $category = Category::create([
                'name_ar' => $entry['name_ar'],
                'name_en' => $entry['name_en'],
                'description_ar' => $entry['description_ar'],
                'description_en' => $entry['description_en'],
                'icon' => $entry['icon'],
                'is_active' => true,
            ]);

            foreach ($entry['subs'] as $sub) {
                SubCategory::create([
                    'category_id' => $category->id,
                    'name_ar' => $sub['ar'],
                    'name_en' => $sub['en'],
                    'description_ar' => $sub['ar'] . ' - وصف الخدمة الفرعية.',
                    'description_en' => $sub['en'] . ' service.',
                    'icon' => 'icons/sub_categories/' . str($sub['en'])->slug() . '.png',
                    'is_active' => true,
                ]);
            }
        }
    }
}
