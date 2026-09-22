<?php

use App\Http\Controllers\AdminIntegrationEventController;
use App\Http\Controllers\AdminAcademicProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PortfolioActivityController;
use App\Http\Controllers\PortfolioIssueReportController;
use App\Http\Controllers\PortfolioParticipantController;
use App\Http\Controllers\PortfolioTagController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TridharmaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/pilih-role', [LoginController::class, 'selectRole'])->name('role.select');
    Route::post('/pilih-role', [LoginController::class, 'storeRole'])->name('role.store');
});

Route::prefix('dosen')->middleware(['auth', 'dosen.role:dosen,admin'])->name('dosen.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'dosen'])->name('dashboard');
    Route::get('/portofolio', [PortfolioActivityController::class, 'index'])->name('portfolio.index');
    Route::get('/portofolio/export', [PortfolioActivityController::class, 'export'])->name('portfolio.export');
    Route::get('/portofolio/create', [PortfolioActivityController::class, 'create'])->name('portfolio.create');
    Route::post('/portofolio', [PortfolioActivityController::class, 'store'])->name('portfolio.store');
    Route::get('/portofolio/{activity}', [PortfolioActivityController::class, 'show'])->name('portfolio.show');
    Route::get('/portofolio/{activity}/edit', [PortfolioActivityController::class, 'edit'])->name('portfolio.edit');
    Route::put('/portofolio/{activity}', [PortfolioActivityController::class, 'update'])->name('portfolio.update');
    Route::post('/portofolio/{activity}/submit', [PortfolioActivityController::class, 'submit'])->name('portfolio.submit');
    Route::post('/portofolio/{activity}/verify', [PortfolioActivityController::class, 'verify'])->name('portfolio.verify');
    Route::post('/portofolio/{activity}/revision', [PortfolioActivityController::class, 'requestRevision'])->name('portfolio.revision');
    Route::post('/portofolio/{activity}/reject', [PortfolioActivityController::class, 'reject'])->name('portfolio.reject');
    Route::post('/portofolio/{activity}/archive', [PortfolioActivityController::class, 'archive'])->name('portfolio.archive');
    Route::post('/portofolio/{activity}/issue-reports', [PortfolioIssueReportController::class, 'store'])->name('portfolio.issue-reports.store');
    Route::patch('/issue-reports/{issueReport}', [PortfolioIssueReportController::class, 'update'])->name('issue-reports.update');
    Route::post('/portofolio/{activity}/participants', [PortfolioParticipantController::class, 'store'])->name('portfolio.participants.store');
    Route::delete('/participants/{participant}', [PortfolioParticipantController::class, 'destroy'])->name('participants.destroy');
    Route::post('/portofolio/{activity}/tags', [PortfolioTagController::class, 'attach'])->name('portfolio.tags.attach');
    Route::delete('/portofolio/{activity}/tags/{tag}', [PortfolioTagController::class, 'detach'])->name('portfolio.tags.detach');
    Route::delete('/portofolio/{activity}', [PortfolioActivityController::class, 'destroy'])->name('portfolio.destroy');
    Route::get('/dokumen', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/dokumen/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/dokumen', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/dokumen/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::post('/inbox/{inboxItem}/read', [InboxController::class, 'markRead'])->name('inbox.read');
    Route::post('/inbox/{inboxItem}/status', [InboxController::class, 'transition'])->name('inbox.status');
    Route::get('/agenda', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::view('/profil-publik', 'placeholder', ['title' => 'Profil Publik'])->name('public-profile.index');
});

Route::middleware(['auth', 'dosen.role:dosen,admin'])->group(function (): void {
    Route::get('/tridharma', [TridharmaController::class, 'index'])->name('tridharma.index');
    Route::get('/tridharma/{domain}/export', [TridharmaController::class, 'export'])
        ->whereIn('domain', ['pendidikan', 'penelitian', 'pengabdian'])
        ->name('tridharma.domain.export');
    Route::get('/tridharma/{domain}', [TridharmaController::class, 'index'])
        ->whereIn('domain', ['pendidikan', 'penelitian', 'pengabdian'])
        ->name('tridharma.domain');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profil/pendidikan', [ProfileController::class, 'storeEducation'])->name('profile.educations.store');
    Route::put('/profil/pendidikan/{education}', [ProfileController::class, 'updateEducation'])->name('profile.educations.update');
    Route::delete('/profil/pendidikan/{education}', [ProfileController::class, 'destroyEducation'])->name('profile.educations.destroy');
    Route::post('/profil/identitas-ilmiah', [ProfileController::class, 'storeIdentifier'])->name('profile.identifiers.store');
    Route::put('/profil/identitas-ilmiah/{identifier}', [ProfileController::class, 'updateIdentifier'])->name('profile.identifiers.update');
    Route::delete('/profil/identitas-ilmiah/{identifier}', [ProfileController::class, 'destroyIdentifier'])->name('profile.identifiers.destroy');
    Route::post('/profil/visibilitas', [ProfileController::class, 'updateVisibility'])->name('profile.visibility.update');
});

Route::get('/profil-publik/{lecturerCoreId}', [ProfileController::class, 'publicProfile'])->name('profile.public');

Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
    ->middleware(['auth', 'dosen.role:admin'])
    ->name('admin.dashboard');

Route::prefix('admin')->middleware(['auth', 'dosen.role:admin'])->name('admin.')->group(function (): void {
    Route::post('/integration-events/{integrationEvent}/retry', [AdminIntegrationEventController::class, 'retry'])->name('integration-events.retry');
    Route::post('/integration-events/{integrationEvent}/ignore', [AdminIntegrationEventController::class, 'ignore'])->name('integration-events.ignore');
    Route::post('/lecturer-educations/{education}/verify', [AdminAcademicProfileController::class, 'verifyEducation'])->name('lecturer-educations.verify');
});

Route::get('/documents/{document}/download', DocumentDownloadController::class)
    ->middleware('auth')
    ->name('documents.download');
