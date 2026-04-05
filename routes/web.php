<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MeetingController as AdminMeetingController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\ConstitutionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleGuideController;
use App\Http\Controllers\Treasurer\ContributionController;
use App\Http\Controllers\Treasurer\DonationController;
use App\Http\Controllers\Treasurer\DuesController;
use App\Http\Controllers\Treasurer\ExpenseController;
use App\Http\Controllers\Treasurer\IncomeController;
use App\Http\Controllers\Treasurer\LoanController;
use App\Http\Controllers\Treasurer\LoanRepaymentController;
use App\Http\Controllers\Treasurer\MemberController;
use App\Http\Controllers\Treasurer\ReceiptController;
use App\Http\Controllers\Treasurer\ReportController;
use App\Http\Controllers\Treasurer\SpecialContributionController;
use App\Http\Controllers\Transparency\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('viewer.dashboard');
});

Route::get('/transparency', [TransparencyController::class, 'index'])->name('transparency');

Route::get('/viewer', [DashboardController::class, 'viewer'])->name('viewer.dashboard');
Route::get('/viewer/members', [DashboardController::class, 'viewerMembers'])->name('viewer.members');
Route::get('/viewer/members/suggestions', [DashboardController::class, 'viewerMemberSuggestions'])->name('viewer.members.suggestions');
Route::get('/user-manual', [HelpController::class, 'manual'])->name('help.manual');
Route::get('/user-manual/pdf', [HelpController::class, 'manualPdf'])->name('help.manual.pdf');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'active', 'role:admin,treasurer'])
    ->name('dashboard');

Route::get('/role-guide', [RoleGuideController::class, 'index'])
    ->middleware(['auth', 'active'])
    ->name('role-guide');

Route::get('/role-guide/admin', [RoleGuideController::class, 'admin'])
    ->middleware(['auth', 'active', 'role:admin'])
    ->name('role-guide.admin');

Route::get('/role-guide/treasurer', [RoleGuideController::class, 'treasurer'])
    ->middleware(['auth', 'active', 'role:admin,treasurer'])
    ->name('role-guide.treasurer');

Route::get('/role-guide/viewer', [RoleGuideController::class, 'viewer'])
    ->middleware(['auth', 'active', 'role:viewer'])
    ->name('role-guide.viewer');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active', 'role:admin,treasurer'])->group(function () {
    Route::get('members', [MemberController::class, 'index'])->name('members.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/ccm-summary.csv', [ReportController::class, 'ccmSummaryCsv'])->name('reports.ccm-summary.csv');
    Route::get('reports/ccm-summary.pdf', [ReportController::class, 'ccmSummaryPdf'])->name('reports.ccm-summary.pdf');
    Route::get('birthdays', [BirthdayController::class, 'index'])->name('birthdays.index');
    Route::get('help', [HelpController::class, 'index'])->name('help.index');
    Route::get('help/pdf', [HelpController::class, 'pdf'])->name('help.pdf');
});

Route::get('constitution', [ConstitutionController::class, 'index'])->name('constitution.index');
Route::get('constitution/download', [ConstitutionController::class, 'download'])->name('constitution.download');

Route::middleware(['auth', 'active', 'role:admin,treasurer'])->group(function () {
    Route::get('members/suggestions', [MemberController::class, 'suggestions'])->name('members.suggestions');
    Route::get('members/{member}/statement/print', [MemberController::class, 'statementPrint'])->name('members.statement.print');
    Route::get('members/{member}/statement/pdf', [MemberController::class, 'statementPdf'])->name('members.statement.pdf');
    Route::resource('members', MemberController::class)->except(['index']);
    Route::delete('members/{member}/force', [MemberController::class, 'forceDestroy'])->name('members.force-destroy');
    Route::get('dues', [DuesController::class, 'index'])->name('dues.index');
    Route::post('dues', [DuesController::class, 'store'])->name('dues.store');
    Route::get('dues/arrears.csv', [DuesController::class, 'arrearsCsv'])->name('dues.arrears.csv');
    Route::get('dues/arrears/print', [DuesController::class, 'arrearsPrint'])->name('dues.arrears.print');
    Route::resource('contributions', ContributionController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('special-contributions', SpecialContributionController::class)->only(['index', 'store']);
    Route::resource('donations', DonationController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('incomes', IncomeController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('loan-repayments', LoanRepaymentController::class)->only(['store', 'edit', 'update', 'destroy']);
    Route::resource('receipts', ReceiptController::class)->only(['index', 'show']);
    Route::get('receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
    Route::get('receipts/{receipt}/view', [ReceiptController::class, 'view'])->name('receipts.view');
    Route::post('receipts/regenerate', [ReceiptController::class, 'regenerateAll'])->name('receipts.regenerate');
    Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('reports/financial/print', [ReportController::class, 'financialPrint'])->name('reports.financial.print');
    Route::get('reports/financial/pdf', [ReportController::class, 'financialPdf'])->name('reports.financial.pdf');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::delete('/activity-logs', [AdminActivityLogController::class, 'clear'])->name('activity-logs.clear');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
    Route::patch('/announcements/{announcement}/toggle', [AdminAnnouncementController::class, 'toggle'])->name('announcements.toggle');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::get('/meetings', [AdminMeetingController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/{meeting}/edit', [AdminMeetingController::class, 'edit'])->name('meetings.edit');
    Route::post('/meetings', [AdminMeetingController::class, 'store'])->name('meetings.store');
    Route::put('/meetings/{meeting}', [AdminMeetingController::class, 'update'])->name('meetings.update');
    Route::patch('/meetings/{meeting}/toggle', [AdminMeetingController::class, 'toggle'])->name('meetings.toggle');
    Route::delete('/meetings/{meeting}', [AdminMeetingController::class, 'destroy'])->name('meetings.destroy');
});

require __DIR__.'/auth.php';
