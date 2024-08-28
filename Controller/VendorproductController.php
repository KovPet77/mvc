<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\MultiImg;
use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use App\Models\CustomeCheckBoxes;
use Image;
use Carbon\Carbon;




class VendorProductController extends Controller
{
    


    public function VendorAllProduct()
    {

        $id = Auth::user()->id;
        $products = Product::where('vendor_id', $id)->latest()->get();
        return view('vendor.backend.product.vendor_product_all', compact('products'));
    }




    public function VendorAddProduct()
    {
        $vendorID = Auth::user()->id;       

        // Lekérjük az összes terméket, ami az adott vendorID-hez tartozik
        
        $customeCheckboxes = CustomeCheckBoxes::where('vendor_id', $vendorID)
                                    ->select('custome_checkbox_1', 'custome_checkbox_2', 'custome_checkbox_3')
                                    ->get();
       
      

        $brands = Brand::latest()->get();   
        $categories = Category::latest()->get();   

        return view('vendor.backend.product.vendor_product_add', compact('brands', 'categories', 'customeCheckboxes'));
    }



    public function handleCustomeCheckbox(Request $request)
    {
        $authID = $request->authID;
        $subcategory_slug = $request->subcategory_slug ? $request->subcategory_slug : null;

        $is_Sub_Category_Exist_In_Shop = Product::where('vendor_id', $authID)
            ->where('subcategory_slug', $subcategory_slug)
            ->exists();

        if(!$is_Sub_Category_Exist_In_Shop){
            return response()->json([
                'message' => 'Ebben az alkategóriában nincs még terméked...',
                'is_Sub_Category_Exist_In_Shop' => false
            ]);
        }

        // Lekérdezzük a CustomeCheckBoxes rekordot
        $customCheckBoxes = CustomeCheckBoxes::where('vendor_id', (int)$authID)
            ->where('subcategory_slug', $subcategory_slug)
            ->first();

        // Lekérdezzük a products rekordot
        $product = Product::where('vendor_id', (int)$authID)
            ->where('subcategory_slug', $subcategory_slug)
            ->first();

        // Ha nincs találat
        if (!$customCheckBoxes || !$product) {
            return response()->json([
                'custome_checkbox' => [],
                'is_Sub_Category_Exist_In_Shop' => true
            ]);
        }

        // Az oszlopok ellenőrzése és összehasonlítása
        $checkboxFields = ['custome_checkbox_1', 'custome_checkbox_2', 'custome_checkbox_3', 'custome_checkbox_4', 'custome_checkbox_5', 'custome_checkbox_6'];

        $checkedStates = [];

        foreach ($checkboxFields as $field) {
            if (!empty($customCheckBoxes->$field) && in_array($customCheckBoxes->$field, $product->toArray())) {
                $checkedStates[$field] = true; // Az adott mező értéke megtalálható a product táblában
            } else {
                $checkedStates[$field] = false;
            }
        }
        #dd($checkedStates);
        return response()->json([
            'custome_checkbox' => $customCheckBoxes->only($checkboxFields),
            'checked_states' => $checkedStates, // Ezt fogjuk a frontendhez adni
            'is_Sub_Category_Exist_In_Shop' => true
        ]);
    }



    public function saveCustomeCheckbox(Request $request)
    {
        $authID          = $request->input('authID');
        $key             = $request->input('key');
        $value           = $request->input('value');
        $subcategorySlug = $request->input('subcategory_slug');
        $product_name    = $request->input('product_name');
        #$rejtett         = $request->input('rejtett');

        #dd($rejtett);
        #dd($product_name);

        // Ellenőrizzük, hogy van-e már ilyen subcategory_slug és vendor_id kombináció az adatbázisban
        $record = CustomeCheckBoxes::where('vendor_id', (int)$authID)
            ->where('subcategory_slug', $subcategorySlug)
            ->first();

        if ($record) {
            // Frissítsük az adott mezőt az új értékkel
            $record->$key = $value;
            $record->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Field updated successfully'
            ]);
        } else {
            // Ha a rekord nem létezik, hozzunk létre egy új rekordot
            $newRecord = new CustomeCheckBoxes();
            $newRecord->vendor_id = (int)$authID;
            $newRecord->subcategory_slug = $subcategorySlug;
            $newRecord->$key = $value;
            $newRecord->save();

            return response()->json([
                'status' => 'success',
                'message' => 'New record created and field saved successfully'
            ]);
        }
    }



