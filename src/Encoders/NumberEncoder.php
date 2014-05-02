<?php

namespace Bezdomni\Barcode\Encoders;

use Bezdomni\Barcode\EncoderInterface;

/**
 * Converts numbers to code words.
 *
 * Can encode: digits 0-9
 * Rate: 2.9 digits per code word.
 */
class NumberEncoder implements EncoderInterface
{
    public function canEncode($char)
    {
        return preg_match('/^[0-9]$/', $char);
    }

    /**
     * The "Numeric" mode is a conversion from base 10 to base 900.
     *
     * - numbers are taken in groups of 44 (or less)
     * - digit "1" is added to the beginning of the group (it will later be
     *   removed by the decoding procedure)
     * - base is changed from 10 to 900
     */
    public function encode($digits)
    {
        if (!preg_match('/^[0-9]+$/', $digits)) {
            throw new \InvalidArgumentException("Invalid input given.");
        }

        // Count the number of 44 character chunks
        $digitCount = strlen($digits);
        $chunkCount = ceil($digitCount / 44);

        // Encode in chunks of 44 digits
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunk = substr($digits, $i * 44, 44);

            $cws = $this->encodeChunk($chunk);

            // Avoid using array_merge
            foreach($cws as $cw) {
                $codeWords[] = $cw;
            }
        }

        return $codeWords;
    }

    private function encodeChunk($chunk)
    {
        $chunk = "1" . $chunk;

        $cws = [];
        while(bccomp($chunk, 0) > 0) {
            $cw = bcmod($chunk, 900);
            $chunk = bcdiv($chunk, 900, 0); // Integer division

            array_unshift($cws, $cw);
        }

        return $cws;
    }
}
