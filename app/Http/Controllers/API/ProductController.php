<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;


class ProductController extends Controller
{

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "page" => "required|numeric",
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $product = Product::paginate(1);

        $product = new ProductCollection($product);

        return $product;

    }

    public function show( Product $product)
    {
        if(!$product){
            return "NOT FOUND";
        }

        $product = new ProductResource($product);

        return $product;
    }

    public function store(Request $request)
    {
        $create = $request->all() ;

        $validator = Validator::make($create, [
            "name"=> 'required|max:255,unique:product',
            "photo"=> 'required|image|mimes:png,jpg|max:5000',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        if (isset($create["photo"])){
            $request->file('photo')->store('products');
            $create["photo"] = $request->file('photo')->hashName();
        }

        $product = product::create($create);

        return [
            "state" => 'product created successfully.',
            "data"=> $product
        ];

    }


    public function update(Request $request ,  product $product)
    {
        $edit = $request->all();

        $validator = Validator::make($edit, [
            "name"=> 'max:255unique:product,'. $product->id,
            "photo"=> 'image|mimes:png,jpg|max:5000',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        if (isset($edit["photo"])){
            Storage::disk('products')->delete($product->photo);
            $request->file('photo')->store('products');
            $edit["photo"] = $request->file('photo')->hashName();
        }

        $product->update($edit);

        return [
            "state" => ' product Updated successfully.',
            "data"=> $product
        ];

    }

    public function destroy(product $product)
    {
        $product->delete();

        return 'product Deleted successfully.';
    }
}
