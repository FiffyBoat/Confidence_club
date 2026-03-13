<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(30);

        return view('admin.activity-logs.index', compact('logs'));
    }

    public function clear(): RedirectResponse
    {
        DB::table('activity_logs')->truncate();

        return redirect()
            ->route('admin.activity-logs.index')
            ->with('success', 'Activity logs cleared.');
    }
}
