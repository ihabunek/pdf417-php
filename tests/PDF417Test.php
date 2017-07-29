<?php

namespace BigFish\PDF417\Tests;

use BigFish\PDF417\PDF417;
use BigFish\PDF417\BarcodeData;
use PHPUnit\Framework\TestCase;

class PDF417Test extends TestCase
{
    public function testDefaultsAndAccessors()
    {
        $cols = 20;
        $secLev = 6;

        $pdf = new PDF417();
        $this->assertSame($pdf::DEFAULT_COLUMNS, $pdf->getColumns());
        $this->assertSame($pdf::DEFAULT_SECURITY_LEVEL, $pdf->getSecurityLevel());

        $pdf->setColumns($cols);
        $this->assertSame($cols, $pdf->getColumns());

        $pdf->setSecurityLevel($secLev);
        $this->assertSame($secLev, $pdf->getSecurityLevel());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Column count must be numeric. Given: foo
     */
    public function testInvalidColumns1()
    {
        $pdf = new PDF417();
        $pdf->setColumns("foo");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Column count must be between 1 and 30. Given: 1000
     */
    public function testInvalidColumns2()
    {
        $pdf = new PDF417();
        $pdf->setColumns(1000);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Security level must be numeric. Given: foo
     */
    public function testInvalidSecurityLevel1()
    {
        $pdf = new PDF417();
        $pdf->setSecurityLevel("foo");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Security level must be between 0 and 8. Given: 1000
     */
    public function testInvalidSecurityLevel2()
    {
        $pdf = new PDF417();
        $pdf->setSecurityLevel(1000);
    }

    /** An end-to-end test. */
    public function testEncode()
    {
        $data = "HRVHUB30\nHRK\n000000010000000\nIvan Habunek\nSavska cesta 13\n10000 Zagreb\nBig Fish Software d.o.o.\nSavska cesta 13\n10000 Zagreb\nHR6623400091110651272\n00\nHR123456\nANTS\nRazvoj paketa za bar kodove\n";

        $pdf = new PDF417();
        $barcodeData = $pdf->encode($data);

        $this->assertInstanceOf("BigFish\\PDF417\\BarcodeData", $barcodeData);

        $expectedCWs = [
            142, 227, 637, 601, 902, 130, 900, 865, 479, 227, 328, 765, 902, 1,
            624, 142, 113, 522, 200, 900, 865, 479, 267, 630, 416, 868, 237, 1,
            613, 130, 865, 479, 567, 21, 550, 26, 64, 559, 26, 902, 113, 900,
            865, 479, 902, 122, 200, 900, 805, 810, 197, 121, 865, 479, 57, 246,
            808, 845, 818, 547, 808, 858, 824, 169, 660, 514, 783, 857, 824,
            857, 824, 857, 765, 888, 810, 648, 300, 782, 138, 570, 809, 902,
            113, 900, 865, 479, 902, 122, 200, 900, 805, 810, 197, 121, 865,
            479, 227, 902, 31, 251, 786, 557, 565, 1, 372, 900, 865, 479, 902,
            100, 900, 865, 479, 227, 902, 1, 348, 256, 900, 865, 479, 13, 588,
            865, 479, 537, 25, 644, 296, 450, 304, 570, 805, 26, 30, 536, 314,
            104, 634, 865, 479, 900, 713, 846, 93, 59, 313, 515, 294, 844];

        $this->assertSame($expectedCWs, $barcodeData->codeWords);

        $expectedCodes = [
            [130728, 108640, 82050, 93980, 67848, 99590, 81384, 82192, 128318, 260649],
            [130728, 128280, 97968, 81084, 101252, 127694, 75652, 113982, 125456, 260649],
            [130728, 86496, 69396, 120312, 66846, 104188, 106814, 96800, 108792, 260649],
            [130728, 107712, 93248, 68708, 73160, 96008, 102812, 67872, 119520, 260649],
            [130728, 110096, 111076, 97694, 104224, 129720, 129938, 119200, 110088, 260649],
            [130728, 125892, 66382, 67960, 113798, 88188, 71822, 129766, 125890, 260649],
            [130728, 108478, 108348, 117798, 120638, 81384, 82784, 68708, 85560, 260649],
            [130728, 125248, 81084, 101252, 97944, 128088, 82408, 97968, 129628, 260649],
            [130728, 129634, 73296, 81608, 103294, 119034, 66382, 67960, 85116, 260649],
            [130728, 83740, 119582, 116920, 66832, 116560, 99984, 69870, 107452, 260649],
            [130728, 108304, 99048, 81342, 114996, 125036, 69754, 115920, 108296, 260649],
            [130728, 129588, 73360, 97906, 122786, 97906, 122786, 97906, 83704, 260649],
            [130728, 106648, 122472, 116534, 66820, 101838, 116814, 71784, 106672, 260649],
            [130728, 125064, 119170, 124520, 115062, 97944, 85936, 97968, 125060, 260649],
            [130728, 82206, 66382, 67960, 69396, 82296, 93944, 102290, 82366, 260649],
            [130728, 76992, 66880, 66820, 112224, 82694, 73160, 96008, 104160, 260649],
            [130728, 83842, 127694, 97944, 120624, 117710, 101498, 127784, 107502, 260649],
            [130728, 124392, 113804, 120312, 95856, 102290, 66382, 67960, 127734, 260649],
            [130728, 111632, 81384, 106848, 68708, 73160, 96008, 93980, 111648, 260649],
            [130728, 82924, 97944, 129720, 123984, 77572, 97968, 81084, 124968, 260649],
            [130728, 74992, 67960, 110072, 122414, 66382, 67960, 114076, 103036, 260649],
            [130728, 75288, 110200, 72624, 116828, 126684, 90896, 94672, 112440, 260649],
            [130728, 124176, 123062, 120324, 110144, 79824, 103300, 119772, 124168, 260649],
            [130728, 126450, 66718, 66382, 67960, 102290, 97740, 122772, 117236, 260649],
            [130728, 111456, 83468, 108060, 74136, 117830, 74048, 69102, 102752, 260649]
        ];

        $this->assertSame($expectedCodes, $barcodeData->codes);
    }
}
