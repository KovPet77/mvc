<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AllUserController extends Controller
{
    // Felhasználói irányítópult megjelenítése
    public function UserIndex()
    {
        // Visszaadja a felhasználói irányítópult nézetet
        return view('frontend.userdashboard.user_index');
    }

    // Felhasználói fiók adatok megjelenítése
    public function UserAccount()
    {
        // Lekérdezi az aktuálisan bejelentkezett felhasználó ID-ját
        $id = Auth::user()->id;
        // Lekérdezi a felhasználói adatokat
        $userData = User::find($id);
        // Visszaadja a fiók adatok nézetet a felhasználói adatokkal
        return view('frontend.userdashboard.account_details', compact('userData'));
    }

    // Jelszó módosító oldal megjelenítése
    public function UserChangePassword()
    {
        // Visszaadja a jelszó változtatási nézetet
        return view('frontend.userdashboard.user_change_password');
    }

    // Felhasználó rendeléseinek oldal megjelenítése
    public function UserOrderPage()
    {
        // Lekérdezi az aktuálisan bejelentkezett felhasználó ID-ját
        $id = Auth::user()->id;
        // Lekérdezi az összes rendelést az aktuális felhasználóhoz, ID szerint csökkenő sorrendben
        $orders = Order::where('user_id', $id)->orderBy('id', 'DESC')->get();
        // Visszaadja a rendelési oldal nézetet a rendelésekkel
        return view('frontend.userdashboard.user_order_page', compact('orders'));
    }

    // Rendelés részleteinek megjelenítése
    public function UserOrderDetails($order_id)
    {
        // Lekérdezi a rendelést és a felhasználót, aki a rendelést leadta
        $order = Order::with('user')->where('id', $order_id)->where('user_id', Auth::id())->first();
        // Lekérdezi a rendeléshez tartozó tételeket és a hozzájuk tartozó termékeket
        $orderItem = OrderItem::with('product')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        // Visszaadja a rendelés részleteit tartalmazó nézetet
        return view('frontend.order.order_details', compact('order', 'orderItem'));
    }

    // Számla generálása és letöltése
    public function UserOrderInvoice($order_id)
    {
        // Lekérdezi a rendelést és a felhasználót, aki a rendelést leadta
        $order = Order::with('user')->where('id', $order_id)->where('user_id', Auth::id())->first();
        // Lekérdezi a rendeléshez tartozó tételeket és a hozzájuk tartozó termékeket
        $orderItem = OrderItem::with('product')->where('order_id', $order_id)->orderBy('id', 'DESC')->get();
        // Generálja a PDF-et a rendelési és tételi adatokkal
        $pdf = Pdf::loadView('frontend.order.order_invoice', compact('order', 'orderItem'))
                   ->setPaper('a4')
                   ->setOption([
                       'tempDir' => public_path(),
                       'chroot'  => public_path()
                   ]);
        // Letölti a PDF-et 'invoice.pdf' néven
        return $pdf->download('invoice.pdf');
    }

    // Rendelés visszaküldési kérés kezelése
    public function ReturnOrder(Request $request, $order_id)
    {
        // Frissíti a rendelést a visszaküldési dátummal és okkal
        Order::findOrFail($order_id)->update([
            'return_date' => Carbon::now()->format('d F Y'), // Formázott dátum
            'return_reason' => $request->return_reason,
            'return_order' => 1
        ]);

        // Sikeres visszaküldési kérés értesítést küld vissza
        $notification = [
            'message'    => 'Rendelés visszaküldési kérés elküldve',
            'alert-type' => 'success'
        ];
        return redirect()->route('user.order.page')->with($notification);
    }

    // Visszaküldött rendelések oldal megjelenítése
    public function ReturnOrderPage()
    {
        // Lekérdezi az összes visszaküldési okkal rendelkező rendelést az aktuális felhasználóhoz
        $orders = Order::where('user_id', Auth::id())
                        ->where('return_reason', '!=', NULL)
                        ->orderBy('id', 'DESC')
                        ->get();
        // Visszaadja a visszaküldött rendelések nézetet a rendelésekkel
        return view('frontend.order.return_order_view', compact('orders'));
    }

    // Rendelés követési oldal megjelenítése
    public function UserTrackOrder()
    {
        // Visszaadja a rendelés követési nézetet
        return view('frontend.userdashboard.user_track_order');
    }

    // Rendelés követése alapján történő keresés
    public function OrderTracking(Request $request)
    {
        // Lekérdezi az invoice számot a kérésből
        $invoice = $request->code;
        // Lekérdezi az rendelést az invoice száma alapján
        $track = Order::where('invoice_no', $invoice)->first();

        if ($track) {
            // Ha talál rendelést, visszaadja a követési nézetet
            return view('frontend.tracking.track_order', compact('track'));
        } else {
            // Ha nem talál rendelést, értesítést küld vissza hibaüzenettel
            $notification = [
                'message'    => 'Nem létező számla sorszám!',
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }
    }
}
