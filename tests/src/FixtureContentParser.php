<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests;

use Error;
use Generator;

/**
 * @phpstan-type Accumulator object{
 *     sniff: string|null,
 *     warnings: array<int, string>,
 *     errors: array<int, string>,
 *     messages: array<int, string>,
 *     properties: object{
 *         start: false|int,
 *         end: false|int,
 *         values: array<string, mixed>,
 *     }&\stdClass,
 *     process: object{
 *          start: false|int,
 *          end: false|int,
 *          content: string,
 *      }&\stdClass,
 * }&\stdClass
 */
class FixtureContentParser
{
    public const TOKEN_SNIFF = '@phpcsSniff';
    public const TOKEN_PROCESS_START = '@phpcsProcessFixtureStart';
    public const TOKEN_PROCESS_END = '@phpcsProcessFixtureEnd';
    public const TOKEN_PROPERTIES_START = '@phpcsSniffPropertiesStart';
    public const TOKEN_PROPERTIES_END = '@phpcsSniffPropertiesEnd';
    private const PATTERN_PROPERTIES_LINE = '~\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=\s*([^;]+);~';
    private const PATTERN_SNIFF = '~^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*|\.)*([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))$~';
    private const PATTERN_SNIFF_LINE = '~' . self::TOKEN_SNIFF . '\s+(\S+)~';
    private const PATTERN_TOKEN_LINE = '~@phpcs(?<type>Warning|Error|Message)On(?<line>This|Next|Previous)Line(?<code>\s+\w+)?~';

    /**
     * @param string $fixturePath
     * @return array{string, SniffMessages, array<string, mixed>|null}
     */
    public function parse(string $fixturePath): array
    {
        if (!is_readable($fixturePath)) {
            throw new Error(sprintf(
                'Fixture file "%s" does not exist or is not readable.',
                $fixturePath,
            ));
        }

        $accumulator = (object) [
            'sniff' => null,
            'warnings' => [],
            'errors' => [],
            'messages' => [],
            'properties' => (object) [
                'start' => false,
                'end' => false,
                'values' => [],
            ],
            'process' => (object) [
                'start' => false,
                'end' => false,
                'content' => '',
            ],
        ];

        foreach ($this->readFile($fixturePath) as $lineNum => $line) {
            $this->readLine($lineNum, $line, $accumulator);
        }

        return $this->processResults($accumulator, $fixturePath);
    }

    /**
     * @param Accumulator $accumulator
     * @param string $fixturePath
     * @return array{string, SniffMessages, array<string, mixed>|null}
     */
    private function processResults(object $accumulator, string $fixturePath): array
    {
        if (!$accumulator->process->content) {
            return [
                $this->checkSniffName($accumulator->sniff),
                new SniffMessages(
                    $accumulator->warnings,
                    $accumulator->errors,
                    $accumulator->messages,
                ),
                $accumulator->properties->values,
            ];
        }

        $results = [];

        /** @var mixed $callback */
        $callback = null;
        // phpcs:ignore Squiz.PHP.Eval.Discouraged
        eval("\$callback = {$accumulator->process->content};");
        if (is_callable($callback)) {
            $results = $callback(
                $accumulator->sniff,
                $accumulator->messages,
                $accumulator->warnings,
                $accumulator->errors,
                $accumulator->properties->values,
            );
        }

        $results = array_values(array_pad(is_array($results) ? $results : [], 5, null));

        /**
         * @var string|null $sniff
         * @var array<int, string>|null $messages
         * @var array<int, string>|null $warnings
         * @var array<int, string>|null $errors
         * @var array<string, mixed>|null $properties
         */
        [$sniff, $messages, $warnings, $errors, $properties] = $results;

        if (
            !is_string($sniff)
            || !is_array($messages)
            || !is_array($warnings)
            || !is_array($errors)
            || !is_array($properties)
        ) {
            throw new Error(sprintf(
                'Process callback for fixture file "%s" (lines #%s:#%s) returned invalid output.',
                $fixturePath,
                (string) $accumulator->process->start,
                (string) $accumulator->process->end,
            ));
        }

        return [
            $this->checkSniffName($sniff),
            new SniffMessages($warnings, $errors, $messages),
            $properties,
        ];
    }

