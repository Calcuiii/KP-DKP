<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternshipLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipLocationController extends Controller
{
    public function index(): View
    {
        $locations = InternshipLocation::orderBy('display_order')->get();

        $metrics = [
            'total' => $locations->count(),
            'available' => $locations->where('quota_status', InternshipLocation::QUOTA_AVAILABLE)->count(),
            'limited' => $locations->where('quota_status', InternshipLocation::QUOTA_LIMITED)->count(),
            'full' => $locations->whereIn('quota_status', [InternshipLocation::QUOTA_FULL, InternshipLocation::QUOTA_UNAVAILABLE])->count(),
            'total_quota' => $locations->sum('quota_available'),
        ];

        return view('pages.admin.internship-locations', compact('locations', 'metrics'));
    }

    public function update(Request $request, InternshipLocation $location): RedirectResponse
    {
        $request->validate([
            'quota_status' => 'required|in:available,limited,full,unavailable,unknown',
            'quota_available' => 'nullable|integer|min:0',
        ]);

        $location->update([
            'quota_status' => $request->quota_status,
            'quota_available' => $request->quota_available,
            'quota_updated_at' => now(),
        ]);

        return back()->with('status', "Status kuota \"{$location->name}\" berhasil diperbarui.");
    }
}