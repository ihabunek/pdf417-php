<?php

namespace Bezdomni\Barcode;

use Bezdomni\Barcode\Encoders\ByteEncoder;
use Bezdomni\Barcode\Encoders\TextEncoder;
use Bezdomni\Barcode\Encoders\NumberEncoder;

class DataEncoder
{
    const START_CHARACTER = "11111111010101000";
    const STOP_CHARACTER  = "11111110100010100";

    public function __construct(array $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * Encodes given data into an array of PDF417 code words.
     *
     * Splits the input data into chains which can be encoded within the same
     * encoder. Then encodes each chain.
     *
     * This is not a algorithm method, it just switches to the best possible
     * mode for each separate character. Should be replaced by a smarter
     * algorithm.
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
            $chainCodes = $activeEncoder->encode($chain);
            foreach ($chainCodes as $code) {
                $codes[] = $code;
            }
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
