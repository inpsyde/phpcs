<?php
// @phpcsSniff Syde.PHP.DisallowTopLevelDefine

if (!defined('X')) {
    define('X', 1);
}

if (false) {
    define('Y', 1);
}

// @phpcsErrorOnNextLine
define('Z', 1);

const ZZZ = 1;
