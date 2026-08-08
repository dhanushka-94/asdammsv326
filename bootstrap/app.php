<?php

/**
 * ASDA Member Management System (MMS)
 *
 * Full Stack Developers:
 * - Dhanushka Bandara
 * - Greshan Bandara
 *
 * See AUTHORS and CREDITS.md for project attribution (not shown in the UI).
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Plesk nginx → Apache terminates SSL; Laravel must see HTTPS for secure session cookies.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'member.approved' => \App\Http\Middleware\EnsureMemberApproved::class,
            'member.password' => \App\Http\Middleware\EnsureMemberPasswordChanged::class,
            'user.password' => \App\Http\Middleware\EnsureSystemUserPasswordChanged::class,
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'activity' => \App\Http\Middleware\RecordActivity::class,
            'desk.unlocked' => \App\Http\Middleware\EnsureAttendanceDeskIsUnlocked::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EnsurePublicAccessIsOpen::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return route('member.login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if (Auth::guard('web')->check()) {
                return route(Auth::guard('web')->user()->homeRoute());
            }

            if (Auth::guard('member')->check()) {
                $member = Auth::guard('member')->user();

                return route($member->homeRoute());
            }

            return route('member.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
