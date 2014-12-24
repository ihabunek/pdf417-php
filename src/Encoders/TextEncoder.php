<?php

namespace BigFish\PDF417\Encoders;

use BigFish\PDF417\EncoderInterface;

/**
 * Converts text to code words.
 *
 * Can encode: ASCII 9, 10, 13 and 32-126
 * Rate: 2 characters per code word.
 *
 * TODO: Currently doesn't support switching to a submode for just one
 * character (see T_PUN, T_UPP in
 * http://grandzebu.net/informatique/codbar-en/pdf417.htm).
 */
class TextEncoder implements EncoderInterface
{
    /**
     * Code word used to switch to Text mode.
     */
    const SWITCH_CODE_WORD = 900;

    /**
     * Since each code word consists of 2 characters, a padding value is
     * needed when encoding a single character. 29 is used as padding because
     * it's a switch in all 4 submodes, and doesn't add any data.
     */
    const PADDING_VALUE = 29;

    // -- Submodes ------------------------------------------------------

    /** Uppercase submode. */
    const SUBMODE_UPPER = "SUBMODE_UPPER";

    /** Lowercase submode. */
    const SUBMODE_LOWER = "SUBMODE_LOWER";

    /** mixed submode (numbers and some punctuation). */
    const SUBMODE_MIXED = "SUBMODE_MIXED";

    /** Punctuation submode. */
    const SUBMODE_PUNCT = "SUBMODE_PUNCT";

    // -- Submode switches ----------------------------------------------

    /** Switch to uppercase submode. */
    const SWITCH_UPPER = "SWITCH_UPPER";

    /** Switch to uppercase submode for a single character. */
    const SWITCH_UPPER_SINGLE = "SWITCH_UPPER_SINGLE";

    /** Switch to lowercase submode. */
    const SWITCH_LOWER = "SWITCH_LOWER";

    /** Switch to mixed submode. */
    const SWITCH_MIXED = "SWITCH_MIXED";

    /** Switch to punctuation submode. */
    const SWITCH_PUNCT = "SWITCH_PUNCT";

    /** Switch to punctuation submode for a single character. */
    const SWITCH_PUNCT_SINGLE = "SWITCH_PUNCT_SINGLE";

    // ------------------------------------------------------------------

    /** Character codes per submode. */
    private $characterTables = [
        self::SUBMODE_UPPER => [
            "A", "B", "C", "D", "E", "F", "G", "H", "I",
            "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", " ",
            self::SWITCH_LOWER,
            self::SWITCH_MIXED,
            self::SWITCH_PUNCT_SINGLE
        ],

        self::SUBMODE_LOWER => [
            "a", "b", "c", "d", "e", "f", "g", "h", "i",
            "j", "k", "l", "m", "n", "o", "p", "q", "r",
            "s", "t", "u", "v", "w", "x", "y", "z", " ",
            self::SWITCH_UPPER_SINGLE,
            self::SWITCH_MIXED,
            self::SWITCH_PUNCT_SINGLE,
        ],

        self::SUBMODE_MIXED => [
            "0", "1", "2", "3", "4", "5", "6", "7", "8",
            "9", "&", "\r", "\t", ",", ":", "#", "-", ".",
            "$", "/", "+", "%", "*", "=", "^",
            self::SWITCH_PUNCT, " ",
            self::SWITCH_LOWER,
            self::SWITCH_UPPER,
            self::SWITCH_PUNCT_SINGLE
        ],

        self::SUBMODE_PUNCT => [
            ";", "<", ">", "@", "[", "\\", "]", "_", "`",
            "~", "!", "\r", "\t", ",", ":", "\n", "-", ".",
            "$", "/", "g", "|", "*", "(", ")", "?", "{", "}", "'",
            self::SWITCH_UPPER,
        ],
    ];

    /** Describes how to switch between submodes (can require two switches). */
    private $switching = [
        self::SUBMODE_UPPER => [
            self::SUBMODE_LOWER => [self::SWITCH_LOWER],
            self::SUBMODE_MIXED => [self::SWITCH_MIXED],
            self::SUBMODE_PUNCT => [self::SWITCH_MIXED, self::SWITCH_PUNCT],
        ],
        self::SUBMODE_LOWER => [
            self::SUBMODE_UPPER => [self::SWITCH_MIXED, self::SWITCH_UPPER],
            self::SUBMODE_MIXED => [self::SWITCH_MIXED],
            self::SUBMODE_PUNCT => [self::SWITCH_MIXED, self::SWITCH_PUNCT],
        ],
        self::SUBMODE_MIXED => [
            self::SUBMODE_UPPER => [self::SWITCH_UPPER],
            self::SUBMODE_LOWER => [self::SWITCH_LOWER],
            self::SUBMODE_PUNCT => [self::SWITCH_PUNCT],
        ],
        self::SUBMODE_PUNCT => [
            self::SUBMODE_UPPER => [self::SWITCH_UPPER],
            self::SUBMODE_LOWER => [self::SWITCH_UPPER, self::SWITCH_LOWER],
            self::SUBMODE_MIXED => [self::SWITCH_UPPER, self::SWITCH_MIXED],
        ],
    ];

    /** Describes which switch changes to which submode. */
    private $switchSubmode = [
        self::SWITCH_UPPER => self::SUBMODE_UPPER,
        self::SWITCH_LOWER => self::SUBMODE_LOWER,
        self::SWITCH_PUNCT => self::SUBMODE_PUNCT,
        self::SWITCH_MIXED => self::SUBMODE_MIXED,
    ];

    /**
     * Reverse lookup array. Indexed by $charater, then by $submode, gives the
     * code (row) of the character in that submode.
     */
    private $reverseLookup;

    public function __construct()
    {
        $this->populateReverseLookup();
    }

    /**
     * {@inheritdoc}
     */
    public function canEncode($char)
    {
        return isset($this->reverseLookup[$char]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSwitchCode($data)
    {
        return self::SWITCH_CODE_WORD;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($text, $addSwitchCode)
    {
        if (!is_string($text)) {
            $type = gettype($text);
            throw new \InvalidArgumentException("Expected first parameter to be a string, $type given.");
        }

        $interim = $this->encodeInterim($text);
        return $this->encodeFinal($interim, $addSwitchCode);
    }

    /**
     * Converts the given text to interim codes from the character tables.
     */
    private function encodeInterim($text)
    {
        // The default sub-mode is uppercase
        $submode = self::SUBMODE_UPPER;

        $codes = [];

        // Iterate byte-by-byte, non-ascii encoding will be encoded in bytes
        // sub-mode.
        $len = strlen($text);
        for ($i=0; $i < $len; $i++) {
            $char = $text[$i];

            // TODO: detect when to use _SINGLE switches for encoding just one
            // character
            if (!$this->existsInSubmode($char, $submode)) {
                $prevSubmode = $submode;
                $submode = $this->getSubmode($char);

                $switchCodes = $this->getSwitchCodes($prevSubmode, $submode);
                foreach ($switchCodes as $sc) {
                    $codes[] = $sc;
                }
            }

            $codes[] = $this->getCharacterCode($char, $submode);
        }

        return $codes;
    }

    /**
     * Converts the interim code to code words.
     */
    private function encodeFinal($codes, $addSwitchCode)
    {
        $codeWords = [];

        if ($addSwitchCode) {
            $codeWords[] = self::SWITCH_CODE_WORD;
        }

        // Two letters per CW
        $chunks = array_chunk($codes, 2);

        foreach ($chunks as $key => $chunk) {

            // Add padding if single char in chunk
            if (count($chunk) == 1) {
                $chunk[] = self::PADDING_VALUE;
            }

            $codeWords[] = 30 * $chunk[0] + $chunk[1];
        }

        return $codeWords;
    }

    /** Returns code for given character in given submode. */
    private function getCharacterCode($char, $submode)
    {
        if (!isset($this->reverseLookup[$char])) {
            $ord = ord($char);
            throw new \Exception("Character [$char] (ASCII $ord) cannot be encoded.");
        }

        if (!isset($this->reverseLookup[$char][$submode])) {
            $ord = ord($char);
            throw new \Exception("Character [$char] (ASCII $ord) cannot be encoded in submode [$submode].");
        }

        return $this->reverseLookup[$char][$submode];
    }

    /**
     * Builds `$this->lookup` based on data in `$this->characterTables`.
     */
    private function populateReverseLookup()
    {
        foreach ($this->characterTables as $submode => $codes) {
            foreach ($codes as $row => $char) {
                if (!isset($this->reverseLookup[$char])) {
                    $this->reverseLookup[$char] = [];
                }
                $this->reverseLookup[$char][$submode] = $row;
            }
        }
    }

    /**
     * Returns true if given character can be encoded in given submode.
     */
    private function existsInSubmode($char, $submode)
    {
        return isset($this->reverseLookup[$char][$submode]);
    }

    /**
     * Returns an array of one or two code for switching between given submodes.
     */
    private function getSwitchCodes($from, $to)
    {
        if (!isset($this->switching[$from][$to])) {
            throw new \Exception("Cannot find switching codes from [$from] to [$to].");
        }

        $switches = $this->switching[$from][$to];

        $codes = [];
        foreach ($switches as $switch) {
            $codes[] = $this->getCharacterCode($switch, $from);
            $from = $this->switchSubmode[$switch];
        }

        return $codes;
    }

    /**
     * Returns a submode in which the given character can be encoded.
     *
     * If the character exists in multiple submodes, returns the first one, as
     * ordered in $this->characterTables.
     */
    private function getSubmode($char)
    {
        if (!isset($this->reverseLookup[$char])) {
            $ord = ord($char);
            throw new \Exception("Cannot encode character [$char] (ASCII $ord).");
        }

        $source = $this->reverseLookup[$char];
        reset($source);
        return key($source);
    }
}
