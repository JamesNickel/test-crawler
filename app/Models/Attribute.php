<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attribute extends Model
{
    protected $table = 'attributes';

    protected $fillable = [
        'label',
        'value',
    ];
    protected $primaryKey = 'id';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
