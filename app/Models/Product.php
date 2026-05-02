<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'base_image_url',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function templates(): HasMany
    {
        return $this->hasMany(ProductTemplate::class);
    }

    public function customizationProjects(): HasMany
    {
        return $this->hasMany(CustomizationProject::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
