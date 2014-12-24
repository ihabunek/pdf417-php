<?php

namespace BigFish\PDF417\Tests\Encoders;

use BigFish\PDF417\Encoders\TextEncoder;

/**
 * @group encoders
 */
class TextEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testCanEncode()
    {
        $te = new TextEncoder();

        for ($ord = ord(' '); $ord < ord('Z'); $ord++) {
            $chr = chr($ord);

            // Skip unencodable characters
            if ($chr == '"') {
                continue;
            }

            $this->assertTrue(
                $te->canEncode($chr),
                "Unable to encode: " . var_export($chr, true)
            );
        }

        // Cannot encode empty strings, non-strings and multi digit strings
        $this->assertFalse($te->canEncode(""));
        $this->assertFalse($te->canEncode("foo"));
    }

    public function testGetSwitchCode()
    {
        $te = new TextEncoder();
        $sw = TextEncoder::SWITCH_CODE_WORD;

        $this->assertSame($sw, $te->getSwitchCode("123"));
        $this->assertSame($sw, $te->getSwitchCode("foo"));
        $this->assertSame($sw, $te->getSwitchCode([]));
    }

    public function testEncode1()
    {
        $te = new TextEncoder();

        $actual = $te->encode("Super !", true);
        $expected = [900, 567, 615, 137, 808, 760];
        $this->assertSame($expected, $actual);

        $actual = $te->encode("Super !", false);
        $expected = [567, 615, 137, 808, 760];
        $this->assertSame($expected, $actual);
    }

    public function testEncode2()
    {
        $te = new TextEncoder();

        $actual = $te->encode("Super ", true);
        $expected = [900, 567, 615, 137, 809];
        $this->assertSame($expected, $actual);

        $actual = $te->encode("Super ", false);
        $expected = [567, 615, 137, 809];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected first parameter to be a string, array given.
     */
    public function testInvalidInput()
    {
        $te = new TextEncoder();
        $te->encode([], true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage  Cannot encode character ["] (ASCII 34).
     */
    public function testInvalidCharacter()
    {
        $te = new TextEncoder();
        $te->encode('"', true);
    }
}
