<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, string $model, ?int $modelId = null, array $oldValues = null, array $newValues = null, ?string $message = null): void
    {
        $user = Auth::user();
        if (!$user) {
            return; // Only log for authenticated actions
        }

        // Attach human-friendly message if provided
        if ($message) {
            $newValues = $newValues ?? [];
            $newValues['message'] = $message;
        }

        ActivityLog::create([
            'business_id' => $user->current_business_id,
            'user_id' => $user->id,
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
