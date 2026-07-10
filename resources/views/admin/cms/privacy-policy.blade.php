@extends('admin.layouts.app')

@section('title', 'Privacy Policy')
@section('page', 'cms-privacy-policy')

@section('content')
    <div class="max-w-3xl card-surface p-6">
        <div id="privacy-banner" class="hidden mb-4" role="alert"></div>
        <form id="privacy-form" class="stagger space-y-4" novalidate>
            <x-admin.textarea-field name="privacy_content_en" label="Content (English)" rows="12" />
            <x-admin.textarea-field name="privacy_content_ar" label="Content (Arabic)" rows="12" />
            <x-admin.submit-button>Save Changes</x-admin.submit-button>
        </form>
    </div>
@endsection
