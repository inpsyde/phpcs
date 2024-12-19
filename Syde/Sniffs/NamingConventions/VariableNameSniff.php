<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\Scopes;

final class VariableNameSniff implements Sniff
{
    public const GLOBALS = [
        '$_COOKIE',
        '$_ENV',
        '$_FILES',
        '$_GET',
        '$_POST',
        '$_REQUEST',
        '$_SERVER',
        '$_SESSION',
        '$GLOBALS',
    ];

    public const WP_GLOBALS = [
        '$charset_collate',
        '$current_user',
        '$interim_login',
        '$is_apache',
        '$is_chrome',
        '$is_edge',
        '$is_gecko',
        '$is_IE',
        '$is_IIS',
        '$is_iis7',
        '$is_iphone',
        '$is_lynx',
        '$is_macIE',
        '$is_NS4',
        '$is_opera',
        '$is_safari',
        '$is_winIE',
        '$manifest_version',
        '$required_mysql_version',
        '$required_php_version',
        '$super_admins',
        '$tinymce_version',
    ];

    private const TYPE_CAMEL_CASE = 'camelCase';
    private const TYPE_SNAKE_CASE = 'snake_case';

    public string $checkType = self::TYPE_CAMEL_CASE;
    /** @var string[] */
    public array $ignoredNames = [];
    public bool $ignoreLocalVars = false;
    public bool $ignoreProperties = false;

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_VARIABLE,
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

        $name = (string) $tokens[$stackPtr]['content'];

        if (str_starts_with($name, '$wp_') || str_starts_with($name, '$_wp_')) {
            return;
        }

        if (in_array($name, $this->allIgnored(), true)) {
            return;
        }

        $isCamelCase = $this->checkType() === self::TYPE_CAMEL_CASE;

        $valid = $isCamelCase ? $this->checkCamelCase($name) : $this->checkSnakeCase($name);
        if ($valid) {
            return;
        }

        $isProperty = Scopes::isOOProperty($phpcsFile, $stackPtr);

        if (
            ($isProperty && $this->arePropertiesIgnored())
            || (!$isProperty && $this->areVariablesIgnored())
        ) {
            return;
        }

        $code = $isCamelCase ? 'SnakeCaseVar' : 'CamelCaseVar';

        $case = $isCamelCase ? self::TYPE_CAMEL_CASE : self::TYPE_SNAKE_CASE;

        $phpcsFile->addWarning(
            'Use "%s" for variable name "%s"',
            $stackPtr,
            $code,
            [$case, $name],
        );
    }

    /**
     * @return string
     */
    private function checkType(): string
    {
        if (strtolower(trim($this->checkType)) === self::TYPE_SNAKE_CASE) {
            return self::TYPE_SNAKE_CASE;
        }

        return self::TYPE_CAMEL_CASE;
    }

    /**
     * @return bool
     */
    private function arePropertiesIgnored(): bool
    {
        return filter_var($this->ignoreProperties, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     */
    private function areVariablesIgnored(): bool
    {
        return filter_var($this->ignoreLocalVars, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkCamelCase(string $name): bool
    {
        return (
            preg_match('~^\$[a-z]+(?:[a-zA-Z0-9]+)?$~', $name)
            && !preg_match('~[A-Z]{2,}~', $name)
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkSnakeCase(string $name): bool
    {
        return (bool) preg_match('~^\$[a-z]+(?:[a-z0-9_]+)?$~', $name);
    }

    /**
     * @return string[]
     */
    private function allIgnored(): array
    {
        /** @var list<string> $normalized */
        $normalized = [];
        foreach ($this->ignoredNames as $name) {
            if (is_string($name)) {
                $normalized[] = '$' . ltrim(trim($name), '$');
            }
        }

        $this->ignoredNames = $normalized;

        return array_merge($normalized, self::GLOBALS, self::WP_GLOBALS);
    }
}
