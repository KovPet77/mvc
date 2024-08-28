<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Backend\Brandcontroller;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SubCategoryController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\VendorProductController;
use App\Http\Controllers\Backend\SliderController;
use App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\ShippingAreaController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\VendorOrderController;
use App\Http\Controllers\Backend\ReturnController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\ActiveUserController;
use App\Http\Controllers\Backend\SiteSettingController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\AdvertiserController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\SikeresFizetes;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\User\WishListController;
use App\Http\Controllers\User\CompareController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\StripeController;
use App\Http\Controllers\User\AllUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfAuthenticated;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [IndexController::class,'Index'])->name('/'); 


Route::get('/instantSearch', [ProductController::class, 'instantSearch'])->name('instantSearch');
Route::get('/instantSearchTwo',[ProductController::class, 'instantSearchTwo'])->name('instantSearchTwo');



Route::get('/instantSearchAll',[ProductController::class, 'instantSearchAll'])->name('instantSearchAll');
Route::get('/instantSearchTwoAll',[ProductController::class, 'instantSearchTwoAll'])->name('instantSearchTwoAll');




Route::get('/csomagok', [IndexController::class,'Csomagok'])->name('csomagok');
Route:: middleware(['auth'])->group(function(){
Route::get('/dashboard', [UserController::class,'UserDashboard'])->name('dashboard'); 
Route::post('/user/profile/store', [UserController::class,'UserProfileStore'])->name('user.profile.store'); 
Route::get('/user/logout', [UserController::class,'UserLogout'])->name('user.logout'); 
Route::post('/user/update/password', [UserController::class,'UserUpdatePassword'])->name('user.update.password'); 

});
    

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';


