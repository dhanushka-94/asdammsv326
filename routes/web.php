<?php

/**
 * ASDA MMS routes.
 * Full Stack Developers: Dhanushka Bandara, Greshan Bandara
 */

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CheckInItemController;
use App\Http\Controllers\Admin\CheckedInMemberController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\InstituteController;
use App\Http\Controllers\Admin\MemberCategoryController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SetPasswordController as AdminSetPasswordController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubInstituteController;
use App\Http\Controllers\Admin\RejectedMemberController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WaitingApprovalController as AdminWaitingApprovalController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Member\EventPoolController;
use App\Http\Controllers\Member\EventInvitationController;
use App\Http\Controllers\Member\LoginController as MemberLoginController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\RegistrationController;
use App\Http\Controllers\Member\SetPasswordController as MemberSetPasswordController;
use App\Http\Controllers\Member\WaitingApprovalController as MemberWaitingApprovalController;
use App\Http\Controllers\UserController;
use App\Support\UserRole;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public member area (default domain access)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:member')->group(function () {
    Route::get('/', [MemberLoginController::class, 'show'])->name('member.login');
    Route::post('/login', [MemberLoginController::class, 'store'])->name('member.login.store');
    Route::get('/register', [RegistrationController::class, 'create'])->name('member.register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('member.register.store');
});

Route::middleware(['auth:member', 'activity'])->prefix('member')->name('member.')->group(function () {
    Route::post('/logout', [MemberLoginController::class, 'destroy'])->name('logout');

    Route::get('/waiting-approval', [MemberWaitingApprovalController::class, 'show'])->name('waiting-approval');
    Route::get('/waiting-approval/status', [MemberWaitingApprovalController::class, 'status'])->name('waiting-approval.status');
    Route::get('/waiting-approval/qr', [MemberWaitingApprovalController::class, 'downloadQr'])->name('waiting-approval.qr');
    Route::redirect('/lobby', '/member/waiting-approval');

    Route::middleware('member.approved')->group(function () {
        Route::get('/set-password', [MemberSetPasswordController::class, 'edit'])->name('password.edit');
        Route::put('/set-password', [MemberSetPasswordController::class, 'update'])->name('password.update');

        Route::middleware('member.password')->group(function () {
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
            Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::get('/profile/qr', [ProfileController::class, 'downloadQr'])->name('profile.qr');

            Route::get('/events', [EventPoolController::class, 'index'])->name('events.index');
            Route::get('/events/{event}', [EventPoolController::class, 'show'])->name('events.show');
            Route::post('/events/{event}/enroll', [EventPoolController::class, 'enroll'])->name('events.enroll');
            Route::delete('/events/{event}/unenroll', [EventPoolController::class, 'unenroll'])->name('events.unenroll');
            Route::get('/events/{event}/invitation/letter', [EventInvitationController::class, 'letter'])->name('events.invitation.letter');
            Route::get('/events/{event}/invitation/card', [EventInvitationController::class, 'card'])->name('events.invitation.card');
        });
    });
});

