<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\InboxItem;
use App\Models\PortfolioActivity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dosen(Request $request)
    {
        $lecturerId = (string) $request->user()->core_lecturer_id;

        return view('dashboard.dosen', [
            'portfolioCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->count(),
            'draftCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->where('verification_status', 'DRAFT')->count(),
            'revisionCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->where('verification_status', 'REVISION_REQUIRED')->count(),
            'verifiedCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->whereIn('verification_status', ['ADMIN_VERIFIED', 'SYSTEM_VERIFIED'])->count(),
            'systemVerifiedCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->where('verification_status', 'SYSTEM_VERIFIED')->count(),
            'pendingCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->where('verification_status', 'SUBMITTED')->count(),
            'documentCount' => Document::query()->where('lecturer_core_id', $lecturerId)->count(),
            'currentYearActivityCount' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->whereYear('created_at', now()->year)->count(),
            'unreadInboxCount' => InboxItem::query()->where('lecturer_core_id', $lecturerId)->where('status', 'UNREAD')->count(),
            'upcomingAgendaCount' => CalendarEvent::query()->where('lecturer_core_id', $lecturerId)->where('starts_at', '>=', now())->count(),
            'actionItems' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->whereIn('verification_status', ['DRAFT', 'REVISION_REQUIRED'])->latest()->limit(5)->get(),
            'recentActivities' => PortfolioActivity::query()->where('lecturer_core_id', $lecturerId)->latest()->limit(5)->get(),
            'upcomingEvents' => CalendarEvent::query()->where('lecturer_core_id', $lecturerId)->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get(),
        ]);
    }

    public function admin()
    {
        return redirect()->route('filament.admin.pages.admin-dashboard');
    }
}
