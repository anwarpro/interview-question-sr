<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function variants()
    {
        return $this->belongsToMany(Variant::class, 'product_variants')
            ->withPivot('variant')
            ->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function product_variant_price()
    {
        return $this->hasMany(ProductVariantPrice::class, 'product_id');
    }

    public function getEditDataAttribute()
    {
        $productVariants = $this->variants()->withPivot('variant')->get()->groupBy('id');

        foreach ($productVariants as $key => $variant) {
            $product_variant[] = [
                'option' => $key,
                'tags' => $variant->pluck('pivot.variant')
            ];
        }

        $productPrices = $this->product_variant_price()
            ->get();

        foreach ($productPrices as $productPrice) {
            $product_variant_prices[] = [
                'title' => $productPrice->getTitle($this->id),
                'price' => $productPrice->price,
                'stock' => $productPrice->stock
            ];
        }

        return [
            'product_id' => $this->id,
            'product_name' => $this->title,
            'product_sku' => $this->sku,
            'product_description' => $this->description,
            'images' => $this->images ?? [],
            'product_variant' => $product_variant ?? [],
            'product_variant_prices' => $product_variant_prices ?? []
        ];

    }

}
