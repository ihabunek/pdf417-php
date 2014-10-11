<?php

namespace BigFish\PDF417\Renderers;

use BigFish\PDF417\BarcodeData;
use BigFish\PDF417\RendererInterface;

use Intervention\Image\Image;

class ImageRenderer implements RendererInterface
{
    /** Supported image formats and corresponding MIME types. */
    private $formats = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ];

    private $options = [
        'format' => 'png',
        'quality' => 90,
        'scale' => 3,
        'ratio' => 3,
        'padding' => 20,
        'color' => "#000",
        'bgColor' => "#fff",
    ];

    public function __construct(array $options)
    {
        // Merge given options with defaults
        foreach ($options as $key => $value) {
            if (isset($this->options[$key])) {
                $this->options[$key] = $value;
            }
        }

        // Validate options
        $format = $this->options['format'];
        if (!isset($this->formats[$format])) {
            throw new \InvalidArgumentException("Invalid image format: \"$format\".");
        }
    }

    public function getContentType()
    {
        $format = $this->options['format'];
        return $this->formats[$format];
    }

    public function render(BarcodeData $data)
    {
        $pixelGrid = $data->getPixelGrid();
        $height = count($pixelGrid);
        $width = count($pixelGrid[0]);

        $options = $this->options;

        $img = Image::canvas($width, $height, $options['bgColor']);

        // Render the barcode
        foreach ($pixelGrid as $y => $row) {
            foreach ($row as $x => $value) {
                if ($value) {
                    $img->pixel($options['color'], $x, $y);
                }
            }
        }

        // Apply scaling & aspect ratio
        $width *= $options['scale'];
        $height *= $options['scale'] * $options['ratio'];
        $img->resize($width, $height);

        // Add padding
        $width += 2 * $options['padding'];
        $height += 2 * $options['padding'];
        $img->resizeCanvas($width, $height, 'center', false, '#fff');

        return $img->encode($options['format']);
    }
}
