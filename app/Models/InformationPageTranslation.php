<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'information_page_id',
    'locale',
    'title',
    'slug',
    'body',
    'seo_title',
    'seo_description',
])]
class InformationPageTranslation extends Model
{
    public function page(): BelongsTo
    {
        return $this->belongsTo(InformationPage::class, 'information_page_id');
    }
}
