<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class FunctionBodyStartSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_FUNCTION,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        $token = $tokens[$stackPtr] ?? [];

        $scopeOpener = (int) ($token['scope_opener'] ?? -1);
        $scopeCloser = (int) ($token['scope_closer'] ?? -1);

        if ($scopeOpener < 0 || $scopeCloser < 0 || $scopeCloser <= $scopeOpener) {
            return;
        }

        $bodyStart = $phpcsFile->findNext(T_WHITESPACE, $scopeOpener + 1, null, true);
        if (
            ($bodyStart === false)
            || !array_key_exists($bodyStart, $tokens)
            || $bodyStart <= $scopeOpener
            || $bodyStart >= $scopeCloser
        ) {
            return;
        }

        [$code, $message, $expectedLine] = $this->checkBodyStart(
            $bodyStart,
            (int) ($tokens[$scopeOpener]['line'] ?? -1),
            (int) ($token['line'] ?? -1),
            $phpcsFile,
        );

        if (
            (($code === null) || ($code === ''))
            || (($message === null) || ($message === ''))
            || ($expectedLine === null)
        ) {
            return;
        }

        if ($phpcsFile->addFixableWarning($message, $stackPtr, $code)) {
            $this->fix($phpcsFile, $bodyStart, $expectedLine, $scopeOpener);
        }
    }

    /**
     * @param int $bodyStart
     * @param int $openerLine
     * @param int $functionLine
     * @param File $file
     * @return array{null, null, null}|array{string, string, int}
     */
    private function checkBodyStart(
        int $bodyStart,
        int $openerLine,
        int $functionLine,
        File $file,
    ): array {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $bodyLine = (int) ($tokens[$bodyStart]['line'] ?? -1);

        $isMultiLineDeclare = ($openerLine - $functionLine) > 1;
        $isSingleLineDeclare = $openerLine === ($functionLine + 1);

        $isSingleLineSignature = $openerLine && ($openerLine === $functionLine);

        $error = (
            (($isMultiLineDeclare || $isSingleLineSignature) && $bodyLine !== ($openerLine + 2))
            || ($isSingleLineDeclare && $bodyLine > ($openerLine + 2))
        );

        if (!$error) {
            return [null, null, null];
        }

        $startWithComment = in_array($tokens[$bodyStart]['code'], Tokens::$emptyTokens, true);

        if (!$startWithComment && ($isMultiLineDeclare || $isSingleLineSignature)) {
            $code = $isSingleLineSignature
                ? 'WrongForSingleLineSignature'
                : 'WrongForMultiLineDeclaration';

            $signature = $isSingleLineSignature
                ? 'single-line signature and the opening curly brace on the same line'
                : 'multi-line signature';

            $message = sprintf(
                'For functions with a %s, the body should start with a blank line',
                $signature,
            );

            return [$code, $message, $openerLine + 2];
        }

        if ($isSingleLineDeclare) {
            $message = sprintf(
                'For functions with a %s, the body should not start with a blank line',
                'single-line signature and the opening curly brace on the next line',
            );

            return ['WrongForSingleLineDeclaration', $message, $openerLine + 1];
        }

        return [null, null, null];
    }

    /**
     * @param File $file
     * @param int $bodyStart
     * @param int $expectedLine
     * @param int $scopeOpener
     * @return void
     */
    private function fix(File $file, int $bodyStart, int $expectedLine, int $scopeOpener): void
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $currentLine = (int) ($tokens[$bodyStart]['line'] ?? -1);

        if ($currentLine === $expectedLine) {
            return;
        }

        $fixer = $file->fixer;

        $fixer->beginChangeset();

        if ($currentLine < $expectedLine) {
            for ($i = ($expectedLine - $currentLine); $i > 0; $i--) {
                $fixer->addNewline($scopeOpener);
            }

            $fixer->endChangeset();

            return;
        }

        for ($i = $bodyStart - 1; $i > 0; $i--) {
            $line = $tokens[$i]['line'];
            if ($line === $currentLine) {
                continue;
            }

            if ($line < $expectedLine) {
                break;
            }

            $fixer->replaceToken($i, '');
        }

        $fixer->endChangeset();
    }
}
