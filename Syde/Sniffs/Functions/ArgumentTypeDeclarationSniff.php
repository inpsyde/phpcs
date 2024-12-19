<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Scopes;
use SydeCS\Syde\Helpers\FunctionDocBlock;
use SydeCS\Syde\Helpers\Functions;
use SydeCS\Syde\Helpers\Hooks;

final class ArgumentTypeDeclarationSniff implements Sniff
{
    /** @var list<string> */
    public array $additionalAllowedMethodNames = [];

    /** @var list<string> */
    public array $allowedMethodNames = [
        'seek',
        'unserialize',
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_CLOSURE,
            T_FN,
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

        if ($this->shouldIgnore($phpcsFile, (int) $stackPtr, $tokens)) {
            return;
        }

        /** @var array<array{name: string, type_hint?: string|false}> $parameters */
        $parameters = FunctionDeclarations::getParameters($phpcsFile, $stackPtr);

        $docBlockTypes = FunctionDocBlock::allParamTypes($phpcsFile, $stackPtr);

        $errors = [];
        foreach ($parameters as $parameter) {
            $typeHint = $parameter['type_hint'] ?? '';
            if (($typeHint !== '') && ($typeHint !== false)) {
                continue;
            }

            $docTypes = $docBlockTypes[$parameter['name']] ?? [];
            if (!Functions::isNonDeclarableDocBlockType($docTypes, false)) {
                $errors[] = $parameter['name'];
            }
        }

        if (!$errors) {
            return;
        }

        $phpcsFile->addWarning(
            'Argument type is missing (parameters: %s)',
            $stackPtr,
            'NoArgumentType',
            [implode(', ', $errors)],
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    private function isAllowedMethodName(string $name): bool
    {
        static $allowedMethodNames;
        if (!is_array($allowedMethodNames)) {
            $allowedMethodNames = array_unique(array_merge(
                $this->allowedMethodNames,
                $this->additionalAllowedMethodNames,
            ));
        }

        return in_array($name, $allowedMethodNames, true);
    }

    /**
     * @param File $file
     * @param int $position
     * @param array<int, array<string, mixed>> $tokens
     * @return bool
     */
    private function shouldIgnore(File $file, int $position, array $tokens): bool
    {
        if (
            Functions::isArrayAccess($file, $position)
            || Functions::isPsrMethod($file, $position)
            || FunctionDeclarations::isSpecialMethod($file, $position)
            || Hooks::isHookClosure($file, $position)
            || Hooks::isHookFunction($file, $position)
        ) {
            return true;
        }

        if (!Scopes::isOOMethod($file, $position)) {
            return false;
        }

        $tokenCode = $tokens[$position]['code'] ?? '';

        $name = ($tokenCode !== T_FN) ? (string) FunctionDeclarations::getName($file, $position) : '';

        return $this->isAllowedMethodName($name);
    }
}
