<?php

namespace App\Providers;

use App\Models\Member;
use App\Support\SriLankaDate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $timezone = config('app.timezone', SriLankaDate::TIMEZONE) ?: SriLankaDate::TIMEZONE;

        date_default_timezone_set($timezone);
        config(['app.timezone' => $timezone]);
        Carbon::setLocale(config('app.locale', 'en'));

        Password::defaults(fn () => Password::min(8));

        Paginator::useTailwind();

        Blade::stringable(function (\Carbon\CarbonInterface $value) {
            return SriLankaDate::datetime($value);
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return url(route('admin.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        View::composer('layouts.dashboard', function ($view) {
            $user = Auth::guard('web')->user();
            $pendingApprovalsCount = 0;

            if ($user && ! $user->isReception()) {
                $pendingApprovalsCount = Member::query()
                    ->where('registration_status', 'pending')
                    ->count();
            }

            $view->with('pendingApprovalsCount', $pendingApprovalsCount);
        });
    }
}
