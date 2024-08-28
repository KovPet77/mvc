<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\MultiImg;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;



class ShopController extends Controller
{
    

public function ShopPage(Request $request) {
    $perPage = 10; // Number of items per page
    $products = Product::query();
    $data = $request->all();
    
    #dd($data);    

    // Add filter based on kategoria_id
    if (!empty($data['kategoria_id'])) {
        $products->where('category_id', $data['kategoria_id']);
    }

    // Price Range
    if (!empty($data['price_range'])) {
        $price = explode('-', $data['price_range']);
        $products->where(function($query) use ($price) {
            $query->whereBetween('selling_price', $price)
                ->orWhereBetween('discount_price', $price);
        });
    }

    // Add filters based on query parameters
    if (!empty($request->input('category'))) {
        $slugs = explode(',', $request->input('category'));
        $catIds = Category::select('id')->whereIn('category_slug', $slugs)->pluck('id')->toArray();
        $products->whereIn('category_id', $catIds);
    }

    if (!empty($request->input('brand'))) {
        $slugs = explode(',', $request->input('brand'));
        $brandIds = Brand::select('id')->whereIn('brand_slug', $slugs)->pluck('id')->toArray();
        $products->whereIn('brand_id', $brandIds);
    }

    $products = $products->where('status', 1)->orderBy('id', 'DESC')->paginate($perPage);

    $categories = Category::orderBy('category_name', 'ASC')->paginate(10);
    $brands = Brand::orderBy('brand_name', 'ASC')->paginate(10);
    $newProduct = Product::orderBy('id', 'DESC')->limit(3)->get();


    // Komment: A category_view oldalon a user tájékoztatására szolgál. A kis navigációs nyíl. kategóra neve-> alkategória...
    $breadcat = Category::where('id', $data['kategoria_id'])->first();
    $subcat = SubCategory::where('category_id', $data['kategoria_id'])->pluck('subcategory_name');

   

    return view('frontend/product.shop_page', compact('products', 'categories', 'newProduct', 'brands', 'subcat', 'breadcat'));
}







    public function ShopFilter(Request $request){
        
        $data = $request->all();
        
        // Kategória filterezés
        $catUrl = "";

        if (!empty($data['category'])) {
            
            foreach($data['category'] as $category){
                if (empty($catUrl)) {
                    $catUrl .= '&category='.$category;
                }else{
                    $catUrl .= ','.$category;
                }
            }
        }


        // Márka filterezés
        $brandUrl = "";

        if (!empty($data['brand'])) {
            
            foreach($data['brand'] as $brand){
                if (empty($brandUrl)) {
                    $brandUrl .= '&brand='.$brand;
                }else{
                    $brandUrl .= ','.$brand;
                }
            }
        }

        // Range filterezés
        $priceRangeUrl = "";
        if (!empty($data['price_range'])) {
             $priceRangeUrl .=  '&price='.$data['price_range'];
         } 

        return redirect()->route('shop.page', $catUrl.$brandUrl.$priceRangeUrl);
    }





    public function ShopFilterSelect(Request $request){

        $perPage = 10; // Number of items per page
        $products = Product::query();
        $data = $request->all();

      
        // Add filter based on kategoria_id
        if (!empty($data['kategoria_id'])) {
            $products->where('category_id', $data['kategoria_id']);
        }

        // Price Range
        if (!empty($data['price_range'])) {
            $price = explode('-', $data['price_range']);
            $products->where(function($query) use ($price) {
                $query->whereBetween('selling_price', $price)
                    ->orWhereBetween('discount_price', $price);
            });
        }

        // Add filters based on query parameters
        if (!empty($request->input('category'))) {
            $slugs = explode(',', $request->input('category'));
            $catIds = Category::select('id')->whereIn('category_slug', $slugs)->pluck('id')->toArray();
            $products->whereIn('category_id', $catIds);
        }

        if (!empty($request->input('brand'))) {
            $slugs = explode(',', $request->input('brand'));
            $brandIds = Brand::select('id')->whereIn('brand_slug', $slugs)->pluck('id')->toArray();
            $products->whereIn('brand_id', $brandIds);
        }



            // Ár szerinti rendezés
        if (!empty($data['rendezes'])) {
            if ($data['rendezes'] === 'arszerint-novekvo') {
                $products->orderByRaw('CASE WHEN discount_price > 0 THEN discount_price ELSE selling_price END ASC');
            } elseif ($data['rendezes'] === 'arszerint-csokkeno') {
                $products->orderByRaw('CASE WHEN discount_price > 0 THEN discount_price ELSE selling_price END DESC');
            } elseif ($data['rendezes'] === 'diszkont') {
                $products->whereNotNull('discount_price')->orderBy('discount_price', 'desc');
            }
        }


            $products = $products->where('status', 1)->orderBy('id', 'DESC')->paginate($perPage);

            $categories = Category::orderBy('category_name', 'ASC')->paginate(10);
            $brands = Brand::orderBy('brand_name', 'ASC')->paginate(10);
            $newProduct = Product::orderBy('id', 'DESC')->limit(3)->get();

            // Komment: A category_view oldalon a user tájékoztatására szolgál. A kis navigációs nyíl. kategóra neve-> alkategória...
            $breadcat = Category::where('id', $data['kategoria_id'])->first();
            $subcat = SubCategory::where('category_id', $data['kategoria_id'])->pluck('subcategory_name');

        
        return view('frontend/product.shop_page', compact('products', 'categories', 'newProduct', 'brands', 'subcat', 'breadcat'));

    }
    
}
