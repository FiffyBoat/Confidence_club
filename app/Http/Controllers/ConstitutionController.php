<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\ClubFiles;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConstitutionController extends Controller
{
    public function index(): View
    {
        $path = Setting::getValue('constitution_path');
        $name = Setting::getValue('constitution_name') ?? 'constitution';

        $exists = ClubFiles::exists($path);

        return view('constitution.index', [
            'constitutionExists' => $exists,
            'constitutionName' => $name,
        ]);
    }

    public function download(): StreamedResponse
    {
        $path = Setting::getValue('constitution_path');
        $name = Setting::getValue('constitution_name') ?? 'constitution.pdf';

        if (! ClubFiles::exists($path)) {
            abort(404, 'Constitution file not found.');
        }

        return ClubFiles::download($path, $name);
    }
}
