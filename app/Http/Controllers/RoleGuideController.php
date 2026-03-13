<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoleGuideController extends Controller
{
    public function index(): RedirectResponse
    {
        $role = Auth::user()->role ?? 'viewer';

        return match ($role) {
            'admin' => redirect()->route('role-guide.admin'),
            'treasurer' => redirect()->route('role-guide.treasurer'),
            default => redirect()->route('role-guide.viewer'),
        };
    }

    public function admin(): View
    {
        return view('roles.admin');
    }

    public function treasurer(): View
    {
        return view('roles.treasurer');
    }

    public function viewer(): View
    {
        return view('roles.viewer');
    }
}
