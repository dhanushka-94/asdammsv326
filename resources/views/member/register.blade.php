@extends('layouts.app')

@section('title', 'Member Registration')

@section('body')
<div class="relative min-h-screen bg-surface px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex flex-col items-center text-center sm:flex-row sm:items-start sm:text-left gap-4">
            <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA Logo" class="h-16 w-auto object-contain">
            <div>
                <h1 class="font-display text-2xl font-bold text-ink">Member Registration</h1>
                <p class="mt-1 text-sm text-muted">Submit your details for ASDA membership approval. NIC must be unique.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('member.register.store') }}" enctype="multipart/form-data" class="card p-5 sm:p-8">
            @csrf

            @if ($errors->any())
                <div class="alert-error mb-5">
                    <div>
                        <p class="font-semibold">Please fix the following:</p>
                        <ul class="mt-1 list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @include('members._form-fields', ['designations' => $designations, 'orgTree' => $orgTree ?? []])

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-between">
                <a href="{{ route('member.login') }}" class="btn-outline">Back to sign in</a>
                <button type="submit" class="btn-primary">Submit registration</button>
            </div>
        </form>

        <p class="mt-6 text-center text-xs font-semibold text-brand-blue">
            Developed by 1920 &amp; TFBS - Department of Agriculture
        </p>
    </div>
</div>
@endsection
