<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $businessId = Auth::user()->current_business_id;

        $query = ActivityLog::with('user')
            ->where('business_id', $businessId)
            ->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Distinct filter options
        $actions = ActivityLog::where('business_id', $businessId)
            ->select('action')->distinct()->orderBy('action')->pluck('action');
        $models = ActivityLog::where('business_id', $businessId)
            ->select('model')->distinct()->orderBy('model')->pluck('model');
        $userIds = ActivityLog::where('business_id', $businessId)
            ->select('user_id')->distinct()->pluck('user_id');
        $users = User::whereIn('id', $userIds)->orderBy('name')->get(['id','name']);

        return view('activity.index', compact('logs', 'actions', 'models', 'users'));
    }
}
