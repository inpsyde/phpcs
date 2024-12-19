<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\Scopes;
use SydeCS\Syde\Helpers\FunctionDocBlock;
use SydeCS\Syde\Helpers\FunctionReturnStatement;
use SydeCS\Syde\Helpers\Functions;
use SydeCS\Syde\Helpers\Hooks;
use SydeCS\Syde\Helpers\Misc;

final class ReturnTypeDeclarationSniff implements Sniff
{
    /** @var list<string> */
    public array $additionalAllowedMethodNames = [];

    /** @var list<string> */
    public array $allowedMethodNames = [
        'count',
        'current',
        'getChildren',
        'getInnerIterator',
        'getIterator',
        'key',
        'valid',
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_CLOSURE,
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
        // Do not check return type for constructors.
        if (
            Scopes::isOOMethod($phpcsFile, $stackPtr)
            && ObjectDeclarations::getName($phpcsFile, $stackPtr) === '__construct'
        ) {
            return;
        }

        $data = FunctionDeclarations::getProperties($phpcsFile, $stackPtr);
        if (!$data['has_body']) {
            return;
        }

        $returnType = $data['return_type'] ?? null;

        $returnTypes = (is_string($returnType) && $returnType !== '')
            ? $this->normalizeReturnTypes($phpcsFile, $data)
            : [];

        $returnInfo = FunctionReturnStatement::allInfo($phpcsFile, $stackPtr);

        if ($returnTypes) {
            $this->checkNonEmptyReturnTypes($phpcsFile, $stackPtr, $returnTypes, $returnInfo);

            return;
        }

        if ($this->checkMissingGeneratorReturnType($phpcsFile, $stackPtr)) {
            return;
        }

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $phpcsFile->getTokens();

        $docTags = FunctionDocBlock::tag('return', $phpcsFile, $stackPtr);

        $docTypes = (count($docTags) === 1)
            ? FunctionDocBlock::normalizeTypesString(reset($docTags))
            : [];

        if (
            Functions::isNonDeclarableDocBlockType($docTypes, true)
            || $this->shouldIgnore($phpcsFile, $stackPtr, $tokens)
        ) {
            $this->checkNonEmptyReturnTypes($phpcsFile, $stackPtr, $docTypes, $returnInfo);

            return;
        }

        $phpcsFile->addWarning(
            'Return type is missing',
            $stackPtr,
            'NoReturnType',
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

    /**
     * @param File $file
     * @param array<string, mixed> $data
     * @return list<string>
     */
    private function normalizeReturnTypes(File $file, array $data): array
    {
        /** @var int $start */
        $start = is_int($data['return_type_token'] ?? null) ? $data['return_type_token'] : -1;

        /** @var int $end */
        $end = is_int($data['return_type_end_token'] ?? null) ? $data['return_type_end_token'] : -1;

        if ($start <= 0 && $end <= 0) {
            return [];
        }

        $returnTypesStr = Misc::tokensSubsetToString($start, $end, $file, []);

        if ((bool) ($data['nullable_return_type'] ?? false)) {
            $returnTypesStr .= '|null';
        }

        return FunctionDocBlock::normalizeTypesString($returnTypesStr);
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnInfo
     * @return void
     */
    private function checkNonEmptyReturnTypes(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo,
    ): void {

        if ($returnTypes === ['mixed']) {
            $this->checkIsNotVoid($file, $position, $returnInfo);

            return;
        }

        $isReturnTypeNull = $returnTypes === ['null'];

        if ($isReturnTypeNull || $returnTypes === ['void']) {
            $this->checkIsActualVoid($file, $position, $returnInfo, $isReturnTypeNull);

            return;
        }

        $this->checkInvalidGenerator($file, $position, $returnTypes, $returnInfo)
            || $this->checkMissingReturn($file, $position, $returnTypes, $returnInfo)
            || $this->checkIncorrectVoid($file, $position, $returnTypes, $returnInfo);
    }

    /**
     * @param File $file
     * @param int $position
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnInfo
     * @return void
     */
    private function checkIsNotVoid(
        File $file,
        int $position,
        array $returnInfo,
    ): void {

        if ($returnInfo['void'] === 0) {
            return;
        }

        $file->addError(
            'Return type is declared non-void, but void return found',
            $position,
            'IncorrectVoidReturn',
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnInfo
     * @param bool $checkNull
     * @return void
     */
    private function checkIsActualVoid(
        File $file,
        int $position,
        array $returnInfo,
        bool $checkNull,
    ): void {

        $key = $checkNull ? 'null' : 'void';

        if ($returnInfo['total'] >= 0 && $returnInfo['total'] === $returnInfo[$key]) {
            return;
        }

        $file->addError(
            'Return type is declared "%s", but incompatible return statement found',
            $position,
            $checkNull ? 'IncorrectNullReturnType' : 'IncorrectVoidReturnType',
            [$key],
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnInfo
     * @return bool
     */
    private function checkIncorrectVoid(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo,
    ): bool {

        $hasReturnNull = $returnInfo['null'] > 0;
        if (
            (!$hasReturnNull || in_array('null', $returnTypes, true))
            && (in_array('void', $returnTypes, true) || $returnInfo['void'] <= 0)
        ) {
            return false;
        }

        $message = $hasReturnNull
            ? 'Return type is not nullable, but "return null" found'
            : 'Return type does not include "void", but void return found';

        $file->addError(
            $message,
            $position,
            $hasReturnNull ? 'IncorrectNullReturn' : 'IncorrectVoidReturn',
        );

        return true;
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnInfo
     * @return bool
     */
    private function checkMissingReturn(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo,
    ): bool {

        $nonEmptyTypes = array_diff($returnTypes, ['void', 'null', 'never']);
        if ($nonEmptyTypes !== $returnTypes) {
            return false;
        }

        $hasReturnNull = $returnInfo['null'] > 0;

        $hasReturnVoid = $returnInfo['void'] > 0;

        if (!$hasReturnNull && !$hasReturnVoid) {
            return false;
        }

        $message = $hasReturnNull && !$hasReturnVoid
            ? 'Non-empty return type declared, but "return null" found'
            : 'Non-empty return type declared, but empty return found';

        $file->addError(
            $message,
            $position,
            $hasReturnNull ? 'IncorrectNullReturn' : 'IncorrectVoidReturn',
        );

        return true;
    }

    /**
     * @param File $file
     * @param int $position
     * @param list<string> $returnTypes
     * @param array{nonEmpty:int, void:int, null:int, total:int} $returnInfo
     * @return bool
     */
    private function checkInvalidGenerator(
        File $file,
        int $position,
        array $returnTypes,
        array $returnInfo,
    ): bool {

        $hasGenerator = false;
        $hasIterator = false;

        $trimLeadingSlash = static fn (string $type): string => ltrim($type, '\\');

        while (!$hasGenerator && $returnTypes) {
            $returnType = explode('&', rtrim(ltrim(array_shift($returnTypes), '('), ')'));
            $returnType = array_map($trimLeadingSlash, $returnType);

            $hasGenerator = in_array('Generator', $returnType, true);

            $hasIterator = (
                $hasIterator
                || $hasGenerator
                || in_array('Traversable', $returnType, true)
                || in_array('Iterator', $returnType, true)
                || in_array('iterable', $returnType, true)
            );
        }

        $yieldCount = Functions::countYieldInBody($file, $position);

        $return = false;

        if ($hasGenerator) {
            if ($yieldCount === 0) {
                $file->addError(
                    'Return type includes "Generator", but no "yield" found in function body',
                    $position,
                    'GeneratorReturnTypeWithoutYield',
                );

                return true;
            }

            if ($returnInfo['total'] > 1) {
                $file->addError(
                    'A function returning a Generator should only have a single return statement',
                    $position,
                    'InvalidGeneratorManyReturns',
                );
            }

            $return = true;
        }

        if (!$hasIterator && $this->checkMissingGeneratorReturnType($file, $position)) {
            return true;
        }

        return $return;
    }

    /**
     * @param File $file
     * @param int $position
     * @return bool
     */
    private function checkMissingGeneratorReturnType(File $file, int $position): bool
    {
        $yield = Functions::countYieldInBody($file, $position);
        if ($yield === 0) {
            return false;
        }

        $file->addError(
            'Return type does not include "Generator", but "yield" found in function body',
            $position,
            'NoGeneratorReturnType',
        );

        return true;
    }
}
