<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class ShortOpenTagWithEchoSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_ECHO,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        $prevPtr = $phpcsFile->findPrevious(
            Tokens::$emptyTokens,
            ($stackPtr - 1),
            null,
            true,
        );

        if (!is_int($prevPtr) || !isset($tokens[$prevPtr])) {
            return;
        }

        $prevToken = $tokens[$prevPtr];

        if ($prevToken['code'] !== T_OPEN_TAG) {
            return;
        }

        $currentLine = $tokens[$stackPtr]['line'];

        if ($prevToken['line'] !== $currentLine) {
            return;
        }

        $closeTagPtr = $phpcsFile->findNext(
            T_CLOSE_TAG,
            ($stackPtr + 1),
        );

        if (
            !is_int($closeTagPtr)
            || !isset($tokens[$closeTagPtr])
            || $tokens[$closeTagPtr]['line'] !== $currentLine
        ) {
            return;
        }

        $message = 'Single-line output on line %d should use short echo tag "%s" instead of "%s".';
        $data = [$currentLine, '<?=', '<?php echo'];

        if ($phpcsFile->addFixableWarning($message, $stackPtr, 'Encouraged', $data)) {
            $this->fix($phpcsFile, $stackPtr, $prevPtr);
        }
    }

    /**
     * @param File $file
     * @param int $echoPtr
     * @param int $openTagPtr
     * @return void
     */
    private function fix(File $file, int $echoPtr, int $openTagPtr): void
    {
        $fixer = $file->fixer;

        $fixer->beginChangeset();

        $fixer->replaceToken($echoPtr, '');
        $fixer->replaceToken($openTagPtr, '<?=');

        $fixer->endChangeset();
    }
}