// Admin
// Védett útvonal auth middleware-el:
Route::middleware(['auth', 'role:admin'])->group(function() { 


// Jogosultságok kezelése:

Route::controller(RoleController::class)->group(function(){
    Route::get('/all/permission', 'AllPermission')->name('all.permission');
    Route::get('/add/permission', 'AddPermission')->name('add.permission');
    Route::post('/store/permission', 'StorePermission')->name('store.permission');
    Route::get('/edit/roles{id}', 'EditRoles')->name('edit.roles');
    Route::post('/update/roles', 'UpdateRoles')->name('update.roles');
    Route::get('/delete/roles{id}', 'DeleteRoles')->name('delete.roles');
    Route::get('/all/roles', 'AllRoles')->name('all.roles');
    Route::get('/add/roles', 'AddRoles')->name('add.roles');
    Route::post('/store/roles', 'StoreRoles')->name('store.roles');


    Route::get('/edit/permission{id}', 'EditPermission')->name('edit.permission');
    Route::post('/update/permission', 'UpdatePermission')->name('update.permission');
    Route::get('/delete/permission{id}', 'DeletePermission')->name('delete.permission');


    Route::get('/add/roles/permission', 'AddRolesPermission')->name('add.roles.permission');
    Route::post('/role/permission/store', 'RolePermissionStore')->name('role.permission.store');
    Route::get('/all/roles/permission', 'AllRolesPermission')->name('all.roles.permission');
    Route::get('/admin/edit/roles/{id}', 'AdminRolesEdit')->name('admin.edit.roles');
    Route::post('/admin/roles/update/{id}', 'AdminRolesUpdate')->name('admin.roles.update');
    Route::get('/admin/delete/roles/{id}', 'AdminRolesDelete')->name('admin.delete.roles');

});    





Route::controller(AdminController::class)->group(function(){

    Route::get('/all/admin', 'AllAdmin')->name('all.admin');
    Route::get('/add/admin', 'AddAdmin')->name('add.admin');
    Route::post('/admin/admin/store', 'AdminUserStore')->name('admin.user.store');
    Route::get('/edit/admin/role/{id}', 'EditAdminRole')->name('edit.admin.role');
    Route::post('/admin/user/update/{id}', 'AdminUserUpdate')->name('admin.user.update');
    Route::get('/delete/admin/role/{id}', 'DeleteAdminRole')->name('delete.admin.role');

});  


Route::get('/admin/dashboard', [AdminController::class,'AdminDashboard'])->name('admin.dashboard');
Route::get('/admin/logout', [AdminController::class,'AdminDestroy'])->name('admin.logout');
Route::get('/admin/profile', [AdminController::class,'AdminProfile'])->name('admin.profile');
Route::post('/admin/profile/store', [AdminController::class,'AdminProfileStore'])->name('admin.profile.store'); // 
Route::get('/admin/change/password', [AdminController::class,'AdminChangePassword'])->name('admin.change.password'); // 
Route::post('/admin/update/password', [AdminController::class,'AdminUpdatePassword'])->name('admin.update.password'); // 



// Admin rendelések jóváhagyása, véglegesítése
Route::controller(OrderController::class)->group(function(){

    Route::get('/pending/order', 'PendingOrder')->name('pending.order');
    Route::get('/admin/order/details/{order_id}', 'AdminOrderDetails')->name('admin.order.details');
    Route::get('/admin/confirmed/order', 'AdminConfirmedOrder')->name('admin.confirmed.order');
    Route::get('/admin/processing/order', 'AdminProcessingOrder')->name('admin.processing.order');
    Route::get('/admin/delivered/order', 'AdminDeliveredgOrder')->name('admin.delivered.order');
    Route::get('/pending/confirm/{order_id}', 'PendingToConfirm')->name('pending-confirm');
    Route::get('/confirm/processing/{order_id}', 'ConfirmToProcessing')->name('confirm-processing');
    Route::get('/processing/delivered/{order_id}', 'ProcessingToDelivered')->name('processing-delivered');
    Route::get('/admin/invoice/download/{order_id}', 'AdminInvoiceDownload')->name('admin.invoice.download');
});    

// Admin visszaküldött rendelések
Route::controller(ReturnController::class)->group(function(){

    Route::get('/return/request', 'ReturnRequest')->name('return.request');
    Route::get('/return/request/approved/{order_id}', 'ReturnRequestApproved')->name('return.request.approved');
    Route::get('/complate/return/request', 'ComplateReturnRequest')->name('complate.return.request');
});



// Admin jelentések
Route::controller(ReportController::class)->group(function(){

    Route::get('/report/view', 'ReportView')->name('report.view');
    Route::post('/search/by/date', 'SearchByDate')->name('search-by-date');
    Route::post('/search/by/month', 'SearchByMonth')->name('search-by-month');
    Route::post('/searc/-by/year', 'SearchByYear')->name('search-by-year');
    Route::get('/order/by/user', 'OrderByUser')->name('order.by.user');
    Route::post('/search/by/user', 'SearchByUser')->name('search-by-user');

});



// Admin. Aktív és inaktív felhasználók keresése
Route::controller(ActiveUserController::class)->group(function(){

    Route::get('/all-user', 'AllUser')->name('all-user');
    Route::get('/all-vendor', 'AllVendor')->name('all-vendor');
  

});


// Admin beállítások
Route::controller(SiteSettingController::class)->group(function(){

    Route::get('/site/settings', 'SiteSetting')->name('site.settings');
    Route::post('/site/setting/update', 'SiteSettingUpdate')->name('site.setting.update');
    Route::get('/seo/settings', 'SeoSetting')->name('seo.settings');
    Route::post('/seo/setting/update', 'SeoSettingUpdate')->name('seo.setting.update');
  
  

});



});

