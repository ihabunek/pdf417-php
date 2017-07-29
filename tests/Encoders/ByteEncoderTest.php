<?php

namespace BigFish\PDF417\Tests\Encoders;

use BigFish\PDF417\Encoders\ByteEncoder;
use PHPUnit\Framework\TestCase;

/**
 * @group encoders
 */
class ByteEncoderTest extends TestCase
{
    public function testCanEncode()
    {
        $be = new ByteEncoder();

        // Can encode any single char
        for ($ord = 0; $ord < 256; $ord++) {
            $chr = chr($ord);
            $this->assertTrue(
                $be->canEncode($chr),
                "Cannot encode character \"$chr\" ($ord)."
            );
        }

        // Cannot encode empty strings, non-strings and multi digit strings
        $this->assertFalse($be->canEncode(""));
        $this->assertFalse($be->canEncode("foo"));
    }

    public function testGetSwitchCode()
    {
        $be = new ByteEncoder();

        $sw1 = ByteEncoder::SWITCH_CODE_WORD;
        $sw2 = ByteEncoder::SWITCH_CODE_WORD_ALT;

        $this->assertSame($sw1, $be->getSwitchCode("1"));
        $this->assertSame($sw1, $be->getSwitchCode("12"));
        $this->assertSame($sw1, $be->getSwitchCode("123"));
        $this->assertSame($sw1, $be->getSwitchCode("1234"));
        $this->assertSame($sw1, $be->getSwitchCode("12345"));
        $this->assertSame($sw2, $be->getSwitchCode("123456"));
        $this->assertSame($sw1, $be->getSwitchCode("1234567"));
        $this->assertSame($sw1, $be->getSwitchCode("12345678"));
        $this->assertSame($sw1, $be->getSwitchCode("123456789"));
        $this->assertSame($sw1, $be->getSwitchCode("1234567890"));
        $this->assertSame($sw1, $be->getSwitchCode("12345678901"));
        $this->assertSame($sw2, $be->getSwitchCode("123456789012"));
        $this->assertSame($sw1, $be->getSwitchCode("1234567890123"));
    }

    public function testEncode1()
    {
        $be = new ByteEncoder();

        $actual = $be->encode("alcool", true);
        $expected = [924, 163, 238, 432, 766, 244];
        $this->assertSame($expected, $actual);

        $actual = $be->encode("alcool", false);
        $expected = [163, 238, 432, 766, 244];
        $this->assertSame($expected, $actual);
    }

    public function testEncode2()
    {
        $be = new ByteEncoder();

        $actual = $be->encode("alcoolique", true);
        $expected = [901, 163, 238, 432, 766, 244, 105, 113, 117, 101];
        $this->assertSame($expected, $actual);

        $actual = $be->encode("alcoolique", false);
        $expected = [163, 238, 432, 766, 244, 105, 113, 117, 101];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected first parameter to be a string, array given.
     */
    public function testInvalidInput()
    {
        $be = new ByteEncoder();
        $be->encode([], true);
    }
}
