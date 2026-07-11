@extends('admin.layouts.app')

@section('title', 'Admins')
@section('page', 'admins')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <input id="admins-search" type="text" placeholder="Search name, phone, email..."
                class="w-64 input-field-sm">
            <button id="new-admin-button" type="button" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Admin
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Phone</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="admins-table-body"></tbody>
            </table>
        </div>

        <div id="admins-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="admin-modal" title="New Admin">
        <form id="admin-form" class="space-y-4" novalidate>
            <input type="hidden" id="admin-id">
            <x-admin.text-field name="admin_name" label="Name" required />
            <div class="grid grid-cols-2 gap-4">
                <x-admin.text-field name="admin_phone" label="Phone" required />
                <x-admin.text-field name="admin_email" type="email" label="Email" />
            </div>
            <x-admin.text-field name="admin_password" type="password" label="Password" autocomplete="new-password" />
            <div class="grid grid-cols-2 gap-4">
                <x-admin.select-field name="admin_role_id" label="Role" placeholder="Select a role" required />
                <x-admin.select-field name="admin_status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'blocked' => 'Blocked']" />
            </div>
            <p id="admin-password-hint" class="text-xs text-zinc-400">Leave blank to keep the current password.</p>
            <x-admin.submit-button>Save Admin</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
