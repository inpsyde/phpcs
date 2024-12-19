<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class FunctionLengthSniff implements Sniff
{
    public bool $ignoreBlankLines = true;
    public bool $ignoreComments = true;
    public bool $ignoreDocBlocks = true;
    public int $maxLength = 50;

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
        $length = $this->structureLinesCount($phpcsFile, $stackPtr);
        if ($length <= $this->maxLength) {
            return;
        }

        $ignored = $this->ignoredLines();

        $ignoring = $ignored ? sprintf(' (ignoring: %s)', implode(', ', $ignored)) : '';

        $phpcsFile->addError(
            'Function length (%d) exceeds allowed maximum of %d lines%s',
            $stackPtr,
            'TooLong',
            [$length, $this->maxLength, $ignoring],
        );
    }

    /**
     * @param File $file
     * @param int $position
     * @return int
     */
    private function structureLinesCount(File $file, int $position): int
    {
        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $token = $tokens[$position] ?? [];

        if (
            !array_key_exists('scope_opener', $token)
            || !array_key_exists('scope_closer', $token)
        ) {
            return 0;
        }

        $start = (int) $token['scope_opener'];
        $end = (int) $token['scope_closer'];

        $length = (int) $tokens[$end]['line'] - (int) $tokens[$start]['line'];

        if ($length <= $this->maxLength) {
            return $length;
        }

        return $length - $this->collectLinesToExclude($start, $end, $tokens);
    }

    /**
     * @param int $start
     * @param int $end
     * @param array<int, array<string, mixed>> $tokens
     * @return int
     */
    private function collectLinesToExclude(int $start, int $end, array $tokens): int
    {
        $docBlocks = [];
        $linesData = [];

        $skipLines = [$tokens[$start + 1]['line'], $tokens[$end]['line']];
        for ($i = $start + 1; $i < $end - 1; $i++) {
            if (in_array($tokens[$i]['line'], $skipLines, true)) {
                continue;
            }

            $docBlocks = $this->docBlocksData($tokens, $i, $docBlocks);
            $linesData = $this->ignoredLinesData($tokens[$i], $linesData);
        }

        $empty = array_filter(array_column($linesData, 'empty'));
        $onlyComment = array_filter(array_column($linesData, 'only-comment'));

        $toExcludeCount = (int) array_sum($docBlocks);
        if ($this->ignoreBlankLines) {
            $toExcludeCount += count($empty);
        }
        if ($this->ignoreComments) {
            $toExcludeCount += count($onlyComment) - count($empty);
        }

        return $toExcludeCount;
    }

    /**
     * @param array<string, mixed> $token
     * @param array<int, array{empty:bool, only-comment:bool}> $lines
     * @return array<int, array{empty:bool, only-comment:bool}>
     */
    private function ignoredLinesData(array $token, array $lines): array
    {
        $line = (int) $token['line'];
        if (!array_key_exists($line, $lines)) {
            $lines[$line] = ['empty' => true, 'only-comment' => true];
        }

        if (!in_array($token['code'], [T_COMMENT, T_WHITESPACE], true)) {
            $lines[$line]['only-comment'] = false;
        }

        if ($token['code'] !== T_WHITESPACE) {
            $lines[$line]['empty'] = false;
        }

        return $lines;
    }

    /**
     * @param array<int, array<string, mixed>> $tokens
     * @param int $position
     * @param list<int> $docBlocks
     * @return list<int>
     */
    private function docBlocksData(array $tokens, int $position, array $docBlocks): array
    {
        if (
            !$this->ignoreDocBlocks
            || $tokens[$position]['code'] !== T_DOC_COMMENT_OPEN_TAG
        ) {
            return $docBlocks;
        }

        $closer = $tokens[$position]['comment_closer'] ?? null;

        $docBlocks[] = is_numeric($closer)
            ? 1 + ((int) $tokens[(int) $closer]['line'] - (int) $tokens[$position]['line'])
            : 1;

        return $docBlocks;
    }

    /**
     * @return string[]
     */
    private function ignoredLines(): array
    {

        $ignored = [];

        $flags = [
            'ignoreBlankLines' => 'blank lines',
            'ignoreComments' => 'inline comments',
            'ignoreDocBlocks' => 'doc blocks',
        ];

        foreach ($flags as $flag => $type) {
            if (filter_var($this->{$flag}, FILTER_VALIDATE_BOOLEAN)) {
                $ignored[] = $type;
            }
        }

        return $ignored;
    }
}
