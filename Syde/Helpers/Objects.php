<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;
use PHPCSUtils\Utils\Scopes;
use PHPCSUtils\Utils\UseStatements;

final class Objects
{
    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    public static function countProperties(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if (!in_array($code, Collections::ooPropertyScopes(), true)) {
            return 0;
        }

        [$start, $end] = Boundaries::objectBoundaries($file, $position);
        if (($start < 0) || ($end < 0)) {
            return 0;
        }

        $found = 0;

        $next = $start + 1;
        while ($next < $end) {
            [, $innerFunctionEnd] = Boundaries::functionBoundaries($file, $next);
            if ($innerFunctionEnd > 0) {
                $next = $innerFunctionEnd + 1;
                continue;
            }

            $nextCode = $tokens[$next]['code'] ?? null;

            if ($nextCode === T_VARIABLE && Scopes::isOOProperty($file, $next)) {
                $found++;
            }

            $next++;
        }

        return $found;
    }

    /**
     * @param File $file
     * @param int $position
     * @return array<string, string>
     */
    public static function findAllImportUses(File $file, int $position): array
    {
        $usePositions = [];

        $nextUse = (int) $file->findPrevious(T_NAMESPACE, $position - 1);

        while (true) {
            $nextUse = $file->findNext(T_USE, $nextUse + 1, $position - 1);
            if ($nextUse === false) {
                break;
            }

            if (UseStatements::isImportUse($file, $nextUse)) {
                $usePositions[] = $nextUse;
            }
        }

        if (!$usePositions) {
            return [];
        }

        $tokens = $file->getTokens();

        $uses = [];

        $useNameEnd = $file->findEndOfStatement(end($usePositions));

        $positionCount = count($usePositions);

        foreach ($usePositions as $i => $usePosition) {
            $end = ($i === $positionCount - 1) ? $useNameEnd : $usePositions[$i + 1];

            $asPos = $file->findNext(T_AS, $usePosition + 1, $end, false, null, true);

            $useName = Misc::tokensSubsetToString(
                $usePosition + 1,
                (($asPos !== false) ? $asPos : $end) - 1,
                $file,
                [T_STRING, T_NS_SEPARATOR],
            );

            $useName = trim($useName, '\\');

            $key = trim((string) substr($useName, (int) strrpos($useName, '\\')), '\\');

            if ($asPos !== false) {
                $keyPos = $file->findNext(T_STRING, $asPos + 1, null, false, null, true);

                $key = (string) ($tokens[$keyPos]['content'] ?? '');
            }

            $uses[$key] = $useName;
        }

        return $uses;
    }

    /**
     * @param File $file
     * @param int $position
     * @return list<string>|null
     */
    public static function allInterfacesFullyQualifiedNames(File $file, int $position): ?array
    {
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if (!in_array($code, Collections::ooCanImplement(), true)) {
            return null;
        }

        $implementsPos = $file->findNext(T_IMPLEMENTS, $position, null, false, null, true);
        if ($implementsPos === false) {
            return null;
        }

        $namesEnd = $file->findNext(
            [T_OPEN_CURLY_BRACKET, T_EXTENDS],
            $position,
            null,
            false,
            null,
            true,
        );

        if ($namesEnd === false) {
            return null;
        }

        /** @var non-empty-list<string>|false $names */
        $names = ObjectDeclarations::findImplementedInterfaceNames($file, $position);
        if (!$names) {
            return [];
        }

        $uses = self::findAllImportUses($file, $position - 1);

        $fqns = [];
        foreach ($names as $name) {
            if (str_starts_with($name, '\\')) {
                $fqns[] = $name;
                continue;
            }

            $parts = explode('\\', $name);
            $first = array_shift($parts);

            if (isset($uses[$first])) {
                $fqns[] = rtrim('\\' . $uses[$first] . '\\' . implode('\\', $parts), '\\');
                continue;
            }

            $namespace = Namespaces::determineNamespace($file, $position);

            $fqns[] = $namespace ? "\\{$namespace}\\{$name}" : "\\{$name}";
        }

        return $fqns;
    }
}
