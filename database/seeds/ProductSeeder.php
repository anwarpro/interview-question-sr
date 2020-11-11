<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Product::class, 10)->create()->each(function ($product) {
            //insert variants
            $variants = Variant::all();
            $colors = ['red', 'green'];
            $sizes = ['xl', 'sm'];

            foreach ($variants as $variant) {
                if ($variant->title == 'Color') {
                    foreach ($colors as $color) {
                        $product->variants()->attach($variant, [
                            'variant' => $color
                        ]);
                    }
                } else if ($variant->title == 'Size') {
                    foreach ($sizes as $size) {
                        $product->variants()->attach($variant, [
                            'variant' => $size
                        ]);
                    }
                }
            }

            //insert stock
            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $productPrice = new ProductVariantPrice();
                    $productPrice->product_variant_one = ProductVariant::where('variant', $color)
                        ->where('product_id', $product->id)->get()->first()->id;

                    $productPrice->product_variant_two = ProductVariant::where('variant', $size)
                        ->where('product_id', $product->id)->get()->first()->id;

                    $productPrice->price = 150.00;
                    $productPrice->stock = 54.00;
                    $productPrice->product_id = $product->id;

                    $productPrice->save();
                }
            }

        });
    }
}
