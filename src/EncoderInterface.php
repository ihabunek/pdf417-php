<?php

namespace Bezdomni\Barcode;

interface EncoderInterface
{
    public function canEncode($char);

    public function encode($string);
}