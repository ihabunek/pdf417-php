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
}
