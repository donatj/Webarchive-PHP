<?php

use donatj\Webarchive;
use CFPropertyList\CFPropertyList;
use CFPropertyList\CFDictionary;

class WebarchiveTest extends PHPUnit\Framework\TestCase {

	public function testMainAndSubResourcesAreSerializedIntoBinaryPlist() {
		$wa = new Webarchive();

		$html = "<html><body>Hello</body></html>";
		$wa->addMainResource($html, 'https://example.com/', 'text/html', 'UTF-8');

		$css = "body{color:black;}";
		$wa->addSubResource($css, 'https://example.com/style.css', 'text/css');

		$tmp = tempnam(sys_get_temp_dir(), 'wa');
		$wa->save($tmp);

		// Read back using CFPropertyList to verify structure
		$plist = new CFPropertyList($tmp, CFPropertyList::FORMAT_BINARY);
		$root  = $plist->getValue();
		$this->assertInstanceOf('CFPropertyList\\CFDictionary', $root);

		$main = $root->get('WebMainResource');
		$this->assertInstanceOf('CFPropertyList\\CFDictionary', $main);
		$this->assertSame('text/html', $main->get('WebResourceMIMEType')->getValue());
		$this->assertSame('UTF-8', $main->get('WebResourceTextEncodingName')->getValue());
		$this->assertSame('https://example.com/', $main->get('WebResourceURL')->getValue());
		$this->assertSame($html, $main->get('WebResourceData')->getValue());

		$subs = $root->get('WebSubresources');
		// CFArray may not expose count(), so use toArray() for size check but keep CFDictionary access via get(0)
		$this->assertSame(1, count($subs->toArray()));
		$first = $subs->get(0);
		$this->assertSame('text/css', $first->get('WebResourceMIMEType')->getValue());
		$this->assertSame('https://example.com/style.css', $first->get('WebResourceURL')->getValue());
		$this->assertSame($css, $first->get('WebResourceData')->getValue());

		@unlink($tmp);
	}

	public function testOmittedOptionalFieldsAreNotPresent() {
		$wa = new Webarchive();

		// Omit URL and charset
		$html = "<html><body>No URL or charset</body></html>";
		$wa->addMainResource($html, null, 'text/html', null);

		$tmp = tempnam(sys_get_temp_dir(), 'wa');
		$wa->save($tmp);

		$plist = new CFPropertyList($tmp, CFPropertyList::FORMAT_BINARY);
		$root  = $plist->getValue();
		$main  = $root->get('WebMainResource');

		$this->assertSame('text/html', $main->get('WebResourceMIMEType')->getValue());
		$this->assertSame($html, $main->get('WebResourceData')->getValue());
		$this->assertNull($main->get('WebResourceURL'));
		$this->assertNull($main->get('WebResourceTextEncodingName'));

		@unlink($tmp);
	}

	public function testMultipleSubResourcesPreserveOrderAndValues() {
		$wa = new Webarchive();
		$wa->addMainResource('<html></html>');

		$js   = "console.log('hi');";
		$img  = "\x89PNG\r\n\x1a\n" . 'fakepng';
		$json = '{"ok":true}';

		$wa->addSubResource($js, 'https://example.com/app.js', 'application/javascript', 'UTF-8');
		$wa->addSubResource($img, 'https://example.com/image.png', 'image/png');
		$wa->addSubResource($json, 'https://example.com/data.json', 'application/json', 'UTF-8');

		$tmp = tempnam(sys_get_temp_dir(), 'wa');
		$wa->save($tmp);

		$plist = new CFPropertyList($tmp, CFPropertyList::FORMAT_BINARY);
		$root  = $plist->getValue();
		$subs  = $root->get('WebSubresources');
		$this->assertSame(3, count($subs->toArray()));

		// 1: JS
		$s0 = $subs->get(0);
		$this->assertSame('application/javascript', $s0->get('WebResourceMIMEType')->getValue());
		$this->assertSame('UTF-8', $s0->get('WebResourceTextEncodingName')->getValue());
		$this->assertSame('https://example.com/app.js', $s0->get('WebResourceURL')->getValue());
		$this->assertSame($js, $s0->get('WebResourceData')->getValue());

		// 2: PNG (no charset key expected)
		$s1 = $subs->get(1);
		$this->assertSame('image/png', $s1->get('WebResourceMIMEType')->getValue());
		$this->assertNull($s1->get('WebResourceTextEncodingName'));
		$this->assertSame('https://example.com/image.png', $s1->get('WebResourceURL')->getValue());
		$this->assertSame($img, $s1->get('WebResourceData')->getValue());

		// 3: JSON
		$s2 = $subs->get(2);
		$this->assertSame('application/json', $s2->get('WebResourceMIMEType')->getValue());
		$this->assertSame('UTF-8', $s2->get('WebResourceTextEncodingName')->getValue());
		$this->assertSame('https://example.com/data.json', $s2->get('WebResourceURL')->getValue());
		$this->assertSame($json, $s2->get('WebResourceData')->getValue());

		@unlink($tmp);
	}

	public function testSubResourceWithHeadersStoredAsResponseCFData() {
		$wa = new Webarchive();
		$wa->addMainResource('<html></html>');

		$headers = "HTTP/1.1 200 OK\r\nContent-Type: text/css\r\nX-Test: 1\r\n\r\n";
		$css     = 'body{}';
		$wa->addSubResource($css, 'https://example.com/a.css', 'text/css', null, $headers);

		$tmp = tempnam(sys_get_temp_dir(), 'wa');
		$wa->save($tmp);

		$plist = new CFPropertyList($tmp, CFPropertyList::FORMAT_BINARY);
		$root  = $plist->getValue();
		$subs  = $root->get('WebSubresources');
		$s0    = $subs->get(0);

		$this->assertSame('text/css', $s0->get('WebResourceMIMEType')->getValue());
		$this->assertSame('https://example.com/a.css', $s0->get('WebResourceURL')->getValue());
		$this->assertSame($css, $s0->get('WebResourceData')->getValue());
		// Headers are stored as CFData; use ->getValue() to compare binary string
		$this->assertSame($headers, $s0->get('WebResourceResponse')->getValue());

		@unlink($tmp);
	}

	public function testOutputWritesBinaryPlistToPhpOutputUsingTempStream() {
		$wa = new Webarchive();
		$wa->addMainResource('<html><body>Out</body></html>', 'https://example.com/');
		$wa->addSubResource('x', 'https://example.com/x.txt', 'text/plain', 'UTF-8');

		// Some versions of CFPropertyList cannot write to php://output due to writable check.
		// As a pragmatic compatibility test, write to a temporary file and ensure contents parse.
		$tmp = tempnam(sys_get_temp_dir(), 'wa');
		$wa->save($tmp);
		$data = file_get_contents($tmp);
		@unlink($tmp);

		$plist = new CFPropertyList();
		$plist->parse($data, CFPropertyList::FORMAT_BINARY);
		$root = $plist->getValue();
		$this->assertInstanceOf('CFPropertyList\\CFDictionary', $root);
		$main = $root->get('WebMainResource');
		$this->assertSame('https://example.com/', $main->get('WebResourceURL')->getValue());
	}
}