public function saveSearchPropertyToProduct(Request $request)
{
    // Az oszlop neve, amit frissíteni szeretnél
    $rejtett = $request->input('rejtett');

    // Felhasználói azonosító és termék neve
    $authID = $request->input('authID');
    $product_name = $request->input('product_name');

    // Az érték, amit be szeretnél írni az oszlopba
    $value = $request->input('value');

    // Egyszerűbb megoldás az update() metódussal
    $affectedRows = Product::where('vendor_id', (int)$authID)
        ->where('product_name', $product_name)
        ->update([$rejtett => $value]);

    if ($affectedRows > 0) {
        return response()->json(['success' => 'Az érték sikeresen frissítve!']);
    } else {
        return response()->json(['error' => 'A frissítés nem sikerült, ellenőrizd az adatokat.'], 400);
    }
}







    public function deleteCustomeCheckbox(Request $request)
    {
        $authID = $request->input('authID');
        $key = $request->input('key');
        $subcategorySlug = $request->input('subcategory_slug');

        $record = CustomeCheckBoxes::where('vendor_id', (int)$authID)
            ->where('subcategory_slug', $subcategorySlug)
            ->first();

        if ($record) {
            if (!empty($record->$key)) {
                $record->$key = null;
                $record->save();

                // Checkboxok újraindexelése
                $this->reindexCheckboxes($record);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Field deleted successfully'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Field is already empty or does not exist'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Record not found'
        ]);
    }

    private function reindexCheckboxes($record)
    {
        $fields = [
            'custome_checkbox_1',
            'custome_checkbox_2',
            'custome_checkbox_3',
            'custome_checkbox_4',
            'custome_checkbox_5',
            'custome_checkbox_6'
        ];

        $newData = [];
        $index = 1;

        foreach ($fields as $field) {
            if (!empty($record->$field)) {
                $newData["custome_checkbox_$index"] = $record->$field;
                $index++;
            }
        }

        foreach ($fields as $field) {
            $record->$field = $newData[$field] ?? null;
        }

        $record->save();
    }






    public function VendorGetSubCategory($category_id){



        $subcat = SubCategory::where('category_id', $category_id)

            ->orderBy('subcategory_name', 'ASC')

            ->get(['id', 'subcategory_name', 'subcategory_slug']);  // Itt adjuk hozzá a subcategory_slug mezőt is

        return json_encode($subcat);
    }






    // Segédfüggvény az ékezetek eltávolításához és SEO-barát slug létrehozásához
    private function removeAccents($string) {
        $accents = array(
            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ö'=>'O', 'Ő'=>'O', 'Ú'=>'U', 'Ü'=>'U', 'Ű'=>'U',
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ö'=>'o', 'ő'=>'o', 'ú'=>'u', 'ü'=>'u', 'ű'=>'u'
        );
        $string = strtr($string, $accents);
        $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string); // Minden nem alfanumerikus és szóköz eltávolítása
        $string = strtolower(trim($string)); // Kisbetűkre váltás és a felesleges szóközök eltávolítása
        $string = preg_replace('/\s+/', '-', $string); // Szóközök helyettesítése kötőjellel
        return $string;
    }


        // Segédfüggvény az ékezetek eltávolításához és a kötőjelek megtartásához a subcategory_slug esetében
    private function removeAccentsKeepHyphens($string) {
        $accents = array(
            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ö'=>'O', 'Ő'=>'O', 'Ú'=>'U', 'Ü'=>'U', 'Ű'=>'U',
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ö'=>'o', 'ő'=>'o', 'ú'=>'u', 'ü'=>'u', 'ű'=>'u'
        );
        $string = strtr($string, $accents);
        $string = preg_replace('/[^a-zA-Z0-9\-]/', '', $string); // Minden nem alfanumerikus, kötőjel eltávolítása
        $string = strtolower(trim($string)); // Kisbetűkre váltás és a felesleges szóközök eltávolítása
        return $string;
    }



