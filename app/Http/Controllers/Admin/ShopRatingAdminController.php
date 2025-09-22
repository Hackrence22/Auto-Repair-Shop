<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopRating;
use App\Models\Shop;

class ShopRatingAdminController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();
        $query = ShopRating::with(['shop:id,name', 'user:id,name,profile_picture,avatar'])
            ->latest();

        if ($admin && method_exists($admin, 'isOwner') && $admin->isOwner()) {
            $ownerShopIds = Shop::where('admin_id', $admin->id)->pluck('id');
            $query->whereIn('shop_id', $ownerShopIds);
        }

        $ratings = $query->paginate(20)->withQueryString();
        return view('admin.ratings.index', compact('ratings'));
    }
}


