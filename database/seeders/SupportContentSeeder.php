<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Faq;
use App\Models\IntroScreen;
use App\Models\PrivacyPolicy;
use App\Models\Provider;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\TermsAndCondition;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SupportContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFaqs();
        $this->seedIntroScreens();
        $this->seedLegalPages();
        $this->seedSupportTickets();
        $this->seedWishlists();
    }

    private function seedFaqs(): void
    {
        if (Faq::exists()) {
            return;
        }

        $faqs = [
            ['How do I book a service provider?', 'Browse categories or post a quotation request, then choose a provider and confirm.', 'كيف أحجز مقدم خدمة؟', 'تصفح الفئات أو انشر طلب عرض سعر، ثم اختر مقدم الخدمة وأكد الحجز.'],
            ['How is the commission calculated?', 'KhidmaNow deducts a small commission from the provider payout once a job is confirmed.', 'كيف تُحسب العمولة؟', 'يخصم تطبيق خدمة الآن عمولة بسيطة من مستحقات مقدم الخدمة بعد تأكيد الطلب.'],
            ['Can I cancel a service request?', 'Yes, you can cancel while the request is still pending.', 'هل يمكنني إلغاء طلب الخدمة؟', 'نعم، يمكنك الإلغاء طالما الطلب لا يزال قيد الانتظار.'],
            ['How do payouts work for providers?', 'Payouts are processed by the admin team after a job is confirmed by the customer.', 'كيف تعمل عمليات الدفع لمقدمي الخدمة؟', 'تتم معالجة المدفوعات من قبل فريق الإدارة بعد تأكيد العميل للطلب.'],
            ['What if I am not satisfied with the service?', 'You can leave a low rating and contact support to raise a ticket.', 'ماذا لو لم أكن راضياً عن الخدمة؟', 'يمكنك ترك تقييم منخفض والتواصل مع الدعم لفتح تذكرة.'],
            ['How do I become a verified provider?', 'Upload your ID and commercial registration documents for admin review.', 'كيف أصبح مقدم خدمة موثقاً؟', 'قم برفع هويتك والسجل التجاري لمراجعتها من قبل الإدارة.'],
        ];

        foreach ($faqs as $order => [$qEn, $aEn, $qAr, $aAr]) {
            Faq::create([
                'question_ar' => $qAr,
                'question_en' => $qEn,
                'answer_ar' => $aAr,
                'answer_en' => $aEn,
                'order' => $order + 1,
                'is_active' => true,
            ]);
        }
    }

    private function seedIntroScreens(): void
    {
        if (IntroScreen::exists()) {
            return;
        }

        $screens = [
            ['Find Trusted Providers', 'ابحث عن مقدمي خدمة موثوقين', 'Browse verified professionals near you for any home service.', 'تصفح محترفين موثقين بالقرب منك لأي خدمة منزلية.'],
            ['Get Instant Quotes', 'احصل على عروض أسعار فورية', 'Post what you need and receive competitive bids from providers.', 'انشر ما تحتاجه واحصل على عروض تنافسية من مقدمي الخدمة.'],
            ['Track Every Step', 'تابع كل خطوة', 'Follow your request from booking to completion in real time.', 'تابع طلبك من الحجز حتى الإنجاز في الوقت الفعلي.'],
        ];

        foreach ($screens as $order => [$titleEn, $titleAr, $descEn, $descAr]) {
            IntroScreen::create([
                'image' => 'intro/screen' . ($order + 1) . '.png',
                'title_ar' => $titleAr,
                'title_en' => $titleEn,
                'description_ar' => $descAr,
                'description_en' => $descEn,
                'order' => $order + 1,
                'is_active' => true,
            ]);
        }
    }

    private function seedLegalPages(): void
    {
        if (!TermsAndCondition::exists()) {
            TermsAndCondition::create([
                'content_ar' => 'باستخدامك لتطبيق خدمة الآن فإنك توافق على الشروط والأحكام الخاصة بالمنصة، بما في ذلك سياسة العمولات وإلغاء الطلبات.',
                'content_en' => 'By using the KhidmaNow app you agree to the platform terms and conditions, including the commission policy and request cancellation rules.',
            ]);
        }

        if (!PrivacyPolicy::exists()) {
            PrivacyPolicy::create([
                'content_ar' => 'نحن نحترم خصوصيتك ونلتزم بحماية بياناتك الشخصية وعدم مشاركتها مع أطراف ثالثة دون إذنك.',
                'content_en' => 'We respect your privacy and are committed to protecting your personal data and never sharing it with third parties without consent.',
            ]);
        }
    }

    private function seedSupportTickets(): void
    {
        if (SupportTicket::exists()) {
            return;
        }

        $admin = User::where('user_type', 'admin')->firstOrFail();

        $tickets = [
            [0, 'Payment not reflecting', "I paid for my service request but it still shows unpaid.", 'open', false],
            [4, 'Provider did not show up', "The provider I booked never arrived for the scheduled appointment.", 'open', false],
            [1, 'Refund request', "I was charged twice for the same service request, please refund.", 'closed', true],
            [6, 'App keeps crashing', "The app crashes when I try to upload a photo attachment.", 'closed', true],
        ];

        foreach ($tickets as [$customerIndex, $subject, $description, $status, $closed]) {
            $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
            $createdAt = Carbon::now()->subDays(10);

            $ticket = SupportTicket::create([
                'user_id' => $customer->id,
                'subject' => $subject,
                'description' => $description,
                'status' => $status,
                'closed_by' => $closed ? $admin->id : null,
                'closed_at' => $closed ? $createdAt->copy()->addDay() : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            SupportTicketReply::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $admin->id,
                'message' => 'Thanks for reaching out, we are looking into this now.',
                'created_at' => $createdAt->copy()->addHours(2),
                'updated_at' => $createdAt->copy()->addHours(2),
            ]);

            if ($closed) {
                SupportTicketReply::create([
                    'ticket_id' => $ticket->id,
                    'sender_id' => $admin->id,
                    'message' => 'This has been resolved. Let us know if you need anything else.',
                    'created_at' => $createdAt->copy()->addDay(),
                    'updated_at' => $createdAt->copy()->addDay(),
                ]);
            }
        }
    }

    private function seedWishlists(): void
    {
        if (Wishlist::exists()) {
            return;
        }

        $favoriteProviders = [
            [0, 'Sparkle Home Services'],
            [1, 'FastFix Plumbing'],
            [2, 'Bright Spark Electric'],
            [3, 'CoolAir AC Services'],
        ];

        foreach ($favoriteProviders as [$customerIndex, $businessName]) {
            $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
            $provider = Provider::where('business_name', $businessName)->firstOrFail();

            Wishlist::create([
                'user_id' => $customer->id,
                'item_type' => 'provider',
                'item_id' => $provider->id,
            ]);
        }

        $favoriteCategories = [
            [4, 'Home Cleaning'],
            [5, 'Electrical'],
        ];

        foreach ($favoriteCategories as [$customerIndex, $categoryName]) {
            $customer = User::where('email', 'customer' . ($customerIndex + 1) . '@khidmanow.com')->firstOrFail();
            $category = Category::where('name_en', $categoryName)->firstOrFail();

            Wishlist::create([
                'user_id' => $customer->id,
                'item_type' => 'category',
                'item_id' => $category->id,
            ]);
        }
    }
}
