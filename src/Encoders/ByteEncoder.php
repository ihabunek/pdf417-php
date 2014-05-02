<?php

namespace Bezdomni\Barcode\Encoders;

use Bezdomni\Barcode\EncoderInterface;

/**
 * Converts a byte array to code words.
 *
 * Can encode: ASCII 0-255
 * Rate: 1.2 bytes per code word.
 *
 * Encoding process converts chunks of 6 bytes to 5 code words in base 900.
 */
class ByteEncoder implements EncoderInterface
{
    public function canEncode($char)
    {
        return (is_string($char) && strlen($char) == 1);
    }

    public function encode($bytes)
    {
        if (!is_string($bytes)) {
            throw new \InvalidArgumentException("Given input is not a string.");
        }

        // Count the number of 6 character chunks
        $byteCount = strlen($bytes);
        $chunkCount = ceil($byteCount / 6);

        // If the number of bytes is a multiple of 6, code word 924 is used to
        // switch to byte mode, otherwise code word 901 is used for switching.
        $switch = ($byteCount % 6 === 0) ? 924 : 901;

        // Start with the switch
        $codeWords = [$switch];

        // Encode in chunks of 6 bytes
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunk = substr($bytes, $i * 6, 6);

            if (strlen($chunk) === 6) {
                $cws = $this->encodeChunk($chunk);
            } else {
                $cws = $this->encodeIncompleteChunk($chunk);
            }

            // Avoid using array_merge
            foreach($cws as $cw) {
                $codeWords[] = $cw;
            }
        }

        return $codeWords;
    }

    /**
     * Takes a chunk of 6 bytes and encodes it to 5 code words.
     *
     * The calculation consists of switching from base 256 to base 900.
     *
     * BC math is used to perform large number arithmetic.
     */
    private function encodeChunk($chunk)
    {
        $sum = "0";
        for ($i = 0; $i < 6; $i++) {
            $char = substr($chunk, 5 - $i, 1);
            $val = bcmul(bcpow(256, $i), ord($char));
            $sum = bcadd($sum, $val);
        }

        $cws = [];
        while(bccomp($sum, 0) > 0) {
            $cw = bcmod($sum, 900);
            $sum = bcdiv($sum, 900, 0); // Integer division

            array_unshift($cws, $cw);
        }

        return $cws;
    }

    /**
     * Takes a chunk of less than 6 bytes and encodes it the same number of code
     * words as the length of the chunk.
     *
     * Base remains unchanged.
     */
    private function encodeIncompleteChunk($chunk)
    {
        $cws = [];

        for ($i = 0; $i < strlen($chunk); $i++) {
            $cws[] = ord($chunk);
        }

        return $cws;
    }
}
