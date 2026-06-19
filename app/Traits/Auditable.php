<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    private function logAudit(object $model, string $action, string $description, array $changes = []): void
    {
        if (! method_exists($model, 'getKey')) {
            return;
        }

        $user = auth()->user();

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'description' => $description,
            'changes' => $changes ?: null,
        ]);
    }
}
