<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\ObjectDeclarations;

final class DeprecatedSerializableInterfaceSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_ANON_CLASS,
            T_CLASS,
            T_ENUM,
            T_INTERFACE,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokenCode = $phpcsFile->getTokens()[$stackPtr]['code'] ?? null;

        $find = ($tokenCode === T_INTERFACE)
            ? ObjectDeclarations::findExtendedInterfaceNames($phpcsFile, $stackPtr)
            : ObjectDeclarations::findImplementedInterfaceNames($phpcsFile, $stackPtr);

        if (($find === false) || !in_array('Serializable', $find, true)) {
            return;
        }

        $phpcsFile->addError(
            'The "%s" interface has been deprecated; use "%s" instead',
            $stackPtr,
            'Found',
            ['Serializable', '__serialize/__unserialize'],
        );
    }
}
