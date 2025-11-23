<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $table = 'sellers';

    protected $fillable = [
        'name',
        'date',
        'vote_score',
        'vote_score_title',
        'vote_count',
        'supply_in_time_percent',
        'supply_in_delivery_percent',
        'no_refund_percent',
    ];
    protected $primaryKey = 'id';

    public function product(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
