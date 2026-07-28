@extends('admin.layouts.app')

@section('title', 'Company CliQ Details')
@section('page', 'cms-company-cliq-details')

@section('content')
    <div class="max-w-3xl card-surface p-6">
        <div id="cliq-banner" class="hidden mb-4" role="alert"></div>
        <form id="cliq-form" class="stagger space-y-4" novalidate>
            <x-admin.text-field name="alias" label="CliQ Alias / Phone Number" />
            <x-admin.text-field name="bank_name" label="Bank Name" />
            <x-admin.text-field name="holder_name" label="Account Holder Name" />
            <x-admin.submit-button data-permission="company_cliq.edit">Save Changes</x-admin.submit-button>
        </form>
    </div>
@endsection
