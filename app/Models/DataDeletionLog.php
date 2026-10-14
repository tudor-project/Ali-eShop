<?php

namespace App\Models;

use App\Enums\DataDeletionAction;
use App\Enums\DataDeletionReason;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['subject_type', 'subject_id', 'action', 'reason'])]
class DataDeletionLog extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => DataDeletionAction::class,
            'reason' => DataDeletionReason::class,
        ];
    }
}
