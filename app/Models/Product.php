<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'vote_score',
        'vote_count',
        'price',
        //'category_id',
        //'media_id',
        //'seller_id',
        //'comment_id',
        //'attribute_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function seller(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    public function comment(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    public function attribute(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }


}
