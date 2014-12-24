<?php

namespace BigFish\PDF417\Encoders;

use BigFish\PDF417\EncoderInterface;

/**
 * Converts numbers to code words.
 *
 * Can encode: digits 0-9
 * Rate: 2.9 digits per code word.
 */
class NumberEncoder implements EncoderInterface
{
    /**
     * Code word used to switch to Numeric mode.
     */
    const SWITCH_CODE_WORD = 902;

    /**
     * {@inheritdoc}
     */
    public function canEncode($char)
    {
        return is_string($char) && 1 === preg_match('/^[0-9]$/', $char);
    }

    /**
     * {@inheritdoc}
     */
    public function getSwitchCode($data)
    {
        return self::SWITCH_CODE_WORD;
    }

    /**
     *  {@inheritdoc}
     *
     * The "Numeric" mode is a conversion from base 10 to base 900.
     *
     * - numbers are taken in groups of 44 (or less)
     * - digit "1" is added to the beginning of the group (it will later be
     *   removed by the decoding procedure)
     * - base is changed from 10 to 900
     */
    public function encode($digits, $addSwitchCode)
    {
        if (!is_string($digits)) {
            $type = gettype($digits);
            throw new \InvalidArgumentException("Expected first parameter to be a string, $type given.");
        }

        if (!preg_match('/^[0-9]+$/', $digits)) {
            throw new \InvalidArgumentException("First parameter contains non-numeric characters.");
        }

        // Count the number of 44 character chunks
        $digitCount = strlen($digits);
        $chunkCount = ceil($digitCount / 44);

        $codeWords = [];

        if ($addSwitchCode) {
            $codeWords[] = self::SWITCH_CODE_WORD;
        }

        // Encode in chunks of 44 digits
        for ($i = 0; $i < $chunkCount; $i++) {
            $chunk = substr($digits, $i * 44, 44);

            $cws = $this->encodeChunk($chunk);

            // Avoid using array_merge
            foreach ($cws as $cw) {
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

            array_unshift($cws, (integer) $cw);
        }

        return $cws;
    }
}
