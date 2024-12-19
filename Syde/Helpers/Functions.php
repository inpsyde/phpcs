<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\Conditions;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;

final class Functions
{
    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function looksLikeFunctionCall(File $file, int $position): bool
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;

        $functionTokens = array_merge(array_keys(Tokens::$functionNameTokens), [T_VARIABLE]);
        if (
            !in_array($code, $functionTokens, true)
            || ($code === T_VARIABLE && Scopes::isOOProperty($file, $position))
        ) {
            return false;
        }

        $callOpen = $file->findNext(Tokens::$emptyTokens, $position + 1, null, true, null, true);
        if ($callOpen === false || $tokens[$callOpen]['code'] !== T_OPEN_PARENTHESIS) {
            return false;
        }

        $prevExclude = Tokens::$emptyTokens;

        $prevMeaningful = $file->findPrevious($prevExclude, $position - 1, null, true, null, true);

        if (
            $prevMeaningful !== false
            && $tokens[$prevMeaningful]['code'] === T_NS_SEPARATOR
        ) {
            $prevExclude = array_merge($prevExclude, [T_STRING, T_NS_SEPARATOR]);
            $prevStart = $prevMeaningful - 1;
            $prevMeaningful = $file->findPrevious($prevExclude, $prevStart, null, true, null, true);
        }

        $prevMeaningfulCode = ($prevMeaningful !== false)
            ? $tokens[$prevMeaningful]['code']
            : null;

        if (in_array($prevMeaningfulCode, [T_NEW, T_FUNCTION], true)) {
            return false;
        }

        $callClose = $file->findNext([T_CLOSE_PARENTHESIS], $callOpen + 1, null, false, null, true);
        if ($callClose === false) {
            return false;
        }

        $expectedCallClose = $tokens[$callOpen]['parenthesis_closer'] ?? -1;

        return $callClose === $expectedCallClose;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isArrayAccess(File $file, int $position): bool
    {
        if (!Scopes::isOOMethod($file, $position)) {
            return false;
        }

        $methods = ['offsetExists', 'offsetGet', 'offsetSet', 'offsetUnset'];

        return in_array(FunctionDeclarations::getName($file, $position), $methods, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function bodyContent(File $file, int $position): string
    {
        [$start, $end] = Boundaries::functionBoundaries($file, $position);
        if ($start < 0 || $end < 0) {
            return '';
        }

        return Misc::tokensSubsetToString($start + 1, $end - 1, $file, []);
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public static function countYieldInBody(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        if ($tokens[$position]['code'] === T_FN) {
            return 0;
        }

        [$start, $end] = Boundaries::functionBoundaries($file, $position);
        if ($start < 0 || $end <= 0) {
            return 0;
        }

        $pos = $start + 1;

        $found = 0;

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
            if ($code === T_YIELD || $code === T_YIELD_FROM) {
                $found++;
            }

            $pos++;
        }

        return $found;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    public static function isPsrMethod(File $file, int $position): bool
    {
        if (!Scopes::isOOMethod($file, $position)) {
            return false;
        }

        $scopes = [T_CLASS, T_ANON_CLASS];

        $classPos = Conditions::getLastCondition($file, $position, $scopes);
        if ($classPos === false) {
            return false;
        }

        $tokens = $file->getTokens();

        $code = $tokens[$classPos]['code'] ?? null;
        if (!in_array($code, $scopes, true)) {
            return false;
        }

        $interfaces = Objects::allInterfacesFullyQualifiedNames($file, $classPos) ?? [];
        foreach ($interfaces as $interface) {
            if (str_starts_with(strtolower($interface), '\\psr\\')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sometimes we don't declare the type because we can't. For example, if the type is "mixed" or
     * it is union, and we are using PHP 7.4.
     * In those cases, we expect to document the type via doc block, and this functions aims
     * to return true.
     *
     * @param list<string> $docTypes
     * @param bool $return
     * @return bool
     */
    public static function isNonDeclarableDocBlockType(array $docTypes, bool $return): bool
    {
        if (!$docTypes) {
            return false;
        }

        $minVer = Misc::minPhpTestVersion();

        $is81 = version_compare($minVer, '8.1', '>=');

        // If "never" is there, this is valid for return types and PHP < 8.1,
        // not valid for argument types.
        if (in_array('never', $docTypes, true)) {
            return $return && !$is81;
        }

        $count = count($docTypes);

        $isIntersection = str_contains(implode('|', $docTypes), '&');

        $is80 = version_compare($minVer, '8.0', '>=');
        $is82 = version_compare($minVer, '8.2', '>=');

        if ($count > 1) {
            // Union type with "mixed" make no sense, just use "mixed"
            if (in_array('mixed', $docTypes, true)) {
                return false;
            }

            // Union type without null, valid if we're not on PHP < 8.0, or on PHP < 8.2 and
            // there's an intersection (DNF)
            if (in_array('null', $docTypes, true)) {
                $docTypes = array_diff($docTypes, ['null']);
                $count = count($docTypes);
            }
        }

        // Union type with "null" plus something else, valid if we're not on PHP < 8.0 or
        // on PHP < 8.2 and there's an intersection (DNF)
        if ($count > 1) {
            return !$is80 || (!$is82 && $isIntersection);
        }

        $singleDocType = (string) reset($docTypes);

        return self::isNonDeclarableSingleDocBlockType($singleDocType, $return);
    }

    /**
     * Sometimes we don't declare the type because we can't. For example, if the type is "mixed" or
     * it is union, and we are using PHP 7.4.
     * In those cases, we expect to document the type via doc block, and this functions aims
     * to return true.
     *
     * @param string $docType
     * @param bool $return
     * @return bool
     */
    private static function isNonDeclarableSingleDocBlockType(string $docType, bool $return): bool
    {
        $minVer = Misc::minPhpTestVersion();

        $is81 = version_compare($minVer, '8.1', '>=');

        // If "never" is there, this is valid for return types and PHP < 8.1,
        // not valid for argument types.
        if ($docType === 'never') {
            return $return && !$is81;
        }

        $isIntersection = str_contains($docType, '&');

        $is80 = version_compare($minVer, '8.0', '>=');
        $is82 = version_compare($minVer, '8.2', '>=');

        return (
            // If the single type is "mixed" is valid if we are on PHP < 8.0.
            ($docType === 'mixed' && !$is80)
            // If the single type is "null" is valid if we are on PHP < 8.2.
            || ($docType === 'null' && !$is82)
            // If the single is an intersection, is valid if we are on PHP < 8.1
            || ($isIntersection && !$is81)
        );
    }
}