public function VendorStoreProduct(Request $request)
{
    // Adatvalidálás
    $validated = $request->validate([
        'category_id'         => 'required|integer',
        'subcategory_id'      => 'required|integer',
        'product_name'        => 'required|string|max:255',
        'product_code'        => 'required|string|max:50',
        'product_qty'         => 'required|integer',
        'product_tags'        => 'nullable|string',
        'product_size'        => 'nullable|string',
        'product_color'       => 'nullable|string',
        'selling_price'       => 'required|numeric',
        'discount_price'      => 'nullable|numeric',
        'short_desc'          => 'nullable|string',
        'long_desc'           => 'nullable|string',
        'custome_checkbox_1'  => 'nullable|boolean',
        'custome_checkbox_2'  => 'nullable|boolean',
        'custome_checkbox_3'  => 'nullable|boolean',
        'shipping_info'       => 'nullable|string',
        'shipping_company'    => 'nullable|string',
        'hot_deals'           => 'nullable|boolean',
        'featured'            => 'nullable|boolean',
        'special_offers'      => 'nullable|boolean',
        'special_deals'       => 'nullable|boolean',
        'meta_description'    => 'nullable|string',
        'meta_keywords'       => 'nullable|string',
        'og_title'            => 'nullable|string',
        'og_description'      => 'nullable|string',
        'product_thumbnail'   => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'multi_img.*'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Több kép validálása
    ]);

    try {
        // Fájl mentése
        $image = $request->file('product_thumbnail');
        $name_generate = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(800, 800)->save('upload/products/thumbnail/' . $name_generate);
        $save_url = 'upload/products/thumbnail/' . $name_generate;

        // Eredeti product_name érték
        $originalProductName = $validated['product_name'];
        // Ékezetek eltávolítása és kisbetűk átalakítása
        $productSlug = $this->removeAccents($originalProductName);
        // Ékezetek eltávolítása és kisbetűk átalakítása a subcategory_slug esetében
        $InsertSubcategory_slug = $this->removeAccentsKeepHyphens($request->subcategory_slug);

        // Termék mentése
        $product_id = Product::insertGetId([
            'brand_id'         => 1,
            'category_id'      => $validated['category_id'],
            'subcategory_id'   => $validated['subcategory_id'],
            'product_name'     => $originalProductName,
            'product_slug'     => $productSlug,
            'subcategory_slug' => $InsertSubcategory_slug,

            'product_code'     => $validated['product_code'],
            'product_qty'      => $validated['product_qty'],
            'product_tags'     => $validated['product_tags'],
            'product_size'     => $validated['product_size'],
            'product_color'    => $validated['product_color'],

            'selling_price'    => $validated['selling_price'],
            'discount_price'   => $validated['discount_price'],
            'short_desc'       => $validated['short_desc'],
            'long_desc'        => $validated['long_desc'],

            'custome_checkbox_1' => $validated['custome_checkbox_1'],
            'custome_checkbox_2' => $validated['custome_checkbox_2'],
            'custome_checkbox_3' => $validated['custome_checkbox_3'],

            'shipping_info'    => $validated['shipping_info'],
            'shipping_company' => $validated['shipping_company'],
            'hot_deals'        => $validated['hot_deals'],
            'featured'         => $validated['featured'],
            'special_offers'   => $validated['special_offers'],
            'special_deals'    => $validated['special_deals'],

            'product_thumbnail' => $save_url,
            'meta_description'  => $validated['meta_description'],
            'meta_keywords'     => $validated['meta_keywords'],
            'og_title'          => $validated['og_title'],
            'og_description'    => $validated['og_description'],
            'og_image'          => $save_url,

            'vendor_id'    => Auth::user()->id,
            'status'       => 1,
            'created_at'   => Carbon::now(),
        ]);

        // Egyedi checkboxok mentése
        CustomeCheckBoxes::insert([
            'subcategory_slug'    => $InsertSubcategory_slug,
            'vendor_id'           => Auth::user()->id,
            'custome_checkbox_1'  => $validated['custome_checkbox_1'],
            'custome_checkbox_2'  => $validated['custome_checkbox_2'],
            'custome_checkbox_3'  => $validated['custome_checkbox_3'],
        ]);

        // Több kép mentése
        $images = $request->file('multi_img');
        if ($images) {
            foreach ($images as $img) {
                $make_name = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension();
                Image::make($img)->resize(800, 800)->save('upload/products/multi-image/' . $make_name);
                $upload_path = 'upload/products/multi-image/' . $make_name;

                MultiImg::insert([
                    'product_id' => $product_id,
                    'photo_name' => $upload_path,
                    'created_at' => Carbon::now(),
                ]);
            }
        }

        // Sikeres értesítés
        $notification = array(
            'message'    => 'Eladói termék sikeresen létrehozva',
            'alert-type' => 'success'
        );

        return redirect()->route('vendor.all.product')->with($notification);

    } catch (\Exception $e) {
        // Hiba esetén értesítés
        return redirect()->back()->with('error', 'Hiba történt a termék létrehozása közben.')->withInput();
    }
}





    public function VendorEditProduct($id)
    {

        $chekboxes   = [];    
        $multiImgs   = MultiImg::where('product_id', $id)->get();       
        $brands      = Brand::latest()->get();   
        $categories  = Category::latest()->get();   
        $subcategory = Subcategory::latest()->get();   
        $products    = Product::findOrFail($id);
        $chekboxes[] = $products->custome_checkbox_1;
        $chekboxes[] = $products->custome_checkbox_2;
        $chekboxes[] = $products->custome_checkbox_3;
        $chekboxes[] = $products->custome_checkbox_4;
        $chekboxes[] = $products->custome_checkbox_5;
        $chekboxes[] = $products->custome_checkbox_6;
       
        return view('vendor.backend.product.vendor_product_edit', compact('brands', 'categories', 'products', 'subcategory', 'multiImgs', 'chekboxes'));
    }




