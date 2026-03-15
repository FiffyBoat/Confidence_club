@extends('admin.layout')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <div class="text-muted small">Admin</div>
        <h2 class="mb-1">Settings</h2>
        <div class="text-muted">Control what viewers can see and manage documents.</div>
    </div>
    <span class="text-muted">{{ now()->format('D, d M Y') }}</span>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <strong>Club Constitution</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Upload the constitution so members can view the latest version.</p>

                    @if($settings['constitution_path'])
                        <div class="alert alert-info">
                            <div class="fw-semibold">Current file</div>
                            <div class="small text-muted">{{ $settings['constitution_name'] ?? 'constitution' }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Upload new file (PDF/DOC/DOCX)</label>
                        <input type="file" name="constitution_file" class="form-control">
                    </div>
                    <div class="form-text">Max size 10MB. The new file replaces the old one.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <strong>Transparency Viewer Settings</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Select what can be shown on the viewer transparency page.</p>

                    <div class="row g-2">
                        @php
                            $labels = [
                                'transparency_show_total_members' => 'Total Members',
                                'transparency_show_total_contributions' => 'Total Contributions',
                                'transparency_show_total_income' => 'Total Income',
                                'transparency_show_total_repayments' => 'Total Repayments',
                                'transparency_show_total_expenses' => 'Total Expenses',
                                'transparency_show_net_balance' => 'Net Balance',
                                'transparency_show_outstanding_loans' => 'Outstanding Loans',
                                'transparency_show_monthly_contributions' => 'Monthly Contributions (6 months)',
                                'transparency_show_monthly_expenses' => 'Monthly Expenses (6 months)',
                                'transparency_show_expense_breakdown' => 'Expense Breakdown',
                                'transparency_show_loan_summary' => 'Loan Summary',
                            ];
                        @endphp

                        @foreach($labels as $key => $label)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="1"
                                        id="{{ $key }}"
                                        name="transparency[{{ $key }}]"
                                        {{ $settings[$key] ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="{{ $key }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert alert-warning mt-3 mb-0">
                        If nothing is selected, viewers will see a notice instead of financial details.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <strong>Dues Settings</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Control the club start date and monthly dues amount used for arrears.</p>
                    <div class="mb-3">
                        <label class="form-label">Club Start Date</label>
                        <input type="date" name="club_start_date" class="form-control" value="{{ old('club_start_date', $settings['club_start_date']) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Dues Amount (GHS)</label>
                        <input type="number" step="0.01" name="monthly_dues_amount" class="form-control" value="{{ old('monthly_dues_amount', $settings['monthly_dues_amount']) }}" required>
                    </div>
                    <div class="form-text">Changes affect dues calculations and expected balances.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <strong>Viewer Dashboard Sections</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Choose which sections are visible on the viewer dashboard.</p>
                    @php
                        $viewerLabels = [
                            'viewer_show_constitution' => 'Constitution card',
                            'viewer_show_announcements' => 'Announcements',
                            'viewer_show_meetings' => 'Meeting schedule',
                            'viewer_show_directory' => 'Member directory (limited)',
                            'viewer_show_birthdays' => 'Birthday highlights',
                            'viewer_show_special_contributions' => 'Special contributions (paid)',
                            'viewer_show_transparency_snapshot' => 'Transparency snapshot',
                        ];
                    @endphp
                    <div class="row g-2">
                        @foreach($viewerLabels as $key => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="1"
                                        id="{{ $key }}"
                                        name="viewer[{{ $key }}]"
                                        {{ $settings[$key] ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label" for="{{ $key }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary">Save Settings</button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</form>
@endsection
