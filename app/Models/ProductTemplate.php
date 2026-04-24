<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'slug',
        'description',
        'preview_data',
        'layout',
        'default_values',
        'editable_areas',
        'is_active',
    ];

    protected $casts = [
        'preview_data' => 'array',
        'layout' => 'array',
        'default_values' => 'array',
        'editable_areas' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customizationProjects(): HasMany
    {
        return $this->hasMany(CustomizationProject::class);
    }
}