public function VendorUpdateProduct(Request $request)
{
   
    // Adatvalidálás
    $validated = $request->validate([
        'category_id'    => 'required|integer',
        'subcategory_id' => 'required|integer',
        'product_name'   => 'required|string|max:255',
        'product_code'   => 'required|string|max:50',
        'product_qty'    => 'required|integer',
        'product_tags'   => 'nullable|string',
        'product_size'   => 'nullable|string',
        'product_color'  => 'nullable|string',
        'selling_price'  => 'required|numeric',
        'discount_price' => 'nullable|numeric',
        'short_desc'     => 'nullable|string',
        'long_desc'      => 'nullable|string',
        'meta_description' => 'nullable|string',
        'meta_keywords'  => 'nullable|string',    
        'add_search_checkbox_to_product' => 'nullable|array',
    ]);


    try {
    $product_id = $request->id;
        // Termék frissítése
        $product = Product::findOrFail($product_id);
        
        $product->update(array_merge([
            'brand_id'         => 1,
            'category_id'      => $validated['category_id'],
            'subcategory_id'   => $validated['subcategory_id'],
            'product_name'     => $validated['product_name'],
            'product_slug'     => strtolower(str_replace(' ', '-', $validated['product_name'])),
            'product_code'     => $validated['product_code'],
            'product_qty'      => $validated['product_qty'],
            'product_tags'     => $validated['product_tags'],
            'product_size'     => $validated['product_size'],
            'product_color'    => $validated['product_color'],
            'selling_price'    => $validated['selling_price'],
            'discount_price'   => $validated['discount_price'],
            'short_desc'       => $validated['short_desc'],
            'long_desc'        => $validated['long_desc'],
            'meta_description' => $validated['meta_description'],
            'meta_keywords'    => $validated['meta_keywords'],        
            'status'           => 1,
            'created_at'       => Carbon::now(),
        ]));

    $notification = array(
        'message'    => 'Eladói Termék sikeresen frissítve',
        'alert-type' => 'success'
    );

      return redirect()->route('vendor.all.product')->with($notification);

      }catch (Exception $e) {
        // Hibakezelés
        echo 'Message: ' .$e->getMessage();
    }

}



