<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests;

use PHP_CodeSniffer\Files\File;

class SniffMessagesExtractor
{
    /**
     * @param File $file
     */
    public function __construct(private readonly File $file)
    {
    }

    /**
     * @return SniffMessages
     */
    public function extractMessages(): SniffMessages
    {
        $this->file->process();

        return new SniffMessages(
            $this->normalize($this->file->getWarnings()),
            $this->normalize($this->file->getErrors()),
        );
    }

    /**
     * @param array<array-key, mixed> $fileMessages
     * @return array<int, string>
     */
    private function normalize(array $fileMessages): array
    {
        $normalized = [];

        /** @var string[][][] $lineMessages */
        foreach ($fileMessages as $line => $lineMessages) {
            $normalized += $this->normalizeLineMessages((int) $line, $lineMessages);
        }

        return $normalized;
    }

    /**
     * @param int $line
     * @param string[][][] $lineMessages
     * @return array<int, string>
     */
    private function normalizeLineMessages(int $line, array $lineMessages): array
    {
        $normalized = [];

        foreach ($lineMessages as $messages) {
            $message = array_shift($messages);
            $sourceParts = explode('.', ($message['source'] ?? ''));
            $normalized[$line] = end($sourceParts);
        }

        return $normalized;
    }
}
