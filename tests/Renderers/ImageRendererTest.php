<?php

namespace BigFish\PDF417\Tests\Renderers;

use BigFish\PDF417\BarcodeData;
use BigFish\PDF417\Renderers\ImageRenderer;

use Intervention\Image\Image;

class ImageRendererTest extends \PHPUnit_Framework_TestCase
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
        file_put_contents('png.png', $png);
        $image = Image::make($png);

        // Expected dimensions
        $width = 2 * $padding + 2 * $scale;
        $height = 2 * $padding + 2 * $scale * $ratio;
        $mime = "image/png";

        $this->assertSame($width, $image->width);
        $this->assertSame($height, $image->height);
        $this->assertSame($mime, $image->mime);
    }
}
