<?php
// Simple router to serve HTML files while keeping extensionless URLs.
// Put this file in the project root (same folder as index.html).

// Get the request path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// If the project is hosted in a subfolder (like /ROIwebsite), remove the directory prefix
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\');
if ($baseDir !== '' && $baseDir !== '/' && strpos($uri, $baseDir) === 0) {
    $uri = substr($uri, strlen($baseDir));
}

$uri = '/' . ltrim($uri, '/');

// Normalise empty path to '/'
if ($uri === '') $uri = '/';

// Map of extensionless routes to actual files on disk (relative to this file)
$routes = [
    '/' => 'index.html',
    '/about' => 'components/about.html',
    '/contact' => 'components/contact.html',
    '/industries' => 'components/industries.html',
    '/blog' => 'blog/mainBlog.html',

    // Blog posts
    '/blog/uxMarketing' => 'blog/uxMarketing.html',
    '/blog/brandingBlog' => 'blog/brandingBlog.html',
    '/blog/socialMediaBlog' => 'blog/socialMediaBlog.html',
    '/blog/seo' => 'blog/seo.html',

    // Services - map friendly slugs to actual files
    '/services/ppc' => 'components/services/Digital-Marketing/PPCservices.html',
    '/services/seo' => 'components/services/Digital-Marketing/SEOservices.html',
    '/services/email-marketing' => 'components/services/Digital-Marketing/emailMarketing.html',
    '/services/website-design' => 'components/services/Development-Design/websiteDesign-Development.html',
    '/services/social-media-design' => 'components/services/Development-Design/socialMediaDesign.html',
    '/services/landing-page' => 'components/services/Development-Design/landingPageDsign.html',
    '/services/social-media-advertising' => 'components/services/Advertise-Management/smAdvertising.html',
    '/services/social-media-management' => 'components/services/Advertise-Management/smManagement.html',
    '/services/listing-management' => 'components/services/Advertise-Management/lisitngManagementservice.html',
    '/services/gmb' => 'components/services/Google-My-Business/GMBservices.html',
    '/services/gmb-management' => 'components/services/Google-My-Business/GMBmanagement.html',
    '/services/gmb-setup' => 'components/services/Google-My-Business/GMBsetupService.html',
    '/services/gmb-support' => 'components/services/Google-My-Business/GMBsupportService.html',
    '/services/gmb-optimization' => 'components/services/Google-My-Business/GMBoptimizationServies.html',
    '/services/gmb-reinstatement' => 'components/services/Google-My-Business/GMBreinstatementService.html',
    '/services/call-tracking' => 'components/services/websiteCallTracking.html',
    '/services/website-call-tracking' => 'components/services/websiteCallTracking.html',
    '/services/video-production' => 'components/services/videoProduction.html',
    '/services/conversion-rate' => 'components/services/conversationRate.html',
    '/services/api-development' => 'components/services/apiDevelopment.html',
];

// Normalize trailing slashes: remove if not root
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = rtrim($uri, '/');
}

// Check direct mappings first
if (isset($routes[$uri])) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . $routes[$uri];
    if (is_file($file)) {
        header('Content-Type: text/html; charset=utf-8');
        // If app is hosted in a subfolder (baseDir), rewrite absolute local URLs in the file
        $content = file_get_contents($file);
        if (!empty($baseDir) && $baseDir !== '/') {
            // Ensure baseDir begins with a leading slash
            $prefix = $baseDir;
            // Replace occurrences of quoted local absolute paths like href="/path" or src='/path'
            $content = preg_replace_callback('#([\'\"])(/(?!/)[^\'\"]*)\1#', function($m) use ($prefix) {
                // $m[1] = quote, $m[2] = path starting with /
                $path = $m[2];
                // If already starts with prefix, leave unchanged
                if (strpos($path, $prefix . '/') === 0 || $path === $prefix) {
                    return $m[0];
                }
                return $m[1] . $prefix . $path . $m[1];
            }, $content);

            // Also replace JS string literals that start with a single slash (not //) e.g. fetch('/api/...')
            $content = preg_replace_callback('#([\'\"])\/(?!/)([^\'\"]*)\1#', function($m) use ($prefix) {
                $quote = $m[1];
                $path = '/' . $m[2];
                if (strpos($path, $prefix . '/') === 0 || $path === $prefix) {
                    return $m[0];
                }
                return $quote . $prefix . $path . $quote;
            }, $content);
        }

        echo $content;
        exit;
    }
}

// Fallback behaviour: try adding .html to the requested path (relative to project root)
$tryPaths = [];
$path = ltrim($uri, '/');
if ($path === '') {
    $tryPaths[] = __DIR__ . DIRECTORY_SEPARATOR . 'index.html';
} else {
    $tryPaths[] = __DIR__ . DIRECTORY_SEPARATOR . $path . '.html';
    $tryPaths[] = __DIR__ . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $path . '.html';
    $tryPaths[] = __DIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . $path . '.html';
    $tryPaths[] = __DIR__ . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . 'index.html';
}

foreach ($tryPaths as $fp) {
    if (is_file($fp)) {
        header('Content-Type: text/html; charset=utf-8');
        $content = file_get_contents($fp);

        // If app is hosted in a subfolder (baseDir), rewrite absolute local URLs
        if (!empty($baseDir) && $baseDir !== '/') {
            $prefix = $baseDir;
            // Prefix quoted local absolute paths (href="/..." or src='/...')
            $content = preg_replace_callback('#([\'\"])(/(?!/)[^\'\"]*)\1#', function($m) use ($prefix) {
                $path = $m[2];
                if (strpos($path, $prefix . '/') === 0 || $path === $prefix) {
                    return $m[0];
                }
                return $m[1] . $prefix . $path . $m[1];
            }, $content);

            // Also replace JS string literals that start with a single slash (not //)
            $content = preg_replace_callback('#([\'\"])\/(?!/)([^\'\"]*)\1#', function($m) use ($prefix) {
                $quote = $m[1];
                $path = '/' . $m[2];
                if (strpos($path, $prefix . '/') === 0 || $path === $prefix) {
                    return $m[0];
                }
                return $quote . $prefix . $path . $quote;
            }, $content);

            // Inject a <base> tag so relative URLs resolve correctly when hosted in a subfolder.
            $baseHref = rtrim($prefix, '/') . '/';
            // Only inject if there's a <head> tag and no existing <base> tag
            if (stripos($content, '<head') !== false && stripos($content, '<base') === false) {
                // Use a real newline instead of the literal "\n" sequence to avoid stray "\\n" text showing up
                $replacement = '<head$1>' . "\n" . '    <base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '">';
                $content = preg_replace('/<head(\s[^>]*)?>/i', $replacement, $content, 1);
            }
        }

        echo $content;
        exit;
    }
}

// If nothing matched, return 404 and a simple message
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo "<!doctype html><html><head><meta charset=\"utf-8\"><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The requested URL " . htmlspecialchars($uri, ENT_QUOTES, 'UTF-8') . " was not found on this server.</p></body></html>";
exit;
