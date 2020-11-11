<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $products = Product::query();

        $title = null;
        if ($request->has('title') && $request->title) {
            $title = $request->title;
            $products = $products->where('title', 'LIKE', '%' . $request->title . '%');
        }

        if ($request->has('date') && $request->date) {
            $products = $products->whereDate('created_at', '=', Carbon::parse($request->date));
        }

//        if ($request->has('variant')) {
//            $products = $products->varients()->wherePivot('variant', $request->has('variant'));
//        }
//
//        if ($request->has('price_from') && $request->price_from) {
//            $products = $products->where('title', $request->price_from);
//            $products = $products->product_variant_prices()->where('price', '>', $request->price_from);
//        }
//
//        if ($request->has('price_to')) {
//            $products = $products->product_variant_prices()->where('price', '>', $request->price_to);
//        }

        $products = $products->paginate(2);

//        dd($products);
        return view('products.index', compact('products', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $product = Product::create($request->only('title', 'sku', 'description'));

        $productImages = $request->product_image;

        if (!empty($productImages)) {
            foreach ($productImages as $image) {
                $productImage = new ProductImage;
                $productImage->file_path = $image;
                $productImage->product_id = $product->id;

                $productImage->save();
            }
        }

        $variants = $request->product_variant;

        $product_variant_prices = $request->product_variant_prices;

        foreach ($variants as $variant) {
            foreach ($variant['tags'] as $tag) {
                $product->variants()->attach($variant['option'], ['variant' => $tag]);
            }
        }

        foreach ($product_variant_prices as $price) {

            $titles = explode('/', $price['title']);
            $productPrice = new ProductVariantPrice;

            $productPrice->product_variant_one = ProductVariant::where('variant', trim($titles[0]))->where('product_id', $product->id)->get()->first()->id;
            $productPrice->product_variant_two = ProductVariant::where('variant', trim($titles[1]))->where('product_id', $product->id)->get()->first()->id;

            //save with stock and others
            $productPrice->price = $price['price'];
            $productPrice->stock = $price['stock'];

            $productPrice->product_id = $product->id;

            $productPrice->save();

        }

        return;
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();

        $product = json_encode($product->getEditDataAttribute());

        return view('products.edit', compact('variants', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $productImages = $request->only('product_image');

//        if (!empty($productImages)) {
//            $productImages = ProductImage::create($productImages);
//            $product->productImages()->save($productImages);
//        }

        $variants = $request->product_variant;

        $product_variant_prices = $request->product_variant_prices;

        foreach ($variants as $variant) {
            foreach ($variant['tags'] as $tag) {
                $product->variants()->attach($variant['option'], ['variant' => $tag]);
            }
        }

        $product->product_variant_price()->delete();
        $product->variants()->detach();

        foreach ($variants as $variant) {
            foreach ($variant['tags'] as $tag) {
                $product->variants()->attach($variant['option'], ['variant' => $tag]);
            }
        }

        foreach ($product_variant_prices as $price) {

            $titles = explode('/', $price['title']);
            $productPrice = new ProductVariantPrice;

            $productPrice->product_variant_one = ProductVariant::where('variant', trim($titles[0]))->where('product_id', $product->id)->get()->first()->id;
            $productPrice->product_variant_two = ProductVariant::where('variant', trim($titles[1]))->where('product_id', $product->id)->get()->first()->id;

            //save with stock and others
            $productPrice->price = $price['price'];
            $productPrice->stock = $price['stock'];

            $productPrice->product_id = $product->id;

            $productPrice->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
