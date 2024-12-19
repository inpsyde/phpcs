<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Utils\ControlStructures;

/**
 * The implementation is inspired by Universal.ControlStructures.DisallowAlternativeSyntaxSniff.
 *
 * @see https://github.com/PHPCSStandards/PHPCSExtra/blob/ed86bb117c340f654eab603a06b95a437ac619c9/Universal/Sniffs/ControlStructures/DisallowAlternativeSyntaxSniff.php
 */
final class AlternativeSyntaxSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_FOR,
            T_FOREACH,
            T_IF,
            T_SWITCH,
            T_WHILE,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (ControlStructures::hasBody($phpcsFile, $stackPtr) === false) {
            // Single line control structure is out of scope.
            return;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        $openerPtr = $tokens[$stackPtr]['scope_opener'] ?? null;
        $closerPtr = $tokens[$stackPtr]['scope_closer'] ?? null;

        if (!is_int($openerPtr) || !is_int($closerPtr) || !isset($tokens[$openerPtr])) {
            // Inline control structure or parse error.
            return;
        }

        if ($tokens[$openerPtr]['code'] === T_COLON) {
            // Alternative control structure.
            return;
        }

        $chainedIssues = $this->findChainedIssues($phpcsFile, $tokens, $stackPtr);

        $message = 'Control structure with inline HTML should use alternative syntax. Found "%s".';

        foreach ($chainedIssues as $conditionPtr) {
            $phpcsFile->addWarning(
                $message,
                $conditionPtr,
                'Encouraged',
                [$tokens[$conditionPtr]['content']],
            );
        }
    }

    /**
     * We consider if - else (else if) chain as the single structure
     * as they should be replaced with alternative syntax altogether.
     *
     * @param File $phpcsFile
     * @param array<int, array<string, mixed>> $tokens
     * @param int $stackPtr
     * @return list<int>
     */
    private function findChainedIssues(File $phpcsFile, array $tokens, int $stackPtr): array
    {
        $hasInlineHtml = false;
        $currentPtr = $stackPtr;
        $chainedIssues = [];

        do {
            $openerPtr = $tokens[$currentPtr]['scope_opener'] ?? null;
            $closerPtr = $tokens[$currentPtr]['scope_closer'] ?? null;

            if (!is_int($openerPtr) || !is_int($closerPtr)) {
                // Something went wrong.
                break;
            }

            $chainedIssues[] = $currentPtr;

            if (!$hasInlineHtml) {
                $hasInlineHtml = $phpcsFile->findNext(T_INLINE_HTML, ($currentPtr + 1), $closerPtr) !== false;
            }

            $currentPtr = $this->findNextChainPointer($phpcsFile, $tokens, $closerPtr);
        } while (is_int($currentPtr));

        return $hasInlineHtml ? $chainedIssues : [];
    }

    /**
     * Find 3 possible options:
     *  - else
     *  - elseif
     *  - else if
     *
     * @param File $phpcsFile
     * @param array<int, array<string, mixed>> $tokens
     * @param int $closerPtr
     * @return int|null
     */
    private function findNextChainPointer(File $phpcsFile, array $tokens, int $closerPtr): ?int
    {
        $firstPtr = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($closerPtr + 1),
            null,
            true,
        );

        if (!is_int($firstPtr)) {
            return null;
        }

        if (!isset($tokens[$firstPtr])) {
            return null;
        }

        $code = $tokens[$firstPtr]['code'];

        if ($code === T_ELSEIF) {
            return $firstPtr;
        }

        if ($code !== T_ELSE) {
            return null;
        }

        $secondPtr = $phpcsFile->findNext(
            Tokens::$emptyTokens,
            ($firstPtr + 1),
            null,
            true,
        );

        if (!is_int($secondPtr)) {
            return $firstPtr;
        }

        if (!isset($tokens[$secondPtr])) {
            return $firstPtr;
        }

        if ($tokens[$secondPtr]['code'] !== T_IF) {
            return $firstPtr;
        }

        return $secondPtr;
    }
}
