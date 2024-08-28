<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTime;
use App\Models\Order;
use App\Models\User;

class ReportController extends Controller
{
    // Jelentés nézet megjelenítése
    public function ReportView()
    {
        // Visszaadja a jelentés nézetet
        return view('backend.report.report_view');
    }    

    // Rendelések keresése dátum szerint
    public function SearchByDate(Request $request)
    {
        // Lekérdezi a dátumot a kérésből és létrehoz egy DateTime objektumot
        $date = new DateTime($request->date);
        // Formázza a dátumot 'd F Y' formátumra
        $formatDate = $date->format('d F Y');
        
        // Lekérdezi azokat a rendeléseket, amelyek az adott napon történtek
        $orders = Order::where('order_date', $formatDate)->latest()->get();
        
        // Visszaadja a jelentést a rendelésekkel és a formázott dátummal
        return view('backend.report.report_by_date', compact('orders', 'formatDate'));
    }    

    // Rendelések keresése hónap és év szerint
    public function SearchByMonth(Request $request)
    {
        // Lekérdezi a hónapot és az évet a kérésből
        $month = $request->month; 
        $year = $request->year_name; 
        
        // Lekérdezi azokat a rendeléseket, amelyek az adott hónapban és évben történtek
        $orders = Order::where('order_mounth', $month)
                       ->where('order_year', $year)
                       ->latest()
                       ->get();
        
        // Visszaadja a jelentést a rendelésekkel, hónappal és évvel
        return view('backend.report.report_by_month', compact('orders', 'month', 'year'));
    }  

    // Rendelések keresése év szerint
    public function SearchByYear(Request $request)
    {
        // Lekérdezi az évet a kérésből
        $year = $request->year; 
        
        // Lekérdezi azokat a rendeléseket, amelyek az adott évben történtek
        $orders = Order::where('order_year', $year)->latest()->get();
        
        // Visszaadja a jelentést a rendelésekkel és az évvel
        return view('backend.report.report_by_year', compact('orders', 'year'));
    }    

    // Rendelések keresése felhasználó szerint
    public function OrderByUser(Request $request)
    {
        // Lekérdezi az összes felhasználót, akinek a szerepe 'user', és rendezve visszaadja őket
        $users = User::where('role', 'user')->latest()->get();        
        
        // Visszaadja a jelentést a felhasználókkal
        return view('backend.report.report_by_user', compact('users'));
    }  

    // Rendelések keresése adott felhasználó szerint
    public function SearchByUser(Request $request)
    {
        // Lekérdezi a felhasználó ID-ját a kérésből
        $users = $request->user; 
        
        // Lekérdezi azokat a rendeléseket, amelyeket a megadott felhasználó készített
        $orders = Order::where('user_id', $users)->latest()->get();       
        
        // Visszaadja a jelentést a felhasználóval és a rendelésekkel
        return view('backend.report.report_by_user_show', compact('users', 'orders'));
    }
}
