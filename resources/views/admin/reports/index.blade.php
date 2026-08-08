@extends('layouts.dashboard')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Membership, events, and check-in analytics')

@section('content')
    @include('admin.reports._nav')

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Total members</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($memberStats['total']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Pending approvals</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-orange">{{ number_format($memberStats['pending']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Active enrollments</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-green">{{ number_format($eventStats['enrollments']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-sm font-medium text-muted">Total check-ins</p>
            <p class="mt-2 font-display text-3xl font-bold text-brand-blue">{{ number_format($eventStats['check_ins']) }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            @include('admin.reports._chart-card', [
                'title' => 'Registrations — last 30 days',
                'subtitle' => 'New member accounts created each day',
                'type' => 'line',
                'labels' => $charts['registration_trend']['labels'],
                'values' => $charts['registration_trend']['values'],
            ])
        </div>
        <div>
            @include('admin.reports._chart-card', [
                'title' => 'Registration status',
                'subtitle' => 'Approved / pending / rejected',
                'type' => 'doughnut',
                'labels' => $charts['registration_status']['labels'],
                'values' => $charts['registration_status']['values'],
                'height' => 'h-72',
            ])
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        @include('admin.reports._chart-card', [
            'title' => 'Check-ins by event',
            'subtitle' => 'Top events by attendance records',
            'type' => 'bar',
            'labels' => $charts['check_ins_by_event']['labels'],
            'values' => $charts['check_ins_by_event']['values'],
        ])
        @include('admin.reports._chart-card', [
            'title' => 'Items handed out',
            'subtitle' => 'Most given check-in items',
            'type' => 'bar',
            'labels' => $charts['items_given']['labels'],
            'values' => $charts['items_given']['values'],
        ])
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-muted">Approved members</p>
            <p class="mt-1 font-display text-2xl font-bold text-ink">{{ number_format($memberStats['approved']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-muted">Active members</p>
            <p class="mt-1 font-display text-2xl font-bold text-ink">{{ number_format($memberStats['active']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-muted">Total events</p>
            <p class="mt-1 font-display text-2xl font-bold text-ink">{{ number_format($eventStats['total']) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-muted">Active events</p>
            <p class="mt-1 font-display text-2xl font-bold text-ink">{{ number_format($eventStats['active']) }}</p>
        </div>
    </div>
@endsection