// Eladók
Route::middleware(['auth', 'role:vendor'])->group(function() { 
Route::get('/vendor/profile', [VendorController::class,'VendorProfile'])->name('vendor.profile'); 
Route::get('/vendor/change/password', [VendorController::class,'VendorChangePassword'])->name('vendor.change.password'); 
Route::get('/vendor/dashboard', [VendorController::class,'VendorDashboard'])->name('vendor.dashboard'); 
Route::get('/vendor/logout', [VendorController::class,'VendorDestroy'])->name('vendor.logout'); 
Route::post('/vendor/update/password', [VendorController::class,'VendorUpdatePassword'])->name('vendor.update.password'); 
Route::post('/vendor/profile/store', [VendorController::class,'VendorProfileStore'])->name('vendor.profile.store'); 





Route::controller(VendorProductController::class)->group(function(){
    
    Route::get('/vendor/all/product', 'VendorAllProduct')->name('vendor.all.product');
    Route::get('/vendor/add/product', 'VendorAddProduct')->name('vendor.add.product');
    Route::post('/vendor/store/product','VendorStoreProduct')->name('vendor.store.product'); 
    Route::get('/vendor/subcategory/ajax/{category_id}', 'VendorGetSubCategory');
    Route::get('/vendor/subcategory/ajax/', 'handleCustomeCheckbox');
    Route::post('/vendor/subcategory/save', 'saveCustomeCheckbox');
    Route::post('/vendor/subcategory/saveSearchPropertyToProduct', 'saveSearchPropertyToProduct');




    Route::post('/vendor/subcategory/delete', 'deleteCustomeCheckbox');
    Route::get('/vendor/edit/product/{id}', 'VendorEditProduct')->name('vendor.edit.product');
    Route::post('/vendor/update/product','VendorUpdateProduct')->name('vendor.update.product'); 
    Route::post('/vendor/update/product/thumbnail','VendorUpdateProductThumbnail')->name('vendor.update.product.thumbnail');
    Route::post('/vendor/update/product/multiimage','VendorUpdateProductMultiimage')->name('vendor.update.product.multiimage'); 
    Route::get('/vendor/product/multiimage/delete', 'VendorMultiImageDelete')->name('vendor.product.multiimage.delete');
    Route::get('/vendor/product/inactive{id}', 'VendorProductInactive')->name('vendor.product.inactive');
    Route::get('/vendor/product/active{id}', 'VendorProductActive')->name('vendor.product.active');
    Route::get('/vendor/delete/product{id}', 'VendorDeleteProduct')->name('vendor.delete.product');
    
});



// Eladók irányítópult útvonalak
Route::controller(VendorOrderController::class)->group(function(){
    
    Route::get('/vendor/order', 'VendorOrder')->name('vendor.order');
    Route::get('/vendor/return/order', 'VendorReturnOrder')->name('vendor.return.order');
    Route::get('/vendor/complate/return/order', 'VendorComplateReturnOrder')->name('vendor.complate.return.order');
    Route::get('/vendor/order/details/{order_id}', 'VendorOrderDetails')->name('vendor.order.details');

    
});



    });


    Route::get('/admin/login', [AdminController::class,'AdminLogin'])->name('admin.login')->middleware(RedirectIfAuthenticated::class); 
    Route::get('/vendor/login', [VendorController::class,'VendorLogin'])->name('vendor.login')->middleware(RedirectIfAuthenticated::class); 
    Route::get('/eladoi/regisztracio', [VendorController::class,'BecomeVendor'])->name('become.vendor'); 


    Route::post('/vendor/regisztracio/masodiklepes', [VendorController::class,'VendorRegisztracioMasodikLepes'])->name('vendor.regisztracio.masodiklepes');
    Route::post('/vendor/regisztracio/harmadiklepes', [VendorController::class,'VendorRegisztracioHarmadiklepes'])->name('vendor.regisztracio.harmadiklepes');




    // Szolgáltató, reklámozó
    Route::get('/advertiser/login', [AdvertiserController::class,'AdvertiserLogin'])->name('advertiser.login'); 
     Route::get('/reklamazoi/regisztracio', [AdvertiserController::class,'BecomeAdvertiser'])->name('become.advertiser'); 
    Route::post('/reklamazoi/regisztracio/masodiklepes', [AdvertiserController::class,'ReklamazoiRegisztracioMasodikLepes'])->name('reklamazoi.regisztracio.masodiklepes');
        Route::post('/reklamozoi/regisztracio/harmadiklepes', [AdvertiserController::class,'ReklamozoiRegisztracioHarmadiklepes'])->name('reklamozoi.regisztracio.harmadiklepes');



     Route::get('/regisztracio', [UserController::class,'Regisztracio'])->name('regisztracio'); 
     Route::get('/regisztracio/masodiklepes', [UserController::class,'RegisztracioMasodikLepes'])->name('regisztracio.masodiklepes'); 
     Route::post('/regisztracio/masodiklepes', [UserController::class,'RegisztracioMasodikLepes'])->name('regisztracio.masodiklepes'); 
     Route::post('/regisztracio/harmadiklepes', [UserController::class,'RegisztracioHarmadiklepes'])->name('regisztracio.harmadiklepes'); 




    // Védett útvonal auth middleware-el:
    Route::middleware(['auth', 'role:admin'])->group(function() { // szerkesztés, hozzáadás.
    // Laravel 9-től lehet így:
    Route::controller(Brandcontroller::class)->group(function(){

        Route::get('/all/brand', 'AllBrand')->name('all.brand');
        Route::get('/add/brand', 'AddBrand')->name('add.brand');
        Route::post('/brand/store', 'BrandStore')->name('brand.store');
        Route::get('/edit/brand/{id}', 'EditBrand')->name('edit.brand');
        Route::post('/update/brand', 'UpdateBrand')->name('update.brand');
        Route::get('/delete/brand{id}', 'DeleteBrand')->name('delete.brand');

    });



    // Kategóriák útvonalai
    Route::controller(CategoryController::class)->group(function(){

        Route::get('/all/category', 'AllCategory')->name('all.category');
        Route::get('/add/category', 'AddCategory')->name('add.category');
        Route::post('/category/store', 'CategoryStore')->name('category.store');
        Route::get('/edit/category/{id}', 'EditCategory')->name('edit.category');
        Route::post('/update/category', 'UpdateCategory')->name('update.category');
        Route::get('/delete/category/{id}', 'DeleteCategory')->name('delete.category');
    });

        // Alkategóriák útvonalai
        Route::controller(SubCategoryController::class)->group(function(){

        Route::get('/all/subcategory', 'AllSubCategory')->name('all.subcategory');
        Route::get('/add/subcategory', 'AddSubCategory')->name('add.subcategory');
        Route::post('/store/subcategory', 'StoreSubCategory')->name('store.subcategory');
        Route::get('/edit/subcategory/{id}', 'EditSubCategory')->name('edit.subcategory');
        Route::post('/update/subcategory', 'UpdateSubCategory')->name('update.subcategory');    
        Route::get('/delete/subcategory/{id}', 'DeleteSubCategory')->name('delete.subcategory');
        Route::get('/subcategory/ajax/{category_id}', 'GetSubCategory');
        });

        // Aktív és inaktív eladók útvonalai
        Route::controller(AdminController::class)->group(function(){

        Route::get('/inactive/vendor', 'InactiveVendor')->name('inactive.vendor');
        Route::get('/active/vendor', 'ActiveVendor')->name('active.vendor');
        Route::get('/inactive/vendor/details/{id}', 'InactiveVendorDetails')->name('inactive.vendor.details');
        Route::post('/active/vendor/approve', 'ActiveVendorApprove')->name('active.vendor.approve');
        Route::get('/active/vendor/details/{id}', 'ActiveVendorDetails')->name('active.vendor.details');
        Route::post('/inactive/vendor/approve', 'InActiveVendorApprove')->name('inactive.vendor.approve');     
     
        });


            
                // Termék útvonalai
            Route::controller(ProductController::class)->group(function(){

            Route::get('/all/product', 'AllProduct')->name('all.product');
            Route::get('/add/product', 'AddProduct')->name('add.product');
            Route::post('/store/product', 'StoreProduct')->name('store.product');
            Route::get('/edit/product/{id}', 'EditProduct')->name('edit.product');
            Route::post('/update/product', 'UpdateProduct')->name('update.product');
            Route::post('/update/product/thumbnail', 'UpdateProductThumbnail')->name('update.product.thumbnail');
            Route::post('/update/product/multiimage', 'UpdateProductMultiimage')->name('update.product.multiimage');
            Route::get('/edit/multiimage/delete/{id}', 'MultiImageDelete')->name('product.multiimage.delete');
            Route::get('/edit/product/inactive/{id}', 'ProductInactive')->name('product.inactive');
            Route::get('/edit/product/active/{id}', 'ProductActive')->name('product.active');
            Route::get('/delete/product/{id}', 'ProductDelete')->name('delete.product');
        



            // Készlet kezelése
             Route::get('/product/stock', 'ProductStock')->name('product.stock');
                });


            // Slider útvonalak
            Route::controller(SliderController::class)->group(function(){

                Route::get('/all/slider', 'AllSlider')->name('all.slider');
                Route::get('/add/slider', 'AddSlider')->name('add.slider');
                Route::post('/slider/store', 'SliderStore')->name('store.slider');
                Route::get('/edit/slider/{id}', 'EditSlider')->name('edit.slider');
                Route::post('/update/slider', 'UpdateSlider')->name('update.slider');              
                Route::get('/delete/slider/{id}', 'DeleteSlider')->name('delete.slider');
            });


            // Banner útvonalak
            Route::controller(BannerController::class)->group(function(){

                Route::get('/all/banner', 'AllBanner')->name('all.banner');
                Route::get('/add/banner', 'AddBanner')->name('add.banner');
                Route::post('/banner/store', 'StoreBanner')->name('store.banner');
                Route::get('/edit/banner/{id}', 'EditBanner')->name('edit.banner');
                Route::post('/update/banner', 'UpdateBanner')->name('update.banner');
                Route::get('/delete/banner/{id}', 'DeleteBanner')->name('delete.banner');
             
            });




            // Kupon útvonalak
            Route::controller(CouponController::class)->group(function(){

                Route::get('/all/coupon', 'AllCoupon')->name('all.coupon');
                Route::get('/add/coupon', 'AddCoupon')->name('add.coupon');
                Route::post('/store/coupon', 'StoreCoupon')->name('store.coupon');
                Route::get('/edit/coupon/{id}', 'EditCoupon')->name('edit.coupon');
                Route::post('/update/coupon/', 'UpdateCoupon')->name('update.coupon');
                Route::get('/delete/coupon/{id}', 'DeleteCoupon')->name('delete.coupon');

             
            });


            // Szállítás útvonalak
            Route::controller(ShippingAreaController::class)->group(function(){

                Route::get('/all/division', 'AllDivision')->name('all.division');
                Route::get('/add/division', 'AddDivision')->name('add.division');
                Route::post('/store/division', 'StoreDivision')->name('store.division');
                Route::get('/edit/division/{id}', 'EditDivision')->name('edit.division');
                Route::post('/update/division/', 'UpdateDivision')->name('update.division');
                Route::get('/delete/division/{id}', 'DeleteDivision')->name('delete.division');

             
            });


});


 
    Route::get('/plaza-termekadat-{id}-{slug}', [IndexController::class,'ProductDetails']); 


    Route::get('/pomaz-plaza-{slug}', [IndexController::class,'VendorDetails'])->name('vendor.details'); 
    Route::get('/osszes-uzlet', [IndexController::class,'VendorAll'])->name('vendor.all'); 

    Route::get('/plaza-kategoria-{id}-{slug}', [IndexController::class,'CatWiseProduct']); 


    Route::get('/plaza-alkategoria-{id}-{slug}', [IndexController::class,'SubCatWiseProduct']);


    // Pop up modal with product data's
    Route::get('/product/view/modal/{id}', [IndexController::class,'ProductViewAjax']);  
   
    Route::get('uzlet-alkategoria-{subcategorySlug}', [IndexController::class, 'getVendorProductsByCategory'])
    ->name('vendor.products.by.category');

    Route::get('/uzlet-{id}-{subcategorySlug}', [IndexController::class,'getVendorProductsByProductName']); 



    Route::controller(ShopController::class)->group(function(){
        Route::post('/shop', 'ShopPage')->name('shop.page');
        Route::get('/shop', 'ShopPage')->name('shop.page');
        Route::post('/shop/filter/select', 'ShopFilterSelect')->name('shop.filter.select');
    });








    // Kosár:
    Route::post('/cart/data/store/{id}', [CartController::class,'AddToCart']);   

    Route::get('/product/mini/cart', [CartController::class,'AddMiniCart']);   

    Route::get('/minicart/product/remove/{rowId}', [CartController::class,'RemoveMiniCart']);  
   // Kosárba dobás a termékoldalról
    Route::post('/dcart/data/store/{id}', [CartController::class,'AddToCartDetails']);  


    Route::post('/addtowishlist/{product_id}', [WishListController::class,'AddToWishList'])->name('addtowishlist'); 




 Route::post('/add-to-compare/{product_id}', [CompareController::class,'AddToCompare']); 


 // Kupon részleg
 Route::post('/coupon-apply', [CartController::class,'CouponApply']); 

 Route::get('/coupon-calculation', [CartController::class,'CouponCalculation']); 
 Route::get('/coupon-remove', [CartController::class,'CouponRemove']); 



 Route::get('/penztar', [CartController::class,'checkoutCreate'])->name('penztar'); 




