<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * List the recorded admin actions.
     */
    public function index(Request $request): Response
    {
        $logs = AuditLog::query()
            ->with('user')
            ->recent()
            ->paginate(50);

        return Inertia::render('Admin/AuditLog/Index', [
            'logs' => $logs,
        ]);
    }
}
