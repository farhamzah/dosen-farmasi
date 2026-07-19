<?php

namespace App\Http\Controllers;

use App\Models\LecturerEducation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdminAcademicProfileController extends Controller
{
    public function verifyEducation(Request $request, LecturerEducation $education)
    {
        Gate::authorize('verify', $education);

        $education->update(['verification_status' => 'VERIFIED']);

        return back()->with('status', 'Riwayat pendidikan diverifikasi.');
    }
}
