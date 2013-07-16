<?php

include('lib/donatj/Webarchive.php');

require_once(__DIR__.'/../vendor/autoload.php');

$archive = new \donatj\Webarchive();

$archive->addMainResource( file_get_contents('http://donatstudios.com/assets/38/Circle-Generator/'), 'http://donatstudios.com/assets/38/Circle-Generator/' );

$archive->addSubResource( file_get_contents('https://ajax.googleapis.com/ajax/libs/mootools/1.3.2/mootools.js'), 'https://ajax.googleapis.com/ajax/libs/mootools/1.3.2/mootools.js');

$archive->addSubResource( file_get_contents('http://donatstudios.com/assets/38/Circle-Generator/Base64.js'), 'http://donatstudios.com/assets/38/Circle-Generator/Base64.js' );

$archive->addSubResource( file_get_contents('http://donatstudios.com/assets/38/Circle-Generator/range-polyfill.js'), 'http://donatstudios.com/assets/38/Circle-Generator/range-polyfill.js' );

$archive->addSubResource( file_get_contents('http://donatstudios.com/assets/38/Circle-Generator/number-polyfill.js'), 'http://donatstudios.com/assets/38/Circle-Generator/number-polyfill.js' );

$archive->save('test.webarchive');