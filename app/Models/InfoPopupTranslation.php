<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['info_popup_id', 'locale', 'title', 'body'])]
class InfoPopupTranslation extends Model
{
    public function infoPopup(): BelongsTo
    {
        return $this->belongsTo(InfoPopup::class);
    }
}
