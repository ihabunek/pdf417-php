<?php

namespace BigFish\PDF417;

interface EncoderInterface
{
    public function canEncode($char);

    public function encode($string, $addSwitchCode);
}