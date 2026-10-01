<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Only the public marketing pages belong here; everything behind sign-in
     * is private and marked noindex.
     */
    private const array SITEMAP_ROUTES = ['welcome'];

    public function sitemap(): Response
    {
        // Built here rather than in a Blade view: with short_open_tag on (the
        // PHP default without a php.ini, as in our Docker image) the XML
        // declaration in a template is parsed as a PHP open tag.
        $entries = array_map(
            fn (string $name): string => '<url><loc>'.e(route($name)).'</loc></url>',
            self::SITEMAP_ROUTES,
        );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .implode("\n", $entries)."\n"
            .'</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /sign-in',
            'Disallow: /sign-out',
            'Disallow: /resource-types',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
