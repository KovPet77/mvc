<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;



class SubCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function category()
    {

        // a belongsTo() rakja össze a kettőt. A subCategory tábla category_id oszlop értéke egyezzen 
        //a Category tábla id oszlopának értékével:
        return $this->belongsTo(Category::class, 'category_id', 'id');

    }
}
