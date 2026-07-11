@extends('admin.layouts.app')

@section('title', 'Terms & Conditions')
@section('page', 'cms-terms')

@section('content')
    <div class="max-w-3xl card-surface p-6">
        <div id="terms-banner" class="hidden mb-4" role="alert"></div>
        <form id="terms-form" class="stagger space-y-4" novalidate>
            <x-admin.html-editor-field name="terms_content_en" label="Content (English)" direction="ltr" />
            <x-admin.html-editor-field name="terms_content_ar" label="Content (Arabic)" direction="rtl" />
            <x-admin.submit-button>Save Changes</x-admin.submit-button>
        </form>
    </div>
@endsection
