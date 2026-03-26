<?php

namespace App\Http\Controllers;

use App\Models\AboutContent;
use App\Models\GalleryImage;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'about' => AboutContent::query()->first(),
            'products' => Product::query()->latest()->take(12)->get(),
            'galleryImages' => GalleryImage::query()
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->get(),
            'siteSettings' => SiteSetting::query()->first(),
        ]);
    }
}
