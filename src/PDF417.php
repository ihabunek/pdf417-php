<?php

namespace Bezdomni\Barcode;

use Bezdomni\Barcode\Encoders\ByteEncoder;
use Bezdomni\Barcode\Encoders\TextEncoder;
use Bezdomni\Barcode\Encoders\NumberEncoder;

/**
 * Constructs a PDF417 barcodes.
 */
class PDF417
{
    const START_CHARACTER = 0x1fea8;
    const STOP_CHARACTER  = 0x3fa29;

    const PADDING_CODE_WORD = 900;

    // -- Builder methods and properties ---------------------------------------

    private $columns = 6;

    private $securityLevel = 2;

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function securityLevel($securityLevel)
    {
        $this->securityLevel = $securityLevel;

        return $this;
    }

    // -------------------------------------------------------------------------

    public function encode($data)
    {
        $codeWords = $this->encodeData($data);

        $secLev = $this->securityLevel;
        $columns = $this->columns;
        $rows = count($codeWords) / $columns;

        $codes = [];

        foreach ($codeWords as $rowNum => $row) {
            $rowCodes = [];

            // Add starting delimiter
            $rowCodes[] = self::START_CHARACTER;

            // Add left-side code word
            $left = $this->getLeftCodeWord($rowNum, $rows, $columns, $secLev);
            $rowCodes[] = Codes::getCodeForRow($rowNum, $left);

            foreach ($row as $word) {
                $rowCodes[] = Codes::getCodeForRow($rowNum, $word);
            }

            // Add right-side code word
            $right = $this->getRightCodeWord($rowNum, $rows, $columns, $secLev);
            $rowCodes[] = Codes::getCodeForRow($rowNum, $right);

            // Add ending delimiter
            $rowCodes[] = self::STOP_CHARACTER;

            $codes[] = $rowCodes;
        }

        return $codes;
    }

    /** Encodes data to a grid of codewords for constructing the barcode. */
    public function encodeData($data)
    {
        $columns = $this->columns;
        $secLev = $this->securityLevel;

        $encoder = new DataEncoder();
        $reedSolomon = new ReedSolomon();

        $dataWords = $encoder->encode($data);
        $rsWords = $reedSolomon->compute($dataWords, 2);
        $padWords = $this->getPadding($dataWords, $rsWords, $columns);

        $codeWords = array_merge($rsWords, $padWords, $dataWords);

        // Arrange codewords into a rows and columns
        $codeWords = array_reverse($codeWords);
        return array_chunk($codeWords, $columns);
    }

    // -------------------------------------------------------------------------

    private function getLeftCodeWord($rowNum, $rows, $columns, $secLev)
    {
        // Table used to encode this row
        $tableID = $rowNum % 3 + 1;

        switch($tableID) {
            case 1:
                $x = intval(($rows - 1) / 3);
                break;
            case 2:
                $x = $secLev * 3;
                $x += ($rows - 1) % 3;
                break;
            case 3:
                $x = $columns - 1;
                break;
        }

        $retval = (int) ($rowNum / 3);
        return $retval * 30 + $x;
    }

    private function getRightCodeWord($rowNum, $rows, $columns, $secLev)
    {
        $tableID = $rowNum % 3 + 1;

        switch($tableID) {
            case 1:
                $x = $columns - 1;
                break;
            case 2:
                $x = intval(($rows - 1) / 3);
                break;
            case 3:
                $x = $secLev * 3;
                $x += ($rows - 1) % 3;
                break;
        }

        $retval = (int) ($rowNum / 3);
        return $retval * 30 + $x;
    }

    private function getPadding($dataWords, $rsWords, $columns)
    {
        $totalCount = count($dataWords) + count($rsWords);
        $mod = $totalCount % $columns;

        if ($mod > 0) {
            $padCount = $columns - $mod;
            $padding = array_fill(0, $padCount, self::PADDING_CODE_WORD);
        } else {
            $padding = [];
        }

        return $padding;
    }
}
