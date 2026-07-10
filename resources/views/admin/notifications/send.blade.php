@extends('admin.layouts.app')

@section('title', 'Send Notification')
@section('page', 'notifications-send')

@section('content')
    <div class="mx-auto max-w-2xl card-surface p-6">
        <div id="send-notification-banner" class="hidden mb-4" role="alert"></div>

        <form id="send-notification-form" class="stagger space-y-4" novalidate>
            <x-admin.text-field name="notification_title" label="Title" required />
            <x-admin.textarea-field name="notification_description" label="Description" rows="3" />

            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="notification_icon" label="Icon (name or URL)" placeholder="bell" />
                <x-admin.select-field name="notification_action" label="Action" :options="[
                    'service_request' => 'Service Request',
                    'payment' => 'Payment',
                    'chat' => 'Chat',
                    'system' => 'System',
                ]" />
            </div>

            <x-admin.text-field name="notification_action_id" type="number" label="Action ID (optional)" placeholder="e.g. related record id" />

            <x-admin.toggle-switch name="send_to_all" label="Send to all users" :checked="true" />

            <div id="notification-user-ids-wrapper" class="hidden">
                <x-admin.text-field name="notification_user_ids" label="User IDs (comma-separated)" placeholder="1, 2, 3" />
            </div>

            <x-admin.submit-button>Send Notification</x-admin.submit-button>
        </form>
    </div>
@endsection