// Kosár:
 Route::get('/kosar', [CartController::class,'MyCart'])->name('kosar'); 
 Route::get('/get-cart-product', [CartController::class,'GetCartProduct']); 
 Route::get('/cart-remove/{rowId}', [CartController::class,'CartRemove']); 
 Route::get('/cart-remove/{rowId}', [CartController::class,'CartRemove']); 
 Route::get('/cart-decrement/{rowId}', [CartController::class,'CartDecrement']); 
 Route::get('/cart-increment/{rowId}', [CartController::class,'CartIncrement']); 
 Route::get('/siker', [SikeresFizetes::class,'Siker']); 
 


Route::controller(CheckoutController::class)->group(function(){

   // Rendelési adatok begyűjtése:
   Route::post('/checkout/store', 'CheckoutStore')->name('checkout.store');

});



// Stripe:
Route::controller(StripeController::class)->group(function(){

   Route::post('/stripe/order', 'StripeOrder')->name('stripe-order');
   Route::post('/cash/order', 'CashOrder')->name('cash.order');

}); 



 Route::middleware(['auth', 'role:user'])->group(function() {

    Route::controller(WishListController::class)->group(function(){

    Route::get('/wishlist', 'AllWishList')->name('wishlist');
    Route::get('/get-wishlist-product', 'GetWishListProduct');
    Route::get('/remove-wishlist/{id}', 'RemoveWishlist');


    }); 



    // Termék összehasonlítás:
    Route::controller(CompareController::class)->group(function(){

    Route::get('/compare', 'AllCompare')->name('compare');
    Route::get('/get-compare-product', 'GetCompareProduct');
    Route::get('/compare-remove/{id}', 'CompareRemove');
    });




    // Regisztrált user útvonalai
    Route::controller(AllUserController::class)->group(function(){

    Route::get('/user/index', 'UserIndex')->name('user.index');
    Route::get('/user/account/page', 'UserAccount')->name('user.account.page');

    Route::get('/user/change/password', 'UserChangePassword')->name('user.change.password');
    Route::get('/user/order/page', 'UserOrderPage')->name('user.order.page');
    Route::get('/user/order_details/{order_id}', 'UserOrderDetails');
    Route::get('/user/invoice_download/{order_id}', 'UserOrderInvoice');
    Route::post('/return/order/{order_id}', 'ReturnOrder')->name('return.order');
    Route::get('/return/order/page', 'ReturnOrderPage')->name('return.order.page');

    // Rendelés nyomonkövetése:
    Route::get('/user/track/order', 'UserTrackOrder')->name('user.track.order');
    Route::post('/order/tracking', 'OrderTracking')->name('order.tracking');
    });

 });

    // Termék keresés
    Route::controller(IndexController::class)->group(function(){

    Route::post('/product/search', 'ProductSearch')->name('product.search');

    // Ajaxos keresés
    Route::post('/search-product', 'SearchProduct')->name('product.search');

    Route::post('/search-vendor', 'SearchVendor')->name('search.vendor');

    Route::get('/shop/filter/select/sub}', 'SearchProductSub')->name('shop.filter.select.sub');
    
    Route::get('/mobil/kereses}', 'MobilKereses')->name('mobil.kereses');
    Route::get('/mobil/rendezes}', 'MobilRendezes')->name('mobil.rendezes');   
    
    Route::get('/vendor/termek/kereses', 'VendorTermekKereses')->name('vendor.termek.kereses');
    Route::get('/vendor/termek/rendezes', 'VendorTermekRendezes')->name('elado.termek.rendezes');
    
   
    
    });

 Route::controller(AdvertiserController::class)->group(function(){
     
     Route::get('/osszes-hirdetes', 'OsszesHirdeto')->name('osszes.hirdeto');
     Route::get('/hirdeto/keresese}', 'HirdetoKeresese')->name('search.advertiser');
     Route::get('/hirdeto/adatai/{id}', 'HirdetoAdatai')->name('advertiser.details');
     Route::get('/hirdeto/hozzadas', 'HirdetesHozzadas')->name('hirdeto.hozzadas');     
     Route::post('/store/advertiser', 'HirdetesStore')->name('store.advertiser');
     Route::get('/osszes/hirdeto/backend}', 'OsszesHirdetoBackend')->name('osszes.hirdeto.backend');     
     Route::get('/hirdeto/szerkesztes/{id}', 'HirdetoSzerkesztes')->name('hirdeto.szerkesztes');   
     Route::post('/update/hirdeto', 'UpdateHirdeto')->name('update.hirdeto');
   
 });
 // Bemutató oldal
 Route::controller(IndexController::class)->group(function(){
      Route::get('/promo', 'Promo')->name('promo');
 
 });
 
 
 
  