<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\WordPress;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SydeCS\Syde\Helpers\Boundaries;
use SydeCS\Syde\Helpers\FunctionReturnStatement;
use SydeCS\Syde\Helpers\Hooks;

final class HookClosureReturnSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_CLOSURE,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        if (!Hooks::isHookClosure($phpcsFile, $stackPtr)) {
            return;
        }

        [$functionStart, $functionEnd] = Boundaries::functionBoundaries($phpcsFile, $stackPtr);
        if ($functionStart < 0 || $functionEnd <= 0) {
            return;
        }

        $returnData = FunctionReturnStatement::allInfo($phpcsFile, $stackPtr);

        $voidReturnCount = $returnData['void'];

        // Allow a filter to return null on purpose.
        $nonVoidReturnCount = $returnData['nonEmpty'] + $returnData['null'];

        if (Hooks::isHookClosure($phpcsFile, $stackPtr, true, false)) {
            if ($voidReturnCount || !$nonVoidReturnCount) {
                $phpcsFile->addError(
                    'No (or void) return found for filter closure',
                    $stackPtr,
                    'NoReturnFromFilter',
                );
            }
            return;
        }

        if ($nonVoidReturnCount) {
            $phpcsFile->addError(
                'Return value found for action closure',
                $stackPtr,
                'ReturnFromAction',
            );
        }
    }
}
