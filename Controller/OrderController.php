<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;   
use App\Models\Product;   
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Auth;
use DB;

class OrderController extends Controller
{
    // Megjeleníti a várakozó (pending) rendeléseket
    public function PendingOrder()
    {
        // Lekérdezi az összes várakozó rendelést az ID szerint csökkenő sorrendben
        $orders = Order::where('status', 'pending')->orderBy('id', 'DESC')->get();
        // Visszaadja a 'pending_orders' nézetet a rendelésekkel
        return view('backend.orders.pending_orders', compact('orders'));
    }

    // Megjeleníti a rendelés részleteit az adminisztrátornak
    public function AdminOrderDetails($order_id)
    {
        // Lekérdezi a rendelést és a felhasználót, aki a rendelést leadta
        $order = Order::with('user')->where('id', $order_id)->first();
        // Lekérdezi a rendeléshez tartozó tételeket és a hozzájuk tartozó termékeket
        $orderItem = OrderItem::with('product')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        // Visszaadja az 'admin_order_details' nézetet a rendelési és tételi adatokkal
        return view('backend.orders.admin_order_details', compact('order', 'orderItem'));
    }

    // Megjeleníti a megerősített (confirmed) rendeléseket
    public function AdminConfirmedOrder()
    {
        // Lekérdezi az összes megerősített rendelést az ID szerint csökkenő sorrendben
        $orders = Order::where('status', 'confirm')->orderBy('id', 'DESC')->get();
        // Visszaadja a 'confirm_orders' nézetet a rendelésekkel
        return view('backend.orders.confirm_orders', compact('orders'));
    }

    // Megjeleníti a feldolgozás alatt lévő (processing) rendeléseket
    public function AdminProcessingOrder()
    {
        // Lekérdezi az összes feldolgozás alatt lévő rendelést az ID szerint csökkenő sorrendben
        $orders = Order::where('status', 'processing')->orderBy('id', 'DESC')->get();
        // Visszaadja a 'processing_orders' nézetet a rendelésekkel
        return view('backend.orders.processing_orders', compact('orders'));
    }

    // Megjeleníti a kézbesített (delivered) rendeléseket
    public function AdminDeliveredOrder()
    {
        // Lekérdezi az összes kézbesített rendelést az ID szerint csökkenő sorrendben
        $orders = Order::where('status', 'delivered')->orderBy('id', 'DESC')->get();
        // Visszaadja a 'delivered_orders' nézetet a rendelésekkel
        return view('backend.orders.delivered_orders', compact('orders'));
    }

    // Várakozó rendelést megerősít
    public function PendingToConfirm($order_id)
    {
        // Frissíti a rendelés státuszát 'confirm' állapotra
        Order::findOrFail($order_id)->update(['status' => 'confirm']); 
        // Sikeres frissítést jelző értesítést küld vissza
        $notification = [
            'message'    => 'Rendelés sikeresen megerősítve!',
            'alert-type' => 'success'
        ];
        return redirect()->route('admin.confirmed.order')->with($notification);
    }

    // Megerősített rendelést feldolgozás alatt lévővé állít
    public function ConfirmToProcessing($order_id)
    {
        // Frissíti a rendelés státuszát 'processing' állapotra
        Order::findOrFail($order_id)->update(['status' => 'processing']); 
        // Sikeres frissítést jelző értesítést küld vissza
        $notification = [
            'message'    => 'Rendelés feldolgozás alatt!',
            'alert-type' => 'success'
        ];
        return redirect()->route('admin.processing.order')->with($notification);
    }

    // Feldolgozás alatt lévő rendelést kézbesítetté állít
    public function ProcessingToDelivered($order_id)
    {
        // Lekérdezi az összes terméket, amely a rendeléshez tartozik
        $product = OrderItem::where('order_id', $order_id)->get();

        // Csökkenti a termékek mennyiségét a raktárban az eladott mennyiséggel
        foreach ($product as $item) {
            Product::where('id', $item->product_id)->update([
                'product_qty' => DB::raw('product_qty - '.$item->qty)
            ]);
        }

        // Frissíti a rendelés státuszát 'delivered' állapotra
        Order::findOrFail($order_id)->update(['status' => 'delivered']);
        // Sikeres frissítést jelző értesítést küld vissza
        $notification = [
            'message'    => 'Rendelés szállítás alatt megerősítve!',
            'alert-type' => 'success'
        ];
        return redirect()->route('admin.delivered.order')->with($notification);
    }  

    // Számla generálása és letöltése
    public function AdminInvoiceDownload($order_id)
    {
        // Lekérdezi a rendelést és a felhasználót, aki a rendelést leadta
        $order = Order::with('user')->where('id', $order_id)->first();
        // Lekérdezi a rendeléshez tartozó tételeket és a hozzájuk tartozó termékeket
        $orderItem = OrderItem::with('product')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();

        // Generálja a PDF-et a rendelési és tételi adatokkal
        $pdf = Pdf::loadView('backend.orders.admin_order_invoice', compact('order', 'orderItem'))
                   ->setPaper('a4')
                   ->setOption([
                       'tempDir' => public_path(),
                       'chroot'  => public_path()
                   ]);
        // Letölti a PDF-et 'invoice.pdf' néven
        return $pdf->download('invoice.pdf');
    }
}
