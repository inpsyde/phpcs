<?php

declare(strict_types=1);

namespace SydeCS\Syde\Helpers;

use PHP_CodeSniffer\Files\File;

final class FunctionDocBlock
{
    /**
     * @param File $file
     * @param int $position
     * @param bool $normalizeContent
     * @return array<string, list<string>>
     */
    public static function allTags(
        File $file,
        int $position,
        bool $normalizeContent = true,
    ): array {

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $code = $tokens[$position]['code'] ?? null;
        if (!in_array($code, [T_FUNCTION, T_CLOSURE, T_FN], true)) {
            return [];
        }

        $closeType = T_DOC_COMMENT_CLOSE_TAG;
        $closeTag = (int) $file->findPrevious($closeType, $position - 1, null, false, null, true);

        $commentOpener = (int) ($tokens[$closeTag]['comment_opener'] ?? -1);
        if ($commentOpener < 0) {
            return [];
        }

        $functionLine = (int) ($tokens[$position]['line'] ?? -1);
        $closeLine = (int) ($tokens[$closeTag]['line'] ?? -1);
        if ($closeLine !== ($functionLine - 1)) {
            return [];
        }

        $tags = self::findAllTags($tokens, $commentOpener + 1, $closeTag);

        return self::normalizeTags($tags, $normalizeContent);
    }

    /**
     * @param string $tag
     * @param File $file
     * @param int $position
     * @return list<string>
     */
    public static function tag(string $tag, File $file, int $position): array
    {
        $tagName = '@' . ltrim($tag, '@');

        $tags = self::allTags($file, $position);
        if (!isset($tags[$tagName]) || !$tags[$tagName]) {
            return [];
        }

        return $tags[$tagName];
    }

    /**
     * @param File $file
     * @param int $functionPosition
     * @return array<string, list<string>>
     */
    public static function allParamTypes(File $file, int $functionPosition): array
    {
        $params = self::tag('param', $file, $functionPosition);
        if (!$params) {
            return [];
        }

        $types = [];
        foreach ($params as $param) {
            preg_match('~^([^$]+)\s*(\$\S+)~', trim($param), $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $types[$matches[2]] = self::normalizeTypesString($matches[1]);
            }
        }

        return $types;
    }

    /**
     * @param string $typesString
     * @return list<string>
     */
    public static function normalizeTypesString(string $typesString): array
    {
        $typesString = preg_replace('~\s+~', '', $typesString);

        $splitTypes = explode('|', $typesString ?? '');

        $hasNull = false;

        $normalized = [];
        foreach ($splitTypes as $splitType) {
            if (str_contains($splitType, '&')) {
                $splitType = rtrim(ltrim($splitType, '('), ')');
            } elseif (str_starts_with($splitType, '?')) {
                $splitType = (string) substr($splitType, 1);
                $hasNull = $hasNull || ($splitType !== '');
            }

            if ($splitType === '') {
                continue;
            }

            if (strtolower($splitType) === 'null') {
                $hasNull = true;
                continue;
            }

            $normalized[] = $splitType;
        }

        $ordered = array_values(array_unique($normalized));
        sort($ordered, SORT_STRING);
        if ($hasNull) {
            $ordered[] = 'null';
        }

        return $ordered;
    }

    /**
     * @param array<int, array<string, mixed>> $tokens
     * @param int $start
     * @param int $end
     * @return array<int, array{string, string}>
     */
    private static function findAllTags(array $tokens, int $start, int $end): array
    {
        $tags = [];

        $inTag = false;
        $key = -1;

        for ($i = $start; $i < $end; $i++) {
            $code = $tokens[$i]['code'] ?? null;
            if ($code === T_DOC_COMMENT_STAR) {
                continue;
            }

            $content = (string) ($tokens[$i]['content'] ?? '');

            if ($code === T_DOC_COMMENT_TAG) {
                $inTag = true;
                $key++;
                $tags[$key] = [$content, ''];
                continue;
            }

            if ($inTag) {
                $tags[$key][1] .= $content;
            }
        }

        /** @var array<int, array{string, string}> */
        return $tags;
    }

    /**
     * @param array<array{string, string}> $tags
     * @param bool $normalizeContent
     * @return array<string, list<string>>
     */
    private static function normalizeTags(array $tags, bool $normalizeContent): array
    {
        $normalizedTags = [];

        static $random;
        if (!$random) {
            $random = bin2hex(random_bytes(3));
        }

        foreach ($tags as [$tagName, $tagContent]) {
            if (!isset($normalizedTags[$tagName])) {
                $normalizedTags[$tagName] = [];
            }

            if (!$normalizeContent) {
                $normalizedTags[$tagName][] = $tagContent;
                continue;
            }

            $lines = array_filter(array_map('trim', explode("\n", $tagContent)));
            $normalized = preg_replace('~\s+~', ' ', implode("%LB%{$random}%LB%", $lines)) ?? '';
            $normalizedTags[$tagName][] = trim(str_replace("%LB%{$random}%LB%", "\n", $normalized));
        }

        return $normalizedTags;
    }
}
