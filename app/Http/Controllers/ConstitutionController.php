<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConstitutionController extends Controller
{
    public function index(): View
    {
        $path = Setting::getValue('constitution_path');
        $name = Setting::getValue('constitution_name') ?? 'constitution';

        $exists = $path ? Storage::disk('public')->exists($path) : false;

        return view('constitution.index', [
            'constitutionExists' => $exists,
            'constitutionName' => $name,
        ]);
    }

    public function download(): StreamedResponse
    {
        $path = Setting::getValue('constitution_path');
        $name = Setting::getValue('constitution_name') ?? 'constitution.pdf';

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Constitution file not found.');
        }

        return Storage::disk('public')->download($path, $name);
    }
}
