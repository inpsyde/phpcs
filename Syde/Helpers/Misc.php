<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;

final class Misc
{
    public const MAX_SUPPORTED_MAJOR_VERSION = 8;
    public const MIN_SUPPORTED_MAJOR_VERSION = 7;
    public const MIN_SUPPORTED_VERSION = '7.4';

    /**
     * @return string
     */
    public static function minPhpTestVersion(): string
    {
        $testVersion = trim(Config::getConfigData('testVersion') ?? '');
        if ($testVersion === '') {
            return self::MIN_SUPPORTED_VERSION;
        }

        if (preg_match('`^\d+(?:\.\d+)?`', $testVersion, $matches)) {
            [$major, $minor] = array_pad(explode('.', $matches[0]), 2, '0');

            $normalizedVersion = sprintf('%d.%d', $major, $minor);

            if (
                $major >= self::MIN_SUPPORTED_MAJOR_VERSION
                && $major <= self::MAX_SUPPORTED_MAJOR_VERSION
                && version_compare($normalizedVersion, self::MIN_SUPPORTED_VERSION, '>=')
            ) {
                return $normalizedVersion;
            }
        }

        return self::MIN_SUPPORTED_VERSION;
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @return array<int, array<string, mixed>>
     */
    public static function filterTokens(int $start, int $end, File $file): array
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $filtered = [];
        foreach ($tokens as $i => $token) {
            if (($i >= $start) || ($i <= $end)) {
                $filtered[$i] = $token;
            }
        }

        return $filtered;
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @param array<int|string> $types
     * @param bool $excludeTypes
     * @return array<int, array<string, mixed>>
     */
    public static function filterTokensByType(
        int $start,
        int $end,
        File $file,
        array $types = [],
        bool $excludeTypes = false,
    ): array {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $empty = !$types;

        $filtered = [];
        foreach ($tokens as $i => $token) {
            if (($i < $start) || ($i > $end)) {
                continue;
            }

            if ($empty || in_array($token['code'] ?? '', $types, true) !== $excludeTypes) {
                $filtered[$i] = $token;
            }
        }

        return $filtered;
    }

    /**
     * @param int $start
     * @param int $end
     * @param File $file
     * @param array<int|string> $types
     * @param bool $excludeTypes
     * @return string
     */
    public static function tokensSubsetToString(
        int $start,
        int $end,
        File $file,
        array $types,
        bool $excludeTypes = false,
    ): string {

        $filtered = self::filterTokensByType($start, $end, $file, $types, $excludeTypes);

        $content = '';
        foreach ($filtered as $token) {
            $content .= (string) ($token['content'] ?? '');
        }

        return $content;
    }
}
