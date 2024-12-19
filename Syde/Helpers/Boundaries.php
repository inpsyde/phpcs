<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\Arrays;

final class Boundaries
{
    /**
     * @param File $file
     * @param int $position
     * @return array{int, int}
     */
    public static function functionBoundaries(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if (in_array($code, array_keys(Collections::functionDeclarationTokens()), true)) {
            return self::startEnd($file, $position);
        }

        return [-1, -1];
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{int, int}
     */
    public static function objectBoundaries(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if (in_array($code, Tokens::$ooScopeTokens, true)) {
            return self::startEnd($file, $position);
        }

        return [-1, -1];
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{int, int}
     */
    public static function arrayBoundaries(File $file, int $position): array
    {
        $openClose = Arrays::getOpenClose($file, $position);
        if (!is_array($openClose)) {
            return [-1, -1];
        }

        $start = $openClose['opener'] ?? null;
        $end = $openClose['closer'] ?? null;

        if (is_int($start) && is_int($end)) {
            return [$start, $end];
        }

        return [-1, -1];
    }

    /**
     * @param File $file
     * @param int $position
     * @return array{int, int}
     */
    private static function startEnd(File $file, int $position): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $token = $tokens[$position] ?? [];

        $code = $token['code'] ?? null;
        if ($code === T_FN) {
            $start = $file->findNext(T_FN_ARROW, $position + 1, null, false, null, true);
            if ($start === false) {
                return [-1, -1];
            }

            return [$start + 1, $file->findEndOfStatement($start)];
        }

        $start = (int) ($token['scope_opener'] ?? 0);
        $end = (int) ($token['scope_closer'] ?? 0);

        if (($start <= 0) || ($end <= 0) || ($start >= ($end - 1))) {
            return [-1, -1];
        }

        return [$start, $end];
    }
}
