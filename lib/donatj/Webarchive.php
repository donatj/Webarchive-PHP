<?php

namespace donatj;

use CFPropertyList\CFArray;
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
	/**
	 * @var \CFPropertyList\CFDictionary
	 */
	private $mainDoc;
	/**
	 * @var \CFPropertyList\CFArray
	 */
	private $subDocs;

	function __construct() {
		$this->plist = new CFPropertyList();
		$this->dict  = new CFDictionary();
		$this->plist->add($this->dict);

		$this->dict->add('WebMainResource', $this->mainDoc = new CFDictionary());

		$this->dict->add('WebSubresources', $this->subDocs = new CFArray());

	}

	public function addMainResource( $content, $url = false, $mime = 'text/html', $charset = 'UTF-8', $headers = false ) {

		$this->addDocument($this->mainDoc, $content, $url, $mime, $charset, $headers);

	}

	private function addDocument( CFDictionary $dict, $content, $url, $mime, $charset, $headers ) {

		$dict->add('WebResourceData', new CFData($content));
		$dict->add('WebResourceMIMEType', new CFString($mime));

		if( $charset ) {
			$dict->add('WebResourceTextEncodingName', new CFString($charset));
		}

		if( $url ) {
			$dict->add('WebResourceURL', new CFString($url));
		}

		if( $headers ) {
			$dict->add('WebResourceResponse', new CFData($headers));
		}

	}

	public function addSubResource( $content, $url, $mime = 'text/html', $charset = false, $headers = false ) {
		$this->subDocs->add($dict = new CFDictionary());

		$this->addDocument($dict, $content, $url, $mime, $charset, $headers);
	}

	function save( $filename ) {
		$this->plist->saveBinary($filename);
	}

	function output() {
		$this->plist->saveBinary('php://output');
	}


}