<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

final class FunctionReturnStatement
{
    /**
     * @param File $file
     * @param int $position
     * @return array{nonEmpty:int, void:int, null:int, total:int}
     */
    public static function allInfo(File $file, int $position): array
    {
        $returnCount = ['nonEmpty' => 0, 'void' => 0, 'null' => 0, 'total' => 0];

        [$start, $end] = Boundaries::functionBoundaries($file, $position);
        if (($start < 0) || ($end <= 0)) {
            $returnCount['total'] = -1;
            return $returnCount;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if ($code === T_FN) {
            $returnCount['total'] = 1;
            $returnCount[self::isNull($file, $position) ? 'null' : 'nonEmpty'] = 1;
            return $returnCount;
        }

        $pos = $start + 1;

        while ($pos < $end) {
            [, $innerFunctionEnd] = Boundaries::functionBoundaries($file, $pos);
            if ($innerFunctionEnd > 0) {
                $pos = $innerFunctionEnd + 1;
                continue;
            }

            [, $innerClassEnd] = Boundaries::objectBoundaries($file, $pos);
            if ($innerClassEnd > 0) {
                $pos = $innerClassEnd + 1;
                continue;
            }

            $code = $tokens[$pos]['code'] ?? null;
            if ($code !== T_RETURN) {
                $pos++;
                continue;
            }

            $returnCount = self::updateReturnCount($returnCount, $file, $pos);

            $pos++;
        }

        return $returnCount;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isVoid(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if ($code !== T_RETURN) {
            return false;
        }

        $nextPos = $file->findNext(Tokens::$emptyTokens, $position + 1, null, true, null, true);

        $nextCode = $tokens[$nextPos]['code'] ?? null;

        return $nextCode === T_SEMICOLON;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isNull(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if ($code !== T_RETURN && $code !== T_FN) {
            return false;
        }

        if ($code === T_FN) {
            $position = $file->findNext(T_FN_ARROW, $position + 1, null, false, null, true);
            if ($position === false) {
                return false;
            }
        }

        $returnString = Misc::tokensSubsetToString(
            $position + 1,
            $file->findEndOfStatement($position + 1) - 1,
            $file,
            Tokens::$emptyTokens,
            true,
        );

        return strtolower($returnString) === 'null';
    }

    /**
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnCount
     * @param File $file
     * @param int $position
     * @return array{nonEmpty:int, void:int, null:int, total:int}
     */
    private static function updateReturnCount(array $returnCount, File $file, int $position): array
    {
        $returnCount['total']++;

        $isVoid = self::isVoid($file, $position);
        if ($isVoid) {
            $returnCount['void']++;
        }

        $isNull = !$isVoid && self::isNull($file, $position);
        if ($isNull) {
            $returnCount['null']++;
        }

        if (!$isVoid && !$isNull) {
            $returnCount['nonEmpty']++;
        }

        return $returnCount;
    }
}
