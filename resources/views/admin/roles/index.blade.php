@extends('admin.layouts.app')

@section('title', 'Roles & Permissions')
@section('page', 'roles')

@section('content')
    <div class="card-surface">
        <div class="card-header">
            <input id="roles-search" type="text" placeholder="Search roles..."
                class="w-64 input-field-sm">
            <button id="new-role-button" type="button" class="btn btn-primary">
                <i class="ph ph-plus"></i> New Role
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Name</th>
                        <th class="py-3 px-4">Permissions</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="roles-table-body"></tbody>
            </table>
        </div>

        <div id="roles-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="role-modal" title="New Role">
        <form id="role-form" class="space-y-4" novalidate>
            <input type="hidden" id="role-id">
            <x-admin.text-field name="role_name" label="Role name" required />
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Permissions</p>
                <p class="mb-3 text-xs text-zinc-400">A page's other permissions only take effect once "View" is granted for that page.</p>
                <div id="role-permission-matrix" class="max-h-[50vh] space-y-4 overflow-y-auto pr-1"></div>
            </div>
            <x-admin.submit-button>Save Role</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection
