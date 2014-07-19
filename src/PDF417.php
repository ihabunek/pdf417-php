<?php

namespace BigFish\PDF417;

use BigFish\Barcode\Encoders\ByteEncoder;
use BigFish\Barcode\Encoders\TextEncoder;
use BigFish\Barcode\Encoders\NumberEncoder;

/**
 * Constructs a PDF417 barcodes.
 */
class PDF417
{
    const MIN_ROWS = 3;
    const MAX_ROWS = 90;

    const MIN_COLUMNS = 1;
    const MAX_COLUMNS = 30;

    const MAX_CODE_WORDS = 925;

    const START_CHARACTER = 0x1fea8;
    const STOP_CHARACTER  = 0x3fa29;

    const PADDING_CODE_WORD = 900;

    // -- Builder methods and properties ---------------------------------------

    private $columns = 6;

    private $securityLevel = 2;

    public function columns($columns)
    {
        $min = self::MIN_COLUMNS;
        $max = self::MAX_COLUMNS;

        if (!is_numeric($columns)) {
            throw new \InvalidArgumentException("Column count must be numeric. Given: $columns");
        }

        if ($columns < $min || $columns > $max) {
            throw new \InvalidArgumentException("Column count must be between $min and $max. Given: $columns");
        }

        $this->columns = intval($columns);

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

        // Arrange codewords into a rows and columns
        $grid = array_chunk($codeWords, $columns);
        $rows = count($grid);

        // Iterate over rows
        $codes = [];
        foreach ($grid as $rowNum => $row) {
            $rowCodes = [];

            // Add starting code word
            $rowCodes[] = self::START_CHARACTER;

            // Add left-side code word
            $left = $this->getLeftCodeWord($rowNum, $rows, $columns, $secLev);
            $rowCodes[] = Codes::getCodeForRow($rowNum, $left);

            // Add data code words
            foreach ($row as $word) {
                $rowCodes[] = Codes::getCodeForRow($rowNum, $word);
            }

            // Add right-side code word
            $right = $this->getRightCodeWord($rowNum, $rows, $columns, $secLev);
            $rowCodes[] = Codes::getCodeForRow($rowNum, $right);

            // Add ending code word
            $rowCodes[] = self::STOP_CHARACTER;

            $codes[] = $rowCodes;
        }

        $data = new BarcodeData();
        $data->codes = $codes;
        $data->rows = $rows;
        $data->columns = $columns;
        $data->codeWords = $codeWords;
        $data->securityLevel = $secLev;

        return $data;
    }

    /** Encodes data to a grid of codewords for constructing the barcode. */
    public function encodeData($data)
    {
        $columns = $this->columns;
        $secLev = $this->securityLevel;

        // Encode data to code words
        $encoder = new DataEncoder();
        $dataWords = $encoder->encode($data);

        // Number of code correction words
        $ecCount = $secLev << 2;
        $dataCount = count($dataWords);

        // Add padding if needed
        $padWords = $this->getPadding($dataCount, $ecCount, $columns);
        $dataWords = array_merge($dataWords, $padWords);

        // Add length specifier as the first data code word
        // Length includes the data CWs, padding CWs and the specifier itself
        $length = count($dataWords) + 1;
        array_unshift($dataWords, $length);

        // Compute error correction code words
        $reedSolomon = new ReedSolomon();
        $ecWords = $reedSolomon->compute($dataWords, $secLev);

        // Combine the code words and return
        return array_merge($dataWords, $ecWords);
    }

    // -------------------------------------------------------------------------

    private function getLeftCodeWord($rowNum, $rows, $columns, $secLev)
    {
        // Table used to encode this row
        $tableID = $rowNum % 3;

        switch($tableID) {
            case 0:
                $x = intval(($rows - 1) / 3);
                break;
            case 1:
                $x = $secLev * 3;
                $x += ($rows - 1) % 3;
                break;
            case 2:
                $x = $columns - 1;
                break;
        }

        return 30 * intval($rowNum / 3) + $x;
    }

    private function getRightCodeWord($rowNum, $rows, $columns, $secLev)
    {
        $tableID = $rowNum % 3;

        switch($tableID) {
            case 0:
                $x = $columns - 1;
                break;
            case 1:
                $x = intval(($rows - 1) / 3);
                break;
            case 2:
                $x = $secLev * 3;
                $x += ($rows - 1) % 3;
                break;
        }

        return 30 * intval($rowNum / 3) + $x;
    }

    private function getPadding($dataCount, $ecCount, $columns)
    {
        // Total number of data words and error correction words, additionally
        // reserve 1 code word for the length descriptor
        $totalCount = $dataCount + $ecCount + 1;
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
