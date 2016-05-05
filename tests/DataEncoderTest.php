<?php

namespace BigFish\PDF417\Tests;

use BigFish\PDF417\DataEncoder;
use BigFish\PDF417\Encoders\TextEncoder;
use BigFish\PDF417\Encoders\NumberEncoder;
use BigFish\PDF417\Encoders\ByteEncoder;

class DataEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testStartingSwitchCodeWordIsAddedOnlyForText()
    {
        $encoder = new DataEncoder();

        // When starting with text, the first code word does not need to be the switch
        $cw1 = $encoder->encode("ABC123")[0];
        $this->assertNotEquals($cw1, TextEncoder::SWITCH_CODE_WORD);

        // When starting with numbers, we do need to switch
        $cw1 = $encoder->encode("123ABC")[0];
        $this->assertEquals($cw1, NumberEncoder::SWITCH_CODE_WORD);

        // Also with bytes
        $cw1 = $encoder->encode("\x0B")[0];
        $this->assertEquals($cw1, ByteEncoder::SWITCH_CODE_WORD);

        // Alternate bytes switch code when number of bytes is divisble by 6
        $cw1 = $encoder->encode("\x0B\x0B\x0B\x0B\x0B\x0B")[0];
        $this->assertEquals($cw1, ByteEncoder::SWITCH_CODE_WORD_ALT);
    }
}
