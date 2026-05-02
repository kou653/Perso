<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomizationProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_template_id',
        'customer_name',
        'customer_email',
        'status',
        'prompt',
        'customization_data',
        'latest_render',
    ];

    protected $casts = [
        'customization_data' => 'array',
        'latest_render' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'product_template_id');
    }
}
