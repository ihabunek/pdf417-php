<?php

namespace BigFish\PDF417\Encoders;

use BigFish\PDF417\EncoderInterface;

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
    /**
     * Code word used to switch to Byte mode.
     */
    const SWITCH_CODE_WORD = 901;

    /**
     * Alternate code word used to switch to Byte mode; used when number of
     * bytes to encode is divisible by 6.
     */
    const SWITCH_CODE_WORD_ALT = 924;

    /**
     * {@inheritdoc}
     */
    public function canEncode($char)
    {
        // Can encode any character
        return is_string($char) && strlen($char) === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getSwitchCode($data)
    {
        return (strlen($data) % 6 === 0) ? self::SWITCH_CODE_WORD_ALT : self::SWITCH_CODE_WORD;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($bytes, $addSwitchCode)
    {
        if (!is_string($bytes)) {
            $type = gettype($bytes);
            throw new \InvalidArgumentException("Expected first parameter to be a string, $type given.");
        }

        // Count the number of 6 character chunks
        $byteCount = strlen($bytes);
        $chunkCount = ceil($byteCount / 6);

        $codeWords = [];

        if ($addSwitchCode) {
            $codeWords[] = $this->getSwitchCode($bytes);
        }

        // Encode in chunks of 6 bytes
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunk = substr($bytes, $i * 6, 6);

            if (strlen($chunk) === 6) {
                $cws = $this->encodeChunk($chunk);
            } else {
                $cws = $this->encodeIncompleteChunk($chunk);
            }

            // Avoid using array_merge
            foreach ($cws as $cw) {
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

            array_unshift($cws, (integer) $cw);
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
            $cws[] = ord($chunk[$i]);
        }

        return $cws;
    }
}
