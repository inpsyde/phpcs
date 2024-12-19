<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Tokens\Collections;
use SydeCS\Syde\Helpers\Names;
use SydeCS\Syde\Helpers\Objects;

final class PropertyLimitSniff implements Sniff
{
    public int $maxCount = 10;

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return array_keys(Collections::ooPropertyScopes());
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $count = Objects::countProperties($phpcsFile, $stackPtr);
        if ($count <= $this->maxCount) {
            return;
        }

        $tokenTypeName = Names::tokenTypeName($phpcsFile, $stackPtr);

        $phpcsFile->addWarning(
            'Number of %s properties (%d) exceeds allowed maximum of %d',
            $stackPtr,
            'TooManyProperties',
            [$tokenTypeName, $count, $this->maxCount],
        );
    }
}
