<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MediaItem;
use App\Models\SiteSetting;
use Illuminate\Http\Response;

class PortfolioController extends Controller
{
    public function home()
    {
        $categories = Category::where('is_visible', true)
            ->whereHas('mediaItems', fn($q) => $q->where('is_published', true))
            ->orderBy('position')
            ->get();

        $items = MediaItem::with(['category', 'media'])
            ->where('is_published', true)
            ->orderBy('position')
            ->get();

        return view('home', compact('categories', 'items'));
    }

    public function about()
    {
        return view('about');
    }

    public function sitemap(): Response
    {
        $items = MediaItem::with('media')->where('is_published', true)->orderBy('position')->get();
        $lastmod = collect([
            $items->max('updated_at'),
            SiteSetting::current()->updated_at,
        ])->filter()->max() ?? now();

        return response()
            ->view('sitemap', compact('items', 'lastmod'))
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nDisallow: /admin\nDisallow: /livewire\n\nSitemap: " . route('sitemap') . "\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}