<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\Scopes;

final class Names
{
    public const NAMEABLE_TOKENS = [
        T_CLASS,
        T_CONST,
        T_ENUM,
        T_ENUM_CASE,
        T_FUNCTION,
        T_INTERFACE,
        T_NAMESPACE,
        T_TRAIT,
        T_VARIABLE,
    ];

    /**
     * @param File $file
     * @param int $position
     * @return string|null Null is an error, empty string is fine.
     */
    public static function nameableTokenName(File $file, int $position): ?string
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if (!in_array($code, self::NAMEABLE_TOKENS, true)) {
            return null;
        }

        if ($code === T_VARIABLE) {
            $name = ltrim((string) ($tokens[$position]['content'] ?? ''), '$');

            return ($name === '') ? null : $name;
        }

        if ($code === T_NAMESPACE) {
            if (!Namespaces::isDeclaration($file, $position)) {
                return null;
            }

            $name = (string) Namespaces::getDeclaredName($file, $position);

            return ($name === '') ? null : $name;
        }

        $namePosition = $file->findNext(T_STRING, $position, null, false, null, true);

        $name = (string) $tokens[$namePosition]['content'];

        return ($name === '') ? null : $name;
    }

    /**
     * @param File $file
     * @param int $position
     * @return string
     */
    public static function tokenTypeName(File $file, int $position): string // phpcs:ignore Generic.Metrics.CyclomaticComplexity
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? -1;

        return match ($code) {
            T_CLASS, T_ANON_CLASS => 'class',
            T_CONST => 'constant',
            T_ENUM => 'enum',
            T_ENUM_CASE => 'enum case',
            T_FUNCTION => 'function',
            T_INTERFACE => 'interface',
            T_LNUMBER, T_DNUMBER => 'number',
            T_STRING => 'string',
            T_THIS => 'property',
            T_TRAIT => 'trait',
            T_VARIABLE => Scopes::isOOProperty($file, $position) ? 'property' : 'variable',
            T_WHITESPACE => 'whitespace',
            default => match (true) {
                in_array($code, self::operatorTokens(), true) => 'operator',
                in_array($code, Tokens::$textStringTokens, true) => 'text',
                in_array($code, Tokens::$commentTokens, true) => 'comment',
                default => 'keyword',
            },
        };
    }

    /**
     * @return list<int|string>
     */
    private static function operatorTokens(): array
    {
        static $tokens;
        if (!is_array($tokens)) {
            $tokens = array_merge(
                array_keys(Tokens::$arithmeticTokens),
                array_keys(Tokens::$assignmentTokens),
                array_keys(Tokens::$equalityTokens),
                array_keys(Tokens::$arithmeticTokens),
                array_keys(Tokens::$operators),
                array_keys(Tokens::$booleanOperators),
                array_keys(Tokens::$castTokens),
                array_keys(Tokens::$bracketTokens),
                array_keys(Tokens::$heredocTokens),
                array_keys(Collections::objectOperators()),
                array_keys(Collections::incrementDecrementOperators()),
                array_keys(Collections::phpOpenTags()),
                array_keys(Collections::namespaceDeclarationClosers()),
                [
                    T_ASPERAND,
                    T_ATTRIBUTE_END,
                    T_BACKTICK,
                    T_COLON,
                    T_COMMA,
                    T_ELLIPSIS,
                    T_FN_ARROW,
                    T_MATCH_ARROW,
                    T_STRING_CONCAT,
                    T_TYPE_INTERSECTION,
                    T_TYPE_UNION,
                ],
            );
        }

        /** @var list<int|string> */
        return $tokens;
    }
}
