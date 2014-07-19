<?php

namespace BigFish\PDF417\Renderers;

use BigFish\PDF417\BarcodeData;

use Intervention\Image\Image;

class ImageRenderer
{
    private $format = 'png';

    private $scale = 3;

    private $ratio = 3;

    private $padding = 20;

    private $bgColor = "#fff";

    private $color = "#000";

    public function getContentType()
    {
        return "image/" . $this->format;
    }

    public function render(BarcodeData $data)
    {
        $pixelGrid = $data->getPixelGrid();
        $height = count($pixelGrid);
        $width = count($pixelGrid[0]);

        $img = Image::canvas($width, $height, $this->bgColor);

        // Render the barcode
        foreach ($pixelGrid as $y => $row) {
            foreach ($row as $x => $value) {
                if ($value) {
                    $img->pixel($this->color, $x, $y);
                }
            }
        }

        // Apply scaling & aspect ratio
        $width *= $this->scale;
        $height *= $this->scale * $this->ratio;
        $img->resize($width, $height);

        // Add padding
        $width += 2 * $this->padding;
        $height += 2 * $this->padding;
        $img->resizeCanvas($width, $height, 'center', false, '#fff');

        return $img->encode($this->format);
    }
}
