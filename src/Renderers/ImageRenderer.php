<?php

namespace BigFish\PDF417\Renderers;

use BigFish\PDF417\BarcodeData;
use BigFish\PDF417\RendererInterface;

use Intervention\Image\ImageManager;
use Intervention\Image\Gd\Color;

class ImageRenderer extends AbstractRenderer
{
    /** Supported image formats and corresponding MIME types. */
    protected $formats = [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'tif' => 'image/tiff',
        'bmp' => 'image/bmp',
        'data-url' => null,
    ];

    protected $options = [
        'format' => 'png',
        'quality' => 90,
        'scale' => 3,
        'ratio' => 3,
        'padding' => 20,
        'color' => "#000000",
        'bgColor' => "#ffffff",
    ];

    /**
     * {@inheritdoc}
     */
    public function validateOptions()
    {
        $errors = [];

        $format = $this->options['format'];
        if (!array_key_exists($format, $this->formats)) {
            $formats = implode(", ", array_keys($this->formats));
            $errors[] = "Invalid option \"format\": \"$format\". Expected one of: $formats.";
        }

        $scale = $this->options['scale'];
        if (!is_numeric($scale) || $scale < 1 || $scale > 20) {
            $errors[] = "Invalid option \"scale\": \"$scale\". Expected an integer between 1 and 20.";
        }

        $ratio = $this->options['ratio'];
        if (!is_numeric($ratio) || $ratio < 1 || $ratio > 10) {
            $errors[] = "Invalid option \"ratio\": \"$ratio\". Expected an integer between 1 and 10.";
        }

        $padding = $this->options['padding'];
        if (!is_numeric($padding) || $padding < 0 || $padding > 50) {
            $errors[] = "Invalid option \"padding\": \"$padding\". Expected an integer between 0 and 50.";
        }

        $quality = $this->options['quality'];
        if (!is_numeric($quality) || $quality < 0 || $quality > 100) {
            $errors[] = "Invalid option \"quality\": \"$quality\". Expected an integer between 0 and 50.";
        }

        // Check colors by trying to parse them
        $color = $this->options['color'];
        $bgColor = $this->options['bgColor'];

        $gdColor = new Color();

        try {
            $gdColor->parse($color);
        } catch (\Exception $ex) {
            $errors[] = "Invalid option \"color\": \"$color\". Supported color formats: \"#000000\", \"rgb(0,0,0)\", or \"rgba(0,0,0,0)\"";
        }

        try {
            $gdColor->parse($bgColor);
        } catch (\Exception $ex) {
            $errors[] = "Invalid option \"bgColor\": \"$bgColor\". Supported color formats: \"#000000\", \"rgb(0,0,0)\", or \"rgba(0,0,0,0)\"";
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        $format = $this->options['format'];
        return $this->formats[$format];
    }

    /**
     * {@inheritdoc}
     *
     * @return \Intervention\Image\Image
     */
    public function render(BarcodeData $data)
    {
        $pixelGrid = $data->getPixelGrid();
        $height = count($pixelGrid);
        $width = count($pixelGrid[0]);

        // Extract options
        $bgColor = $this->options['bgColor'];
        $color = $this->options['color'];
        $format = $this->options['format'];
        $padding = $this->options['padding'];
        $quality = $this->options['quality'];
        $ratio = $this->options['ratio'];
        $scale = $this->options['scale'];

        // Create a new image
        $manager = new ImageManager();
        $img = $manager->canvas($width, $height, $bgColor);

        // Render the barcode
        foreach ($pixelGrid as $y => $row) {
            foreach ($row as $x => $value) {
                if ($value) {
                    $img->pixel($color, $x, $y);
                }
            }
        }

        // Apply scaling & aspect ratio
        $width *= $scale;
        $height *= $scale * $ratio;
        $img->resize($width, $height);

        // Add padding
        $width += 2 * $padding;
        $height += 2 * $padding;
        $img->resizeCanvas($width, $height, 'center', false, $bgColor);

        return $img->encode($format, $quality);
    }
}
