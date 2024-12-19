<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\Scopes;

final class DisallowGetterSetterSniff implements Sniff
{
    public const ALLOWED_NAMES = [
        'getChildren',
        'getInnerIterator',
        'getIterator',
        'setUp',
        'setUpBeforeClass',
    ];

    public bool $skipForPrivate = true;
    public bool $skipForProtected = false;

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
        if (!Scopes::isOOMethod($phpcsFile, $stackPtr)) {
            return;
        }

        $functionName = $phpcsFile->getDeclarationName($stackPtr) ?? '';
        if (($functionName === '') || in_array($functionName, self::ALLOWED_NAMES, true)) {
            return;
        }

        if ($this->shouldSkip($phpcsFile, $stackPtr)) {
            return;
        }

        preg_match('/^(set|get)[_A-Z0-9]+/', $functionName, $matches);
        if (!$matches) {
            return;
        }

        if ($matches[1] === 'set') {
            $phpcsFile->addWarning(
                'Setters are discouraged. Use immutable objects and constructor injection instead. '
                . 'Classes that really need changing state, use behavior naming instead, '
                . 'for example, "changeName()" instead of "setName()".',
                $stackPtr,
                'SetterFound',
            );
            return;
        }

        $phpcsFile->addWarning(
            'Getters are discouraged. Apply the "Tell, Don\'t Ask" principle, if possible. '
            . 'If you really need getters, use property-based naming instead, '
            . 'for example, "id()" instead of "getId()".',
            $stackPtr,
            'GetterFound',
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function shouldSkip(File $file, int $position): bool
    {
        if (!$this->skipForPrivate && !$this->skipForProtected) {
            return false;
        }

        $modifierPointerPosition = $file->findPrevious(
            [T_WHITESPACE, T_ABSTRACT],
            $position - 1,
            null,
            true,
            null,
            true,
        );

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $modifier = $tokens[$modifierPointerPosition]['code'] ?? null;

        if (($modifier === T_PRIVATE) && $this->skipForPrivate) {
            return true;
        }

        if (($modifier === T_PROTECTED) && $this->skipForProtected) {
            return true;
        }

        return false;
    }
}
