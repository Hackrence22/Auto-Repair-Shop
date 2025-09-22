<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopRatingController extends Controller
{
    public function store(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $rating = ShopRating::create([
            'shop_id' => $shop->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Your rating has been saved.');
    }
}


