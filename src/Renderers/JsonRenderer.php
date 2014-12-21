<?php

namespace BigFish\PDF417\Renderers;

use BigFish\PDF417\BarcodeData;
use BigFish\PDF417\RendererInterface;

class JsonRenderer implements RendererInterface
{
    public function getContentType()
    {
        return "application/json";
    }

    public function render(BarcodeData $data)
    {
        // Function which translates true/false to 1/0
        $fmap = function ($element) {
            return $element ? 1 : 0;
        };

        // Apply function to the pixel map
        $return = [];
        foreach ($data->getPixelGrid() as $row) {
            $return[] = array_map($fmap, $row);
        }

        $json = json_encode($return);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $msg = json_last_error_msg();
            throw new \Exception("Failed encoding JSON: $msg");
        }

        return $json;
    }
}
