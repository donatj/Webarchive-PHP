<?php

namespace donatj;

use CFPropertyList\CFData;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFPropertyList;
use CFPropertyList\CFString;

class Webarchive {

	/**
	 * @var \CFPropertyList\CFPropertyList
	 */
	private $plist;

	/**
	 * @var \CFPropertyList\CFDictionary
	 */
	private $dict;

	private $mainDoc;

	function __construct() {
		$this->plist = new CFPropertyList();
		$this->dict  = new CFDictionary();
		$this->plist->add($this->dict);

		$this->dict->add('WebMainResource', $this->mainDoc = new CFDictionary());

	}

	function addMainResource( $content, $url = false, $charset = 'UTF-8', $mime = 'text/html' ) {

		$this->mainDoc->add('WebResourceData', new CFData($content));
		$this->mainDoc->add('WebResourceMIMEType', new CFString($mime));
		$this->mainDoc->add('WebResourceTextEncodingName', new CFString($charset));

		if( $url ) {
			$this->mainDoc->add('WebResourceURL', new CFString($url));
		}

	}

	function addSubResources( $content, $url, $charset = 'UTF-8', $mime = 'text/html' ) {

	}

	function save($filename) {
		$this->plist->saveBinary( $filename );
	}

	function output() {
		$this->plist->saveBinary( 'php://output' );
	}


}