public function VendorUpdateProductThumbnail(Request $request)
{
    // Adatvalidálás
    $validated = $request->validate([
        'id'               => 'required|integer|exists:products,id',
        'old_img'          => 'required|string',
        'product_thumbnail'=> 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    try {
        $pro_id   = $validated['id'];
        $oldImage = $validated['old_img'];

        $image = $request->file('product_thumbnail');
        $name_generate = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();        
        Image::make($image)->resize(800,800)->save('upload/products/thumbnail/'.$name_generate);
        $save_url = 'upload/products/thumbnail/'.$name_generate;

        // Előző fotó eltávolítása:
        if (file_exists($oldImage)) {
            unlink($oldImage);
        }

        // Termék frissítése
        Product::findOrFail($pro_id)->update([
            'product_thumbnail' => $save_url,
            'updated_at'        => Carbon::now()
        ]);

        // Sikeres frissítés értesítése
        $notification = [
            'message'    => 'Thumbnail fotó sikeresen frissítve',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    } catch (\Exception $e) {
        // Hibakezelés
        return redirect()->back()->with('error', 'Hiba történt a thumbnail frissítése közben: ' . $e->getMessage());
    }
}



    
public function VendorUpdateProductMultiimage(Request $request)
{
    // Adatvalidálás
    $validated = $request->validate([
        'multi_img.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        'ids' => 'required|array',
        'ids.*' => 'required|integer|exists:multi_imgs,id',
    ]);

    $imgIds = $validated['ids'];
    $imgs = $request->file('multi_img');

    try {
        // Ellenőrizzük, hogy a feltöltött képek és az ID-k száma megegyezik
        if (count($imgs) !== count($imgIds)) {
            throw new \Exception('A képek és az ID-k száma nem egyezik.');
        }

        foreach ($imgs as $index => $img) {
            $id = $imgIds[$index];
            $imgDel = MultiImg::findOrFail($id);

            // Előző fotó eltávolítása
            if (file_exists($imgDel->photo_name)) {
                unlink($imgDel->photo_name);
            }

            // Új fotó feltöltése
            $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();        
            Image::make($img)->resize(800,800)->save('upload/products/multi-image/'.$make_name);

            $upload_path = 'upload/products/multi-image/'.$make_name;

            // MultiImg frissítése
            MultiImg::where('id', $id)->update([
                'photo_name' => $upload_path,
                'updated_at' => Carbon::now(),
            ]);
        }

        $notification = [
            'message'    => 'Termék fotó sikeresen frissítve',
            'alert-type' => 'success'
        ];
        
        return redirect()->back()->with($notification);
    } catch (\Exception $e) {
        // Hibakezelés
        return redirect()->back()->with('error', 'Hiba történt a termék fotók frissítése közben: ' . $e->getMessage());
    }
}




        public function VendorMultiImageDelete($id)
        {
            $old_img = MultiImg::findOrFail($id);
            unlink($old_img->photo_name);
            MultiImg::findOrFail($id)->delete();

            $notification = array(
                'message'    => 'Termék fotó sikeresen törölve',
                'alert-type' => 'success'
            );
            
            return redirect()->back()->with($notification);

        }


        public function VendorProductInactive($id)
        {
            Product::findOrFail($id)->update(['status' => 0]);
            $notification = array(

                'message'    => 'Termék sikeresen inaktiválva',
                'alert-type' => 'success'
            );
            
            return redirect()->back()->with($notification);
        }



        public function VendorProductActive($id)
        {
            Product::findOrFail($id)->update(['status' => 1]);
            $notification = array(
                
                'message'    => 'Termék sikeresen aktiválva',
                'alert-type' => 'success'
            );
            
            return redirect()->back()->with($notification);
        }




        public function VendorDeleteProduct($id)
        {
            $product = Product::findOrFail($id);
            
            unlink($product->product_thumbnail);
            
            Product::findOrFail($id)->delete();
            $images = MultiImg::where('product_id', $id)->get();

            foreach($images as $image){
                unlink($image->photo_name);
                MultiImg::where('product_id', $id)->delete();
            }

            $notification = array(
                
                'message'    => 'Termék sikeresen törölve',
                'alert-type' => 'success'
            );
            
            return redirect()->back()->with($notification);
        }


}
