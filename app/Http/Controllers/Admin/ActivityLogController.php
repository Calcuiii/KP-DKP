<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->search, fn ($q) => $q->where('description', 'like', "%{$request->search}%"))
            ->when($request->admin, fn ($q) => $q->where('user_id', $request->admin))
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $admins = User::whereIn('role', ['admin', 'superadmin'])->get();
        $modules = ActivityLog::select('module')->distinct()->pluck('module');

        return view('pages.admin.activity-log', compact('logs', 'admins', 'modules'));
    }
}