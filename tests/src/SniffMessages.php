<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests;

final class SniffMessages
{
    /** @var array<int, string> */
    private array $messages;
    private bool $messagesContainTotal;

    /**
     * @param array<int, string> $warnings
     * @param array<int, string> $errors
     * @param array<int, string>|null $messages
     */
    public function __construct(
        private readonly array $warnings,
        private readonly array $errors,
        ?array $messages = null,
    ) {

        $this->messages = $messages ?? ($errors + $warnings);
        $this->messagesContainTotal = $messages === null;
    }

    /**
     * @return array<int, string>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * @param int $line
     * @return string|null
     */
    public function messageIn(int $line): ?string
    {
        return $this->messages[$line] ?? null;
    }

    /**
     * @return int[]
     */
    public function messageLines(): array
    {
        $messageLines = array_keys($this->messages);
        if ($this->messagesContainTotal) {
            return $messageLines;
        }

        return array_unique(array_merge($this->errorLines(), $this->warningLines(), $messageLines));
    }

    /**
     * @return array<int, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param int $line
     * @return string|null
     */
    public function errorIn(int $line): ?string
    {
        return $this->errors[$line] ?? null;
    }

    /**
     * @return int[]
     */
    public function errorLines(): array
    {
        return array_keys($this->errors);
    }

    /**
     * @return array<int, string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param int $line
     * @return string|null
     */
    public function warningIn(int $line): ?string
    {
        return $this->warnings[$line] ?? null;
    }

    /**
     * @return int[]
     */
    public function warningLines(): array
    {
        return array_keys($this->warnings);
    }

    /**
     * @return int
     */
    public function total(): int
    {
        if ($this->messagesContainTotal) {
            return count($this->messages);
        }

        return count($this->messages) + count($this->errors) + count($this->warnings);
    }
}
