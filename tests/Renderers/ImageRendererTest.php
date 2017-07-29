<?php

namespace BigFish\PDF417\Tests\Renderers;

use BigFish\PDF417\BarcodeData;
use BigFish\PDF417\Renderers\ImageRenderer;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;

class ImageRendererTest extends TestCase
{
    public function testContentType()
    {
        $renderer = new ImageRenderer();
        $actual = $renderer->getContentType();
        $expected = "image/png";
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(["format" => "png"]);
        $actual = $renderer->getContentType();
        $expected = "image/png";
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(["format" => "jpg"]);
        $actual = $renderer->getContentType();
        $expected = "image/jpeg";
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(["format" => "gif"]);
        $actual = $renderer->getContentType();
        $expected = "image/gif";
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(["format" => "bmp"]);
        $actual = $renderer->getContentType();
        $expected = "image/bmp";
        $this->assertSame($expected, $actual);

        $renderer = new ImageRenderer(["format" => "tif"]);
        $actual = $renderer->getContentType();
        $expected = "image/tiff";
        $this->assertSame($expected, $actual);

        // data-url format does not have a mime type
        $renderer = new ImageRenderer(["format" => "data-url"]);
        $actual = $renderer->getContentType();
        $this->assertNull($actual);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "format": "foo".
     */
    public function testInvalidFormat()
    {
        new ImageRenderer(["format" => "foo"]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "scale": "0".
     */
    public function testInvalidScale()
    {
        new ImageRenderer(["scale" => 0]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "ratio": "0".
     */
    public function testInvalidRatio()
    {
        new ImageRenderer(["ratio" => 0]);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "padding": "-1".
     */
    public function testInvalidPadding()
    {
        new ImageRenderer(["padding" => -1]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "color": "red".
     */
    public function testInvalidColor()
    {
        new ImageRenderer(["color" => "red"]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "bgColor": "red".
     */
    public function testInvalidBgColor()
    {
        new ImageRenderer(["bgColor" => "red"]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option "quality": "101".
     */
    public function testInvalidQuality()
    {
        new ImageRenderer(["quality" => 101]);
    }

    public function testRenderPNG()
    {
        $data = new BarcodeData();
        $data->codes = [[true, false],[false, true]];

        $scale = 4;
        $ratio = 5;
        $padding = 6;

        $renderer = new ImageRenderer([
            'format' => 'png',
            'scale' => $scale,
            'ratio' => $ratio,
            'padding' => $padding
        ]);


        $png = $renderer->render($data);

        $manager = new ImageManager();
        $image = $manager->make($png);

        // Expected dimensions
        $width = 2 * $padding + 2 * $scale;
        $height = 2 * $padding + 2 * $scale * $ratio;
        $mime = "image/png";

        $this->assertSame($width, $image->width());
        $this->assertSame($height, $image->height());
        $this->assertSame($mime, $image->mime);
    }


    public function testColors()
    {
        $color = "#ff0000";
        $bgColor = "#0000ff";

        $renderer = new ImageRenderer([
            'color' => $color,
            'bgColor' => $bgColor,
        ]);

        $data = new BarcodeData();
        $data->codes = [[true, false],[false, true]];

        $png = $renderer->render($data);

        // Open the image
        $manager = new ImageManager();
        $image = $manager->make($png);

        // The whole image should have either forground or background color
        // Check no other colors appear in the image
        for ($x = 0; $x < $image->width(); $x++) {
            for ($y = 0; $y < $image->height(); $y++) {
                $c = $image->pickColor($x, $y, 'hex');
                $this->assertTrue(
                    in_array($c, [$color, $bgColor]),
                    "Unexpected color $c encountered. Expected only $color or $bgColor."
                );
            }
        }
    }
}
