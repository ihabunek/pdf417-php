<?php

namespace BigFish\PDF417\Tests;

use BigFish\PDF417\DataEncoder;
use BigFish\PDF417\Encoders\TextEncoder;
use BigFish\PDF417\Encoders\NumberEncoder;
use BigFish\PDF417\Encoders\ByteEncoder;
use PHPUnit\Framework\TestCase;

class DataEncoderTest extends TestCase
{
    public function testStartingSwitchCodeWordIsAddedOnlyForText()
    {
        $encoder = new DataEncoder();

        // When starting with text, the first code word does not need to be the switch
        $result = $encoder->encode("ABC123");
        $this->assertNotEquals($result[0], TextEncoder::SWITCH_CODE_WORD);
        $this->assertEquals([1, 89, 902, 1, 223], $result);

        // When starting with numbers, we do need to switch
        $result = $encoder->encode("123ABC");
        $this->assertEquals($result[0], NumberEncoder::SWITCH_CODE_WORD);
        $this->assertEquals([902, 1, 223, 900, 1, 89], $result);

        // Also with bytes
        $result = $encoder->encode("\x0B");
        $this->assertEquals($result[0], ByteEncoder::SWITCH_CODE_WORD);
        $this->assertEquals([901, 11], $result);

        // Alternate bytes switch code when number of bytes is divisble by 6
        $result = $encoder->encode("\x0B\x0B\x0B\x0B\x0B\x0B");
        $this->assertEquals($result[0], ByteEncoder::SWITCH_CODE_WORD_ALT);
        $this->assertEquals([924, 18, 455, 694, 754, 291], $result);
    }
}
