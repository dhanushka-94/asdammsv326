@extends('layouts.app')

@section('body')
<div class="relative min-h-screen lg:flex">
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-brand-blue-dark/50 backdrop-blur-sm lg:hidden"></div>

    <aside id="app-sidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-brand-blue text-white transition-transform duration-300 lg:static lg:translate-x-0">
        <div class="flex items-center justify-between border-b border-white/10 px-4 py-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white p-1.5 shadow-sm">
                    <img src="{{ asset('images/asda-logo.png') }}" alt="ASDA" class="h-full w-full object-contain">
                </div>
                <div class="min-w-0">
                    <p class="font-display text-base font-bold tracking-tight">ASDA MMS</p>
                    <p class="truncate text-xs text-slate-300">Member Management System</p>
                </div>
            </div>
            <button id="sidebar-close" type="button" class="rounded-lg p-2 text-slate-300 hover:bg-white/10 lg:hidden" aria-label="Close menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
            @if (auth()->user()->isReception())
                <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Attendance</p>
                <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Event Attendance
                </a>
            @else
                <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Main</p>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    Dashboard
                </a>

                <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Members</p>
                <a href="{{ route('admin.waiting-approvals.index') }}" class="{{ request()->routeIs('admin.waiting-approvals.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Waiting Approvals
                </a>
                <a href="{{ route('admin.members.index') }}" class="{{ request()->routeIs('admin.members.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Members
                </a>
                <a href="{{ route('admin.rejected-members.index') }}" class="{{ request()->routeIs('admin.rejected-members.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Rejected
                </a>

                <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">ASDA Events</p>
                <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    ASDA Events
                </a>
                @if (auth()->user()->canAccessAttendance())
                    <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Event Attendance
                    </a>
                @endif

                <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Settings</p>
                @php
                    $settingsSectionActive = request()->routeIs(
                        'admin.settings.*',
                        'admin.designations.*',
                        'admin.member-categories.*',
                        'admin.institutes.*',
                        'admin.sub-institutes.*',
                        'admin.sections.*',
                        'admin.users.*',
                        'admin.activity-logs.*'
                    );
                @endphp
                <div data-sidebar-dropdown data-open="1">
                    <button
                        type="button"
                        data-sidebar-dropdown-toggle
                        class="{{ $settingsSectionActive ? 'sidebar-link-active' : 'sidebar-link' }} w-full"
                        aria-expanded="true"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="flex-1 text-left">Settings</span>
                        <svg data-sidebar-dropdown-chevron class="h-4 w-4 shrink-0 rotate-180 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div data-sidebar-dropdown-panel class="mt-1 space-y-0.5 overflow-hidden">
                        <a href="{{ route('admin.designations.index') }}" class="{{ request()->routeIs('admin.designations.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                            Designations
                        </a>
                        <a href="{{ route('admin.member-categories.index') }}" class="{{ request()->routeIs('admin.member-categories.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                            Member Categories
                        </a>
                        <a href="{{ route('admin.institutes.index') }}" class="{{ request()->routeIs('admin.institutes.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                            Institutes
                        </a>
                        <a href="{{ route('admin.sub-institutes.index') }}" class="{{ request()->routeIs('admin.sub-institutes.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                            Sub-institutes
                        </a>
                        <a href="{{ route('admin.sections.index') }}" class="{{ request()->routeIs('admin.sections.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                            Sections
                        </a>
                        <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                            System Settings
                        </a>
                        @if (auth()->user()->canManageUsers())
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                                System Users
                            </a>
                            <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'sidebar-sublink-active' : 'sidebar-sublink' }}">
                                Activity Log
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('admin.profile.show') }}"
               class="mb-3 flex items-center gap-3 rounded-xl px-3 py-3 transition {{ request()->routeIs('admin.profile.*') ? 'bg-white/15 ring-1 ring-white/20' : 'bg-white/5 hover:bg-white/10' }}"
               title="View my profile">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-green text-sm font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-300">{{ auth()->user()->roleLabel() }}</p>
                </div>
                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn-outline w-full border-white/20 bg-transparent text-white hover:bg-white/10">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen flex-1 flex-col">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
            <div class="flex items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button id="sidebar-open" type="button" class="rounded-xl border border-slate-200 p-2 text-muted hover:bg-slate-50 lg:hidden" aria-label="Open menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="font-display text-lg font-bold tracking-tight text-ink sm:text-xl">@yield('page-title')</h1>
                        @hasSection('page-subtitle')
                            <p class="text-sm text-muted">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @yield('page-actions')
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (\App\Support\AppSettings::maintenanceMode())
                <div class="mb-4 rounded-xl border border-brand-orange/20 bg-brand-orange-soft px-4 py-3 text-sm text-brand-orange">
                    Maintenance mode is ON — public site is blocked.
                    @if (auth()->user()->canManageSettings())
                        <a href="{{ route('admin.settings.edit') }}" class="font-semibold underline">Manage settings</a>
                    @endif
                </div>
            @endif

            @if (session('success'))
                <div class="alert-success">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-slate-200/80 bg-white px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="max-w-3xl text-xs leading-relaxed text-muted">
                    Annual Symposium of the Department of Agriculture (ASDA) - Member Management System
                </p>
                <p class="shrink-0 text-xs font-semibold text-brand-blue">
                    Developed by 1920 &amp; TFBS - DOA
                </p>
            </div>
        </footer>
    </div>
</div>
@endsection
