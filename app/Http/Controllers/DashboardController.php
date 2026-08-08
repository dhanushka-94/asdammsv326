<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_members' => Member::count(),
            'pending_members' => Member::where('registration_status', 'pending')->count(),
            'approved_members' => Member::where('registration_status', 'approved')->count(),
            'active_members' => Member::where('status', 'active')->count(),
        ];

        $recentMembers = Member::with('designation')->latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentMembers', 'recentUsers'));
    }
}
