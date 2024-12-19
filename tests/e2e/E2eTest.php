<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests;

use JsonException;

/**
 * @psalm-type PhpcsMessagesData = array<string, list<array{source: string, line: int}>>
 */

class E2eTest extends TestCase
{
    private static string $phpCsBinary;
    private static string $testPackagePath;

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $libPath = (string) getenv('LIB_PATH');

        self::$phpCsBinary = $libPath . '/vendor/bin/phpcs';
        self::$testPackagePath = $libPath . '/tests/e2e/test-package';
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testRulesets(): void
    {
        $output = [];

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
        exec(
            sprintf(
                'cd %s && %s',
                self::$testPackagePath,
                self::$phpCsBinary,
            ),
            $output,
        );

        $json = end($output);

        self::assertEquals($this->expectedMessages(), $this->actualMessages((string) $json));
    }

    /**
     * @return array<string, list<array{source: string, line: int}>>
     * @throws JsonException
     */
    private function expectedMessages(): array
    {
        $json = file_get_contents(self::$testPackagePath . '/messages.json');

        return $this->decodeMessages((string) $json);
    }

    /**
     * @param string $json
     * @return array<string, list<array{source: string, line: int}>>
     * @throws JsonException
     */
    private function actualMessages(string $json): array
    {
        /** @var array{files: array<string, PhpcsMessagesData>} $data */
        $data = (array) json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $result = [];
        foreach ($data['files'] as $fileName => $fileData) {
            $baseName = basename($fileName);
            $result[$baseName] = [];
            foreach ($fileData['messages'] as ['source' => $source, 'line' => $line]) {
                $result[$baseName][] = ['source' => $source, 'line' => $line];
            }
        }

        return $result;
    }

    /**
     * @param string $json
     * @return array<string, list<array{source: string, line: int}>>
     * @throws JsonException
     */
    private function decodeMessages(string $json): array
    {
        /** @var PhpcsMessagesData */
        return (array) json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
