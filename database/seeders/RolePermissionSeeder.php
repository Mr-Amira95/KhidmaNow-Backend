<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Page/action permission matrix. Keys follow `{group}.{action}`; `view` gates
     * every other action in its group (enforced in PermissionMiddleware).
     */
    private const MATRIX = [
        'dashboard'        => ['label' => 'Dashboard', 'actions' => ['view' => 'View Dashboard']],
        'clients'          => ['label' => 'Clients', 'actions' => [
            'view' => 'View Clients', 'create' => 'Create Clients', 'edit' => 'Edit Clients',
            'delete' => 'Delete Clients', 'block' => 'Block/Unblock Clients', 'change_password' => 'Change Client Password',
        ]],
        'providers'        => ['label' => 'Providers', 'actions' => [
            'view' => 'View Providers', 'edit' => 'Edit Providers', 'delete' => 'Delete Providers',
            'verify' => 'Verify/Unverify Providers', 'manage_documents' => 'Approve/Reject Provider Documents',
        ]],
        'categories'       => ['label' => 'Categories', 'actions' => [
            'view' => 'View Categories', 'create' => 'Create Categories', 'edit' => 'Edit Categories', 'delete' => 'Delete Categories',
        ]],
        'countries'        => ['label' => 'Countries', 'actions' => [
            'view' => 'View Countries', 'create' => 'Create Countries', 'edit' => 'Edit Countries', 'delete' => 'Delete Countries',
        ]],
        'cities'           => ['label' => 'Cities', 'actions' => [
            'view' => 'View Cities', 'create' => 'Create Cities', 'edit' => 'Edit Cities', 'delete' => 'Delete Cities',
        ]],
        'areas'            => ['label' => 'Areas', 'actions' => [
            'view' => 'View Areas', 'create' => 'Create Areas', 'edit' => 'Edit Areas', 'delete' => 'Delete Areas',
        ]],
        'intro_screens'    => ['label' => 'Intro Screens', 'actions' => [
            'view' => 'View Intro Screens', 'create' => 'Create Intro Screens', 'edit' => 'Edit Intro Screens', 'delete' => 'Delete Intro Screens',
        ]],
        'terms'            => ['label' => 'Terms & Conditions', 'actions' => ['view' => 'View Terms & Conditions', 'edit' => 'Edit Terms & Conditions']],
        'privacy'          => ['label' => 'Privacy Policy', 'actions' => ['view' => 'View Privacy Policy', 'edit' => 'Edit Privacy Policy']],
        'faqs'             => ['label' => 'FAQs', 'actions' => [
            'view' => 'View FAQs', 'create' => 'Create FAQs', 'edit' => 'Edit FAQs', 'delete' => 'Delete FAQs',
        ]],
        'service_requests' => ['label' => 'Service Requests', 'actions' => [
            'view' => 'View Service Requests', 'create' => 'Create Service Requests', 'edit' => 'Edit Service Requests',
        ]],
        'quotations'       => ['label' => 'Quotations', 'actions' => [
            'view' => 'View Quotations', 'create' => 'Create Quotations', 'edit' => 'Edit Quotations',
        ]],
        'payments'         => ['label' => 'Payments', 'actions' => ['view' => 'View Payments']],
        'payouts'          => ['label' => 'Payouts', 'actions' => ['view' => 'View Payouts', 'edit' => 'Update Payout Status']],
        'wallets'          => ['label' => 'Wallets', 'actions' => ['view' => 'View Wallets']],
        'rates'            => ['label' => 'Rates / Reviews', 'actions' => [
            'view' => 'View Rates', 'create' => 'Create Rates', 'delete' => 'Delete Rates',
        ]],
        'settings'         => ['label' => 'Settings', 'actions' => [
            'view' => 'View Settings', 'create' => 'Create Settings', 'edit' => 'Edit Settings', 'delete' => 'Delete Settings',
        ]],
        'notifications'    => ['label' => 'Notifications', 'actions' => ['view' => 'View Notifications', 'send' => 'Send Notifications']],
        'chats'            => ['label' => 'Chats', 'actions' => ['view' => 'View Chats']],
        'support_tickets'  => ['label' => 'Support Tickets', 'actions' => [
            'view' => 'View Support Tickets', 'reply' => 'Reply to Support Tickets', 'close' => 'Close/Reopen Support Tickets',
        ]],
    ];

    public function run(): void
    {
        foreach (self::MATRIX as $group => $config) {
            foreach ($config['actions'] as $action => $label) {
                Permission::firstOrCreate(
                    ['key' => "{$group}.{$action}"],
                    ['name' => $label, 'group' => $config['label']]
                );
            }
        }

        if (!User::where('user_type', 'admin')->where('is_super_admin', true)->exists()) {
            User::where('user_type', 'admin')->oldest('id')->first()?->update(['is_super_admin' => true]);
        }
    }
}
