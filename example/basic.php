<?php

require_once __DIR__ . '/../vendor/autoload.php';

$archive = new \donatj\Webarchive();

// Main HTML document (entry point)
$html = "<html><head><title>Example</title><link rel=\"stylesheet\" href=\"style.css\"></head><body><h1>Hello Webarchive</h1></body></html>";
$archive->addMainResource($html, 'https://example.test/index.html', 'text/html', 'UTF-8');

// A simple CSS subresource referenced by the HTML page
$css = "body{font-family:sans-serif;background:#fafafa;color:#333;} h1{color:#0066cc;}";
$archive->addSubResource($css, 'https://example.test/style.css', 'text/css');

// Save the webarchive (binary plist) to a file
$outFile = __DIR__ . '/basic.webarchive';
$archive->save($outFile);

echo "Wrote webarchive: " . $outFile . "\n";
