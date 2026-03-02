<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml
     */
    public function index(): Response
    {
        $annonces = Annonce::where('is_active', true)
            ->select(['id', 'titre', 'slug', 'updated_at'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('sitemap.index', compact('annonces'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate robots.txt dynamically
     */
    public function robots(): Response
    {
        $sitemapUrl = url('/sitemap.xml');
        
        $content = <<<ROBOTS
User-agent: *
Allow: /

# Disallow admin and auth routes
Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /password/
Disallow: /profile
Disallow: /dashboard
Disallow: /messages
Disallow: /favoris
Disallow: /mes-annonces
Disallow: /historique-recherche
Disallow: /api/

# Sitemap
Sitemap: {$sitemapUrl}
ROBOTS;

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
