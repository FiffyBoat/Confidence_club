<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $roles = ['admin', 'treasurer'];

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['admin', 'treasurer'])],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own admin role.',
            ])->withInput();
        }

        if ($user->id === $request->user()->id && ! $validated['is_active']) {
            return back()->withErrors([
                'is_active' => 'You cannot disable your own account.',
            ])->withInput();
        }

        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors([
                    'role' => 'At least one admin account is required.',
                ])->withInput();
            }
        }

        $user->update($validated);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Updated User Role/Profile',
            'description' => 'Updated user '.$user->email.' (role: '.$validated['role'].')',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors([
                'status' => 'You cannot disable your own account.',
            ]);
        }

        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $newStatus ? 'Enabled User' : 'Disabled User',
            'description' => ($newStatus ? 'Enabled ' : 'Disabled ').$user->email,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User status updated.');
    }
}
