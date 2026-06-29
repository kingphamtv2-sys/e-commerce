<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService): View
    {
        return view('admin.dashboard', [
            ...$dashboardService->data($request->string('range')->toString()),
            'dashboardService' => $dashboardService,
        ]);
    }
}
