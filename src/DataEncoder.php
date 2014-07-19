<?php

namespace Bezdomni\Barcode;

use Bezdomni\Barcode\Encoders\ByteEncoder;
use Bezdomni\Barcode\Encoders\TextEncoder;
use Bezdomni\Barcode\Encoders\NumberEncoder;

/**
 * Encodes data into PDF417 code words.
 *
 * This is the top level data encoder which assigns encoding to lower level
 * (byte, number, text) encoders.
 */
class DataEncoder
{
    const START_CHARACTER = "11111111010101000";
    const STOP_CHARACTER  = "11111110100010100";

    public function __construct(array $encoders)
    {
        if (empty($encoders)) {
            throw new \Exception("No encoders given");
        }

        foreach ($encoders as $encoder) {
            if (!($encoder instanceof EncoderInterface)) {
                $class = get_class($encoder);
                throw new \Exception("Given encoder [$class] does not implement EncoderInterface.");
            }
        }

        $this->encoders = $encoders;
    }

    /**
     * Encodes given data into an array of PDF417 code words.
     *
     * Splits the input data into chains which can be encoded within the same
     * encoder. Then encodes each chain.
     *
     * Uses a pretty dumb algorithm: switches to the best possible encoder for
     * each separate character (the one that encodes it to the least bytes).
     *
     * TODO: create a better algorithm
     *
     * @param string $data The data to encode.
     */
    public function encode($data)
    {
        // Holds encoded data
        $codes = [];

        $activeEncoder = reset($this->encoders);

        // A chain of symbols which can be encoded with the active encoder
        $chain = "";

        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {

            $char = $data[$i];
            $newEncoder = $this->getEncoder($char);

            if ($activeEncoder !== $newEncoder) {
                // Encode current chain
                $this->encodeChain($chain, $activeEncoder, $codes);

                // Reset the chain, activate new encoder
                $chain = "";
                $activeEncoder = $newEncoder;
            }

            $chain .= $char;
        }

        if (!empty($chain)) {
            $this->encodeChain($chain, $activeEncoder, $codes);
        }

        return $codes;
    }

    public function getEncoder($char)
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->canEncode($char)) {
                return $encoder;
            }
        }

        $ord = ord($char);
        throw new \Exception("Cannot encode character $char (ASCII $ord)");
    }

    private function encodeChain($chain, EncoderInterface $encoder, array &$codes)
    {
        if (empty($chain)) {
            return;
        }

        $encoded = $encoder->encode($chain);
        foreach ($encoded as $code) {
            $codes[] = $code;
        }
    }
}