    /**
     * @param string|null $sniff
     * @return string
     */
    private function checkSniffName(?string $sniff): string
    {
        if ($sniff === null) {
            throw new Error('No sniff class found for the test.');
        }

        if (preg_match(self::PATTERN_SNIFF, $sniff)) {
            return $sniff;
        }

        throw new Error(sprintf('Invalid sniff name "%s".', $sniff));
    }

    /**
     * @param string $file
     * @return Generator<int, string>
     */
    private function readFile(string $file): Generator
    {
        $handle = fopen($file, 'rb');
        if ($handle === false) {
            throw new Error(sprintf('Could not open "%s" for reading.', $file));
        }

        $lineNum = 1;

        $line = fgets($handle);
        while ($line !== false) {
            yield $lineNum++ => rtrim($line, "\r\n");
            $line = fgets($handle);
        }

        fclose($handle);
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param Accumulator $accumulator
     * @return void
     */
    private function readLine(int $lineNum, string $line, object $accumulator): void
    {
        (
            $this->readProcessLine($lineNum, $line, $accumulator)
            || $this->readSniffLine($line, $accumulator)
            || $this->readPropertiesLine($lineNum, $line, $accumulator)
            || $this->readTokenLine($lineNum, $line, $accumulator)
        );
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param Accumulator $accumulator
     * @return bool
     */
    private function readProcessLine(int $lineNum, string $line, object $accumulator): bool
    {
        if ($accumulator->process->end !== false) {
            return false;
        }

        if (substr_count($line, self::TOKEN_PROCESS_END)) {
            $accumulator->process->end = $lineNum;
            return true;
        }

        if ($accumulator->process->start !== false) {
            $accumulator->process->content .= $line . PHP_EOL;
            return true;
        }

        if (substr_count($line, self::TOKEN_PROCESS_START)) {
            $accumulator->process->start = $lineNum;
            return true;
        }

        return false;
    }

    /**
     * @param string $line
     * @param Accumulator $accumulator
     * @return bool
     */
    private function readSniffLine(string $line, object $accumulator): bool
    {
        if (isset($accumulator->sniff) && $accumulator->sniff !== '') {
            return false;
        }

        preg_match(self::PATTERN_SNIFF_LINE, $line, $matches);

        if (!isset($matches[1])) {
            return false;
        }

        $accumulator->sniff = $matches[1];

        return true;
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param Accumulator $accumulator
     * @return bool
     */
    private function readPropertiesLine(int $lineNum, string $line, object $accumulator): bool
    {
        if ($accumulator->properties->end !== false) {
            return false;
        }

        if (substr_count($line, self::TOKEN_PROPERTIES_END)) {
            $accumulator->properties->end = $lineNum;
            return true;
        }

        if ($accumulator->properties->start !== false && preg_match(self::PATTERN_PROPERTIES_LINE, $line, $matches)) {
            /** @var mixed $value */
            $value = null;
            // phpcs:ignore Squiz.PHP.Eval.Discouraged
            eval('$value = ' . $matches[2] . ';');
            $accumulator->properties->values[$matches[1]] = $value;
        }

        if (substr_count($line, self::TOKEN_PROPERTIES_START)) {
            $accumulator->properties->start = $lineNum;
            return true;
        }

        return false;
    }

    /**
     * @param int $lineNum
     * @param string $line
     * @param Accumulator $accumulator
     * @return bool
     */
    private function readTokenLine(int $lineNum, string $line, object $accumulator): bool
    {
        preg_match(self::PATTERN_TOKEN_LINE, $line, $matches);
        if (!$matches) {
            return false;
        }

        if ($matches['line'] !== 'This') {
            $lineNum += $matches['line'] === 'Next' ? 1 : -1;
        }

        $code = '';
        if (isset($matches['code'])) {
            $matchedCode = trim($matches['code']);
            if ($matchedCode) {
                $code = $matchedCode;
            }
        }

        switch ($matches['type'] ?? '') {
            case 'Message':
                $accumulator->messages[$lineNum] = $code;
                break;

            case 'Warning':
                $accumulator->warnings[$lineNum] = $code;
                break;

            default:
                $accumulator->errors[$lineNum] = $code;
        }

        return true;
    }
}
