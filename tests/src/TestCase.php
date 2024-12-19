<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionClass;
use ReflectionException;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param string $content
     * @param string|null $minTestVersion
     * @return File
     * @throws ReflectionException
     */
    protected function factoryFile(string $content, ?string $minTestVersion = null): File
    {
        $args = ($minTestVersion === null)
            ? []
            : ['--runtime-set', 'testVersion', "{$minTestVersion}-"];

        $config = new Config($args, false);
        $config->standards = [];
        $config->extensions = ['php' => 'PHP'];
        $config->setCommandLineValues([]);

        /** @var Ruleset $ruleset */
        $ruleset = (new ReflectionClass(Ruleset::class))->newInstanceWithoutConstructor();

        $file = new DummyFile($content, $ruleset, $config);
        $file->parse();

        return $file;
    }
}
