PDF 417 barcode generator
=========================

[![Build Status](https://img.shields.io/travis/ihabunek/pdf417-php.svg?style=flat-square)](https://travis-ci.org/ihabunek/pdf417-php)
[![Latest Version](https://img.shields.io/packagist/v/bigfish/pdf417.svg?style=flat-square&label=stable)](https://packagist.org/packages/bigfish/pdf417)
[![Total Downloads](https://img.shields.io/packagist/dt/bigfish/pdf417.svg?style=flat-square)](https://packagist.org/packages/bigfish/pdf417)
[![License](https://img.shields.io/packagist/l/bigfish/pdf417.svg?style=flat-square)](https://packagist.org/packages/bigfish/pdf417)
[![Author](https://img.shields.io/badge/author-%40ihabunek-blue.svg?style=flat-square)](https://twitter.com/ihabunek)

Requirements
------------

Requires the following components:

* PHP >= 5.4
* Fileinfo extension
* GD extension

Installation
------------

Add it to your `composer.json` file:

```
composer require bigfish/pdf417 ~0.1
```

Usage overview
--------------

```php
require 'vendor/autoload.php';

use BigFish\PDF417\PDF417;
use BigFish\PDF417\Renderers\ImageRenderer;
use BigFish\PDF417\Renderers\SvgRenderer;

// Text to be encoded into the barcode
$text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur
imperdiet sit amet magna faucibus aliquet. Aenean in velit in mauris imperdiet
scelerisque. Maecenas a auctor erat.';

// Encode the data, returns a BarcodeData object
$pdf417 = new PDF417();
$data = $pdf417->encode($text);

// Create a PNG image
$renderer = new ImageRenderer([
    'format' => 'png'
]);

$image = $renderer->render($data);

// Create an SVG representation
$renderer = new SvgRenderer([
    'color' => 'black',
]);

$svg = $renderer->render($data);
```

Thanks
------

Without these pages, implementation of this project would have been much harder:

* http://grandzebu.net/informatique/codbar-en/pdf417.htm
* http://www.idautomation.com/barcode-faq/2d/pdf417/
