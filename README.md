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
* PHP extensions: bcmath, fileinfo, gd

Installation
------------

Add it to your `composer.json` file:

```
composer require bigfish/pdf417 ^1.0.0
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

ImageRenderer
-------------

Renders the barcode to an image using [Intervention Image](http://image.intervention.io/)

Render function returns an instance of `Intervention\Image\Image`.

#### Options

Option  | Default | Description
------- | ------- | -----------
format  | png     | Output format, one of: `jpg`, `png`, `gif`, `tif`, `bmp`, `data-url`
quality | 90      | Jpeg encode quality (1-10)
scale   | 3       | Scale of barcode elements (1-20)
ratio   | 3       | Height to width ration of barcode elements (1-10)
padding | 20      | Padding in pixels (0-50)
color   | #000000 | Foreground color as a hex code
bgColor | #ffffff | Background color as a hex code

#### Examples

```php
$pdf417 = new PDF417();
$data = $pdf417->encode("My hovercraft is full of eels");

// Create a PNG image, red on green background, extra big
$renderer = new ImageRenderer([
    'format' => 'png',
    'color' => '#FF0000',
    'bgColor' => '#00FF00',
    'scale' => 10,
]);

$image = $renderer->render($data);
$image->save('hovercraft.png');
```

The `data-url` format is not like the others, it returns a base64 encoded PNG
image which can be used in an image `src` or in CSS. When you create an image
using this format:

```php
$pdf417 = new PDF417();
$data = $pdf417->encode('My nipples explode with delight');

$renderer = new ImageRenderer([
    'format' => 'data-url'
]);
$img = $renderer->render($data);
```

You can use it directly in your HTML or CSS code:

```html
<img src="<?= $img->encoded ?>" />
```

And this will be rendered as:
```html
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA.... " />
```

Thanks
------

Without these pages, implementation of this project would have been much harder:

* http://grandzebu.net/informatique/codbar-en/pdf417.htm
* http://www.idautomation.com/barcode-faq/2d/pdf417/
