<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SydeCS\Syde\Helpers\Boundaries;
use SydeCS\Syde\Helpers\FunctionDocBlock;

final class StaticClosureSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_CLOSURE,
            T_FN,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        [$functionStart, $functionEnd] = Boundaries::functionBoundaries($phpcsFile, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $isStatic = $phpcsFile->findPrevious(T_STATIC, $stackPtr, $stackPtr - 3, false, null, true);
        if ($isStatic !== false) {
            return;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        $thisFound = false;
        $i = $functionStart + 1;

        while (!$thisFound && ($i < $functionEnd)) {
            $token = $tokens[$i];

            $code = $token['code'] ?? null;
            $content = (string) ($token['content'] ?? '');

            $thisFound = (
                ($code === T_VARIABLE && $content === '$this')
                || (
                    in_array($code, [T_DOUBLE_QUOTED_STRING, T_HEREDOC], true)
                    && str_contains($content, '$this->')
                )
            );

            $i++;
        }

        if ($thisFound) {
            return;
        }

        if ($this->checkFunctionDocBlock($phpcsFile, $stackPtr)) {
            return;
        }

        $message = 'Closure found in line %d could be static';
        $line = (int) $tokens[$stackPtr]['line'];

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'PossiblyStaticClosure', [$line])) {
            $this->fix($phpcsFile, $stackPtr);
        }
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function checkFunctionDocBlock(File $file, int $position): bool
    {
        $boundDoc = FunctionDocBlock::tag('bound', $file, $position);
        if ($boundDoc) {
            return true;
        }

        $varDoc = FunctionDocBlock::tag('var', $file, $position);
        foreach ($varDoc as $content) {
            if (preg_match('~(?:^|\s+)\$this(?:$|\s+)~', $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     */
    private function fix(File $file, int $position): void
    {
        $fixer = $file->fixer;

        $fixer->beginChangeset();

        $fixer->addContentBefore($position, 'static ');

        $fixer->endChangeset();
    }
}
