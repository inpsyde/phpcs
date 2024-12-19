<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SydeCS\Syde\Helpers\Functions;

final class DisallowTopLevelDefineSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_STRING,
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

        $content = $tokens[$stackPtr]['content'] ?? '';
        if ($content !== 'define') {
            return;
        }

        $level = $tokens[$stackPtr]['level'] ?? -1;
        if ($level !== 0) {
            return;
        }

        if (!Functions::looksLikeFunctionCall($phpcsFile, $stackPtr)) {
            return;
        }

        $phpcsFile->addError(
            'Do not use "%s" for top-level constant definitions; use "%s" instead',
            $stackPtr,
            'Found',
            ['define', 'const'],
        );
    }
}
