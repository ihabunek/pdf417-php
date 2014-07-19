<?php

namespace BigFish\PDF417;

/**
 * Container class which holds all data needed to render a PDF417 bar code.
 */
class BarcodeData
{
    public $codeWords;
    public $columns;
    public $rows;
    public $codes;
    public $securityLevel;

    public function getPixelGrid()
    {
        $pixelGrid = [];
        foreach ($this->codes as $row) {
            $pixelRow = [];
            foreach ($row as $value) {
                $bin = decbin($value);
                $len = strlen($bin);
                for ($i = 0; $i < $len; $i++) {
                    $pixelRow[] = (boolean) $bin[$i];
                }
            }
            $pixelGrid[] = $pixelRow;
        }

        return $pixelGrid;
    }
}
