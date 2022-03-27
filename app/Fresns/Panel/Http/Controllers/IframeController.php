<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;

class IframeController extends Controller
{
    protected function addLangToUrl($url)
    {
        // Parse the passed url
        $queryString = parse_url($url);
        // Get the query parameters in the url separately
        $query = $queryString['query'] ?? '';

        // Converting query parameters into arrays
        parse_str($query, $params);
        
        // Passing on the language tag
        $langParams = array_merge([
            'lang' => \App::getLocale(),
        ], $params);

        // Splicing query parameters
        $langQueryString = http_build_query($langParams);

        // Splicing url
        $langUrl = $queryString['path'].'?'.$langQueryString;

        return $langUrl;
    }

    // Plugin iframe
    public function plugin(Request $request)
    {
        // Plugin Sidebar
        $enablePlugins = Plugin::type(1)->where('is_enable', 1)->get();

        $url = $this->addLangToUrl($request->url);

        return view('FsView::iframe.plugin', compact('url', 'enablePlugins'));
    }

    // Client iframe
    public function client(Request $request)
    {
        $url = $this->addLangToUrl($request->url);

        return view('FsView::iframe.client', compact('url'));
    }

    // Fresns Market iframe
    public function market(Request $request)
    {
        $url = $request->url;

        return view('FsView::iframe.market', compact('url'));
    }
}
