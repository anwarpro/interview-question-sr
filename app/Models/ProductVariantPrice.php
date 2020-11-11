<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function product_variant_one()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one');
    }

    public function product_variant_two()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two');
    }

    public function product_variant_three()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three');
    }

    public function getTitle($product_id)
    {
        return $this->product_variant_one()->where('product_id', $product_id)->first()->variant . '/' . $this->product_variant_two()->where('product_id', $product_id)->first()->variant;
    }
}
