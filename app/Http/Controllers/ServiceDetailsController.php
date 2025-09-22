<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceDetailsController extends Controller
{
    public function show(Service $service)
    {
        // Return JSON only if explicitly requested
        if (request()->wantsJson() || request('format') === 'json') {
            return response()->json([
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'duration' => $service->duration,
                'type' => $service->type,
                'shop' => $service->shop ? [
                    'id' => $service->shop->id,
                    'name' => $service->shop->name,
                    'address' => $service->shop->full_address,
                ] : null,
            ]);
        }

        $service->load('shop');
        return view('services.show', compact('service'));
    }
} 