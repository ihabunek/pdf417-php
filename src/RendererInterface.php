<?php

namespace BigFish\PDF417;

interface RendererInterface
{
    public function render(BarcodeData $data);
}
