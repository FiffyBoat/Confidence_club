<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function index(): View
    {
        $meetings = Meeting::query()
            ->orderBy('meeting_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.meetings', compact('meetings'));
    }

    public function edit(Meeting $meeting): View
    {
        return view('admin.meetings_edit', compact('meeting'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'meeting_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Meeting::create([
            'title' => $validated['title'],
            'meeting_at' => $validated['meeting_at'],
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting scheduled.');
    }

    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'meeting_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $meeting->update([
            'title' => $validated['title'],
            'meeting_at' => $validated['meeting_at'],
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting updated.');
    }

    public function toggle(Meeting $meeting): RedirectResponse
    {
        $meeting->update(['is_active' => ! $meeting->is_active]);

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting status updated.');
    }

    public function destroy(Meeting $meeting): RedirectResponse
    {
        $meeting->delete();

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting deleted.');
    }
}
