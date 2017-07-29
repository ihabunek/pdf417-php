<?php

namespace BigFish\PDF417\Tests;

use BigFish\PDF417\Codes;
use PHPUnit\Framework\TestCase;

class CodesTest extends TestCase
{
    public function testGetCode()
    {
        $this->assertSame(0x1d5c0, Codes::getCode(0, 0));
        $this->assertSame(0x1f560, Codes::getCode(1, 0));
        $this->assertSame(0x1abe0, Codes::getCode(2, 0));

        $this->assertSame(0x1bef4, Codes::getCode(0, 928));
        $this->assertSame(0x13f26, Codes::getCode(1, 928));
        $this->assertSame(0x1c7ea, Codes::getCode(2, 928));

    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid code word [0][929]
     *
     * @return [type] [description]
     */
    public function testInvalidCode()
    {
        $this->assertSame(0x1abe0, Codes::getCode(0, 929));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid code word [3][0]
     */
    public function testInvalidTable()
    {
        $this->assertSame(0x1abe0, Codes::getCode(3, 0));
    }
}
