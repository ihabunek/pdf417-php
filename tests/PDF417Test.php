<?php

namespace BigFish\PDF417\Tests;

use BigFish\PDF417\PDF417;
use BigFish\PDF417\BarcodeData;

class PDF417Test extends \PHPUnit_Framework_TestCase
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
        $data = 'HRVHUB30\nHRK\n000000010000000\nIvan Habunek\nSavska cesta 13\n10000 Zagreb\nBig Fish Software d.o.o.\nSavska cesta 13\n10000 Zagreb\nHR6623400091110651272\n00\nHR123456\nANTS\nRazvoj paketa za bar kodove\n';

        $pdf = new PDF417();
        $barcodeData = $pdf->encode($data);

        $this->assertInstanceOf("BigFish\\PDF417\\BarcodeData", $barcodeData);

        $expectedCWs = [166, 227, 637, 601, 902, 130, 900, 865, 179, 823, 868,
            227, 328, 755, 897, 419, 902, 1, 624, 142, 113, 522, 200, 900, 865,
            179, 823, 868, 267, 630, 416, 868, 237, 1, 613, 130, 865, 179, 823,
            868, 567, 21, 550, 26, 64, 559, 26, 902, 113, 900, 865, 179, 823,
            902, 122, 200, 900, 805, 810, 197, 121, 865, 179, 823, 868, 57, 246,
            808, 845, 818, 547, 808, 858, 824, 169, 660, 514, 783, 857, 824,
            857, 824, 857, 755, 897, 418, 858, 810, 648, 300, 782, 138, 570,
            809, 902, 113, 900, 865, 179, 823, 902, 122, 200, 900, 805, 810,
            197, 121, 865, 179, 823, 868, 227, 902, 31, 251, 786, 557, 565,
            1, 372, 900, 865, 179, 823, 902, 100, 900, 865, 179, 823, 868, 227,
            902, 1, 348, 256, 900, 865, 179, 823, 868, 13, 588, 865, 179, 823,
            868, 537, 25, 644, 296, 450, 304, 570, 805, 26, 30, 536, 314, 104,
            634, 865, 179, 823, 900, 226, 356, 899, 554, 115, 592, 152, 776];

        $this->assertSame($expectedCWs, $barcodeData->codeWords);

        $expectedCodes = [
            [130728, 86080, 104312, 93980, 67848, 99590, 81384, 82192, 128318, 260649],
            [130728, 129678, 97968, 81084, 106946, 123314, 97694, 127694, 128268, 260649],
            [130728, 86496, 78862, 97398, 118738, 78238, 69396, 120312, 119934, 260649],
            [130728, 119408, 116358, 82050, 82784, 100440, 93248, 68708, 119520, 260649],
            [130728, 120588, 81084, 106946, 123314, 97694, 93168, 115762, 120582, 260649],
            [130728, 125892, 78268, 67404, 74360, 120312, 79910, 103920, 120784, 260649],
            [130728, 85820, 73160, 121440, 99970, 73156, 78032, 89536, 85560, 260649],
            [130728, 128176, 113404, 120324, 90012, 78588, 120324, 97944, 125216, 260649],
            [130728, 129634, 106814, 102290, 66382, 111646, 122788, 69396, 85054, 260649],
            [130728, 83900, 106974, 93248, 68708, 66880, 66820, 112224, 107452, 260649],
            [130728, 119692, 125088, 81084, 106946, 123314, 97694, 89904, 119686, 260649],
            [130728, 129588, 129850, 106348, 98038, 81644, 71836, 106348, 107390, 260649],
            [130728, 118862, 66640, 66720, 104252, 118408, 122060, 101430, 106672, 260649],
            [130728, 128070, 72894, 114996, 72894, 114996, 72894, 105970, 125058, 260649],
            [130728, 82206, 118738, 95164, 122762, 81608, 68764, 70716, 82108, 260649],
            [130728, 104048, 71784, 82080, 94672, 66824, 81384, 82784, 104160, 260649],
            [130728, 83928, 97968, 81084, 106946, 123314, 97944, 128088, 83916, 260649],
            [130728, 124392, 93944, 102290, 73296, 81608, 103294, 119034, 124388, 260649],
            [130728, 121356, 73160, 121440, 99970, 73156, 93980, 81384, 111648, 260649],
            [130728, 82918, 120624, 117710, 101498, 127784, 127778, 129720, 128022, 260649],
            [130728, 74992, 95856, 102290, 66382, 111646, 122788, 69396, 93424, 260649],
            [130728, 93720, 106848, 68708, 73160, 121440, 99970, 73156, 112440, 260649],
            [130728, 127628, 127694, 97944, 129720, 123984, 77572, 97968, 127622, 260649],
            [130728, 126450, 66382, 111646, 122788, 67404, 110072, 122414, 121844, 260649],
            [130728, 102704, 73160, 121440, 99970, 73156, 78210, 110200, 102752, 260649],
            [130728, 104368, 78322, 121744, 116762, 103328, 124520, 123062, 77600, 260649],
            [130728, 74620, 129766, 128450, 97072, 116210, 125172, 66718, 92028, 260649],
            [130728, 74638, 73160, 121440, 99970, 68708, 112542, 101056, 102878, 260649],
            [130728, 127576, 81630, 129954, 85912, 113640, 107468, 128954, 126368, 260649],
        ];

        $this->assertSame($expectedCodes, $barcodeData->codes);
    }
}
