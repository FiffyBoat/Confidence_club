<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const SETTING_KEYS = [
        'transparency_show_total_members',
        'transparency_show_total_contributions',
        'transparency_show_total_income',
        'transparency_show_total_repayments',
        'transparency_show_total_expenses',
        'transparency_show_net_balance',
        'transparency_show_outstanding_loans',
        'transparency_show_monthly_contributions',
        'transparency_show_monthly_expenses',
        'transparency_show_expense_breakdown',
        'transparency_show_loan_summary',
    ];

    private const VIEWER_KEYS = [
        'viewer_show_constitution',
        'viewer_show_announcements',
        'viewer_show_meetings',
        'viewer_show_directory',
        'viewer_show_birthdays',
        'viewer_show_special_contributions',
        'viewer_show_transparency_snapshot',
    ];

    public function index(): View
    {
        $settings = [
            'constitution_path' => Setting::getValue('constitution_path'),
            'constitution_name' => Setting::getValue('constitution_name'),
        ];

        foreach (self::SETTING_KEYS as $key) {
            $settings[$key] = Setting::getBool($key, false);
        }

        foreach (self::VIEWER_KEYS as $key) {
            $default = $key !== 'viewer_show_transparency_snapshot';
            $settings[$key] = Setting::getBool($key, $default);
        }

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'constitution_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx'],
            'transparency' => ['nullable', 'array'],
            'viewer' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('constitution_file')) {
            $file = $validated['constitution_file'];
            $previousPath = Setting::getValue('constitution_path');
            if ($previousPath) {
                Storage::disk('public')->delete($previousPath);
            }

            $path = $file->store('constitution', 'public');

            Setting::setValue('constitution_path', $path);
            Setting::setValue('constitution_name', $file->getClientOriginalName());
        }

        $selected = array_keys($validated['transparency'] ?? []);
        $viewerSelected = array_keys($validated['viewer'] ?? []);

        foreach (self::SETTING_KEYS as $key) {
            $value = in_array($key, $selected, true) ? '1' : '0';
            Setting::setValue($key, $value);
        }

        foreach (self::VIEWER_KEYS as $key) {
            $value = in_array($key, $viewerSelected, true) ? '1' : '0';
            Setting::setValue($key, $value);
        }

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Settings updated.');
    }
}
