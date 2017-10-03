# Flags

[![Latest Stable Version](https://poser.pugx.org/donatj/webarchive/version)](https://packagist.org/packages/donatj/webarchive)
[![Total Downloads](https://poser.pugx.org/donatj/webarchive/downloads)](https://packagist.org/packages/donatj/webarchive)
[![License](https://poser.pugx.org/donatj/webarchive/license)](https://packagist.org/packages/donatj/webarchive)


A library for writing Apple Safari's 'webarchive' binary plist files

## Requirements

- **rodneyrehm/plist**: 2.*
- **php**: >=5.3

## Installing

Install the latest version with:

```bash
composer require 'donatj/webarchive'
```

## Example

To Come!

## Documentation

### Class: \donatj\Webarchive

#### Method: Webarchive->__construct

```php
function __construct()
```

Webarchive constructor

---

#### Method: Webarchive->addMainResource

```php
function addMainResource($content [, $url = null [, $mime = 'text/html' [, $charset = 'UTF-8' [, $headers = null]]]])
```

##### Parameters:

- ***string*** `$content`
- ***string*** | ***null*** `$url`
- ***string*** `$mime`
- ***string*** | ***null*** `$charset`
- ***string*** | ***null*** `$headers`

---

#### Method: Webarchive->addSubResource

```php
function addSubResource($content, $url [, $mime = 'text/html' [, $charset = null [, $headers = null]]])
```

##### Parameters:

- ***string*** `$content`
- ***string*** | ***null*** `$url`
- ***string*** `$mime`
- ***string*** | ***null*** `$charset`
- ***string*** | ***null*** `$headers`

---

#### Method: Webarchive->save

```php
function save($filename)
```

Save to a file

##### Parameters:

- ***mixed*** `$filename` - string

---

#### Method: Webarchive->output

```php
function output()
```

Output to `php://output`