<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        return view('help.index');
    }

    public function pdf(): Response
    {
        $pdf = Pdf::loadView('help.pdf');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ccm-user-manual.pdf"',
        ]);
    }

    public function manual(): View
    {
        return view('help.user_manual');
    }

    public function manualPdf(): Response
    {
        $pdf = Pdf::loadView('help.user_manual_pdf');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ccm-user-manual-public.pdf"',
        ]);
    }
}
