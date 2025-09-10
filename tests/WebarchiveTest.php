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
        $unlink = true;
            $wa->save($tmp);

            // Read back using CFPropertyList to verify structure
            $plist = new CFPropertyList($tmp, CFPropertyList::FORMAT_BINARY);
            $root = $plist->getValue();
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
}