/*
|--------------------------------------------------------------------------
| System access (admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('/', [AdminLoginController::class, 'show'])->name('login');
        Route::post('/', [AdminLoginController::class, 'store'])->name('login.store');
        Route::redirect('/login', '/admin');

        Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
    });

    Route::middleware(['auth:web', 'role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN.','.UserRole::VIEWER.','.UserRole::RECEPTION, 'activity', 'desk.unlocked'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/set-password', [AdminSetPasswordController::class, 'edit'])->name('set-password.edit');
        Route::put('/set-password', [AdminSetPasswordController::class, 'update'])->name('set-password.update');

        Route::middleware('user.password')->group(function () {
            Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.show');
            Route::get('/profile/edit', [AdminProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/desk-pin', [AdminProfileController::class, 'updateDeskPin'])->name('profile.desk-pin');

            Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN.','.UserRole::RECEPTION)->group(function () {
                Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
                Route::get('attendance/lock', [AttendanceController::class, 'lockScreen'])->name('attendance.lock');
                Route::post('attendance/lock', [AttendanceController::class, 'lock'])->name('attendance.lock.store');
                Route::post('attendance/unlock', [AttendanceController::class, 'unlock'])->name('attendance.unlock');
                Route::get('attendance/events/{event}/setup', [AttendanceController::class, 'setup'])->name('attendance.setup');
                Route::post('attendance/events/{event}/start', [AttendanceController::class, 'start'])->name('attendance.start');
                Route::get('attendance/events/{event}', [AttendanceController::class, 'desk'])->name('attendance.desk');
                Route::post('attendance/events/{event}/lookup', [AttendanceController::class, 'lookup'])->name('attendance.lookup');
                Route::post('attendance/events/{event}/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
                Route::post('attendance/events/{event}/update-items', [AttendanceController::class, 'updateItems'])->name('attendance.update-items');

                Route::get('checked-in', [CheckedInMemberController::class, 'index'])->name('checked-in.index');
                Route::get('checked-in/members/{member}', [CheckedInMemberController::class, 'show'])->name('checked-in.show');
            });

            Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN.','.UserRole::VIEWER)->group(function () {
                Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

                Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
                Route::get('reports/members', [ReportController::class, 'members'])->name('reports.members');
                Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
                Route::get('reports/items', [ReportController::class, 'items'])->name('reports.items');

                Route::get('waiting-approvals', [AdminWaitingApprovalController::class, 'index'])->name('waiting-approvals.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::post('waiting-approvals/bulk', [AdminWaitingApprovalController::class, 'bulk'])->name('waiting-approvals.bulk');
                });

                Route::get('rejected-members', [RejectedMemberController::class, 'index'])->name('rejected-members.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::post('rejected-members/bulk', [RejectedMemberController::class, 'bulk'])->name('rejected-members.bulk');
                    Route::post('members/{member}/re-accept', [AdminMemberController::class, 'reAccept'])->name('members.re-accept');
                });

                Route::get('events', [AdminEventController::class, 'index'])->name('events.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('events/create', [AdminEventController::class, 'create'])->name('events.create');
                    Route::post('events', [AdminEventController::class, 'store'])->name('events.store');
                });
                Route::get('events/{event}', [AdminEventController::class, 'show'])->name('events.show');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('events/{event}/edit', [AdminEventController::class, 'edit'])->name('events.edit');
                    Route::put('events/{event}', [AdminEventController::class, 'update'])->name('events.update');
                    Route::delete('events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
                });

                Route::get('members', [AdminMemberController::class, 'index'])->name('members.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('members/create', [AdminMemberController::class, 'create'])->name('members.create');
                    Route::post('members', [AdminMemberController::class, 'store'])->name('members.store');
                });
                Route::middleware('role:'.UserRole::SUPER_ADMIN)->group(function () {
                    Route::get('members/import', [AdminMemberController::class, 'importForm'])->name('members.import');
                    Route::get('members/import/template', [AdminMemberController::class, 'importTemplate'])->name('members.import.template');
                    Route::post('members/import', [AdminMemberController::class, 'import'])->name('members.import.store');
                    Route::post('members/bulk', [AdminMemberController::class, 'bulk'])->name('members.bulk');
                });
                Route::get('members/{member}', [AdminMemberController::class, 'show'])->name('members.show');
                Route::get('members/{member}/qr', [AdminMemberController::class, 'downloadQr'])->name('members.qr');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('members/{member}/edit', [AdminMemberController::class, 'edit'])->name('members.edit');
                    Route::put('members/{member}', [AdminMemberController::class, 'update'])->name('members.update');
                    Route::delete('members/{member}', [AdminMemberController::class, 'destroy'])->name('members.destroy');
                    Route::post('members/{member}/approve', [AdminMemberController::class, 'approve'])->name('members.approve');
                    Route::post('members/{member}/reject', [AdminMemberController::class, 'reject'])->name('members.reject');
                    Route::post('members/{member}/events/{event}/kick', [AdminMemberController::class, 'kickFromEvent'])->name('members.events.kick');
                    Route::post('members/{member}/reset-password', [AdminMemberController::class, 'resetPassword'])->name('members.reset-password');
                    Route::post('members/{member}/require-password-change', [AdminMemberController::class, 'requirePasswordChange'])->name('members.require-password-change');
                });

                Route::get('designations', [DesignationController::class, 'index'])->name('designations.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('designations/import', [DesignationController::class, 'importForm'])->name('designations.import');
                    Route::get('designations/import/template', [DesignationController::class, 'importTemplate'])->name('designations.import.template');
                    Route::post('designations/import', [DesignationController::class, 'import'])->name('designations.import.store');
                    Route::get('designations/create', [DesignationController::class, 'create'])->name('designations.create');
                    Route::post('designations', [DesignationController::class, 'store'])->name('designations.store');
                    Route::get('designations/{designation}/edit', [DesignationController::class, 'edit'])->name('designations.edit');
                    Route::put('designations/{designation}', [DesignationController::class, 'update'])->name('designations.update');
                    Route::delete('designations/{designation}', [DesignationController::class, 'destroy'])->name('designations.destroy');
                });

                Route::get('institutes', [InstituteController::class, 'index'])->name('institutes.index');
                Route::get('sub-institutes', [SubInstituteController::class, 'index'])->name('sub-institutes.index');
                Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('institutes/import', [InstituteController::class, 'importForm'])->name('institutes.import');
                    Route::get('institutes/import/template', [InstituteController::class, 'importTemplate'])->name('institutes.import.template');
                    Route::post('institutes/import', [InstituteController::class, 'import'])->name('institutes.import.store');
                    Route::get('institutes/create', [InstituteController::class, 'create'])->name('institutes.create');
                    Route::post('institutes', [InstituteController::class, 'store'])->name('institutes.store');
                    Route::get('institutes/{institute}/edit', [InstituteController::class, 'edit'])->name('institutes.edit');
                    Route::put('institutes/{institute}', [InstituteController::class, 'update'])->name('institutes.update');
                    Route::delete('institutes/{institute}', [InstituteController::class, 'destroy'])->name('institutes.destroy');

                    Route::get('sub-institutes/import', [SubInstituteController::class, 'importForm'])->name('sub-institutes.import');
                    Route::get('sub-institutes/import/template', [SubInstituteController::class, 'importTemplate'])->name('sub-institutes.import.template');
                    Route::post('sub-institutes/import', [SubInstituteController::class, 'import'])->name('sub-institutes.import.store');
                    Route::get('sub-institutes/create', [SubInstituteController::class, 'create'])->name('sub-institutes.create');
                    Route::post('sub-institutes', [SubInstituteController::class, 'store'])->name('sub-institutes.store');
                    Route::get('sub-institutes/{sub_institute}/edit', [SubInstituteController::class, 'edit'])->name('sub-institutes.edit');
                    Route::put('sub-institutes/{sub_institute}', [SubInstituteController::class, 'update'])->name('sub-institutes.update');
                    Route::delete('sub-institutes/{sub_institute}', [SubInstituteController::class, 'destroy'])->name('sub-institutes.destroy');

                    Route::get('sections/import', [SectionController::class, 'importForm'])->name('sections.import');
                    Route::get('sections/import/template', [SectionController::class, 'importTemplate'])->name('sections.import.template');
                    Route::post('sections/import', [SectionController::class, 'import'])->name('sections.import.store');
                    Route::get('sections/create', [SectionController::class, 'create'])->name('sections.create');
                    Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
                    Route::get('sections/{section}/edit', [SectionController::class, 'edit'])->name('sections.edit');
                    Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
                    Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
                });

                Route::get('member-categories', [MemberCategoryController::class, 'index'])->name('member-categories.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('member-categories/create', [MemberCategoryController::class, 'create'])->name('member-categories.create');
                    Route::post('member-categories', [MemberCategoryController::class, 'store'])->name('member-categories.store');
                    Route::get('member-categories/{member_category}/edit', [MemberCategoryController::class, 'edit'])->name('member-categories.edit');
                    Route::put('member-categories/{member_category}', [MemberCategoryController::class, 'update'])->name('member-categories.update');
                    Route::delete('member-categories/{member_category}', [MemberCategoryController::class, 'destroy'])->name('member-categories.destroy');
                });

                Route::get('check-in-items', [CheckInItemController::class, 'index'])->name('check-in-items.index');
                Route::middleware('role:'.UserRole::SUPER_ADMIN.','.UserRole::ADMIN)->group(function () {
                    Route::get('check-in-items/create', [CheckInItemController::class, 'create'])->name('check-in-items.create');
                    Route::post('check-in-items', [CheckInItemController::class, 'store'])->name('check-in-items.store');
                    Route::get('check-in-items/{check_in_item}/edit', [CheckInItemController::class, 'edit'])->name('check-in-items.edit');
                    Route::put('check-in-items/{check_in_item}', [CheckInItemController::class, 'update'])->name('check-in-items.update');
                    Route::delete('check-in-items/{check_in_item}', [CheckInItemController::class, 'destroy'])->name('check-in-items.destroy');
                });

                Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
                Route::middleware('role:'.UserRole::SUPER_ADMIN)->group(function () {
                    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
                    Route::resource('users', UserController::class);
                    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
                    Route::post('users/{user}/require-password-change', [UserController::class, 'requirePasswordChange'])->name('users.require-password-change');
                    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
                });
            });
        });
    });
});
