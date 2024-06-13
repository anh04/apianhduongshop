<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'prd_id';
    protected $fillable = [
        'prd_batch_code',
        'prd_type',
        'prd_name',
        'prd_sku',
        'prd_quantity',
        'prd_disctiption',
        'prd_short_disctiption',
        'prd_att',

        'prd_price',
        'prd_regular_price',

        'prd_sex',
        'prd_size',
        'prd_color',
        'multi_colors',
        'multi_sizes',
        'multi_sexes',
        'prd_tag',
        'prd_relative'

    ];

    protected $guarded = [
        'prd_img',
        'prd_library'];

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }
}
