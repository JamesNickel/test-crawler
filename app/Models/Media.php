<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'url',
        'type', // photo, video
        'is_active'
    ];
    protected $primaryKey = 'id';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
