<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with(['services', 'slotSettings', 'admin'])
            ->withAvg('ratings', 'rating')
            ->active()
            ->ordered()
            ->paginate(6);
        
        return view('shops.index', compact('shops'));
    }

    public function show(Shop $shop)
    {
        $shop->load(['services' => function($query) {
            $query->where('is_active', true);
        }, 'slotSettings' => function($query) {
            $query->where('is_active', true);
        }, 'ratings' => function($query) {
            $query->latest()->with('user:id,name,profile_picture,avatar');
        }, 'admin']);
        
        return view('shops.show', compact('shop'));
    }
} 