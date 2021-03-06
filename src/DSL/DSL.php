<?php
declare(strict_types=1);

/*
    Copyright (C) <2020>  <Andy Daniel Navarro Taño>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace ThenLabs\PyramidalTests\DSL;

use ThenLabs\PyramidalTests\Model\Record;
use ThenLabs\PyramidalTests\Model\Test;
use ThenLabs\PyramidalTests\Model\Macro;
use ThenLabs\PyramidalTests\Model\TestCase;
use ThenLabs\PyramidalTests\Exception\MacroNotFoundException;
use ThenLabs\PyramidalTests\Exception\InvalidContextException;
use Closure;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 * @abstract
 */
abstract class DSL
{
    /**
     * @var \Closure
     */
    protected static $incompleteClosure;

    public static function setTestCaseNamespace(string $namespace): void
    {
        Record::setTestCaseNamespace($namespace);
    }

    public static function setTestCaseClass(string $class): void
    {
        Record::setTestCaseClass($class);
    }

    public static function testCase($description, Closure $closure = null): void
    {
        if ($description instanceof Closure) {
            $closure = $description;
            $description = uniqid('AnonymousTestCase');
        }

        $newTestCase = new TestCase($description, $closure);
        $newTestCase->setNamespace(Record::getTestCaseNamespace());
        $newTestCase->setTopTestCaseClass(Record::getTestCaseClass());

        $newTestCaseName = $newTestCase->getName();

        $currentTestCase = Record::getCurrentTestCase();
        if ($currentTestCase instanceof TestCase) {
            $oldTestCase = $currentTestCase->getTestCase($newTestCaseName);

            if (! $oldTestCase) {
                $oldTestCase = $currentTestCase->getTestCaseByDescription($description);
            }

            if ($oldTestCase) {
                $newTestCase = $oldTestCase;
            } else {
                $currentTestCase->addTestCase($newTestCase);
                $newTestCase->setParent($currentTestCase);
                $newTestCase->setNamespace(
                    $currentTestCase->getNamespace() . '\\' . $currentTestCase->getName()
                );
            }
        } else {
            Record::addTestCase($newTestCase);
        }

        $newTestCase->setComments(Record::getComments());

        Record::setCurrentTestCase($newTestCase);
        Record::clearComments();

        call_user_func($closure);

        Record::setCurrentTestCase($currentTestCase);
    }

    public static function setUpBeforeClass(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setSetUpBeforeClass($closure);
        $testCase->setInvokeParentInSetUpBeforeClass($invokeParent);
    }

    public static function setUp(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setSetUp($closure);
        $testCase->setInvokeParentInSetUp($invokeParent);
    }

    public static function test($description, ?Closure $closure = null): void
    {
        if ($description instanceof Closure) {
            $closure = $description;
            $description = uniqid('testAnonymous');
        }

        $currentTestCase = Record::getCurrentTestCase();
        if (! $currentTestCase) {
            $currentTestCase = new TestCase('DefaultTestCase');
            Record::addTestCase($currentTestCase);
            Record::setCurrentTestCase($currentTestCase);
        }

        $test = new Test($description, $closure);
        $test->setComments(Record::getComments());

        Record::clearComments();

        $currentTestCase->addTest($test);
    }

    public static function tearDown(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setTearDown($closure);
        $testCase->setInvokeParentInTearDown($invokeParent);
    }

    public static function tearDownAfterClass(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setTearDownAfterClass($closure);
        $testCase->setInvokeParentInTearDownAfterClass($invokeParent);
    }

    public static function createMethod(string $method, Closure $closure): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->createMethod($method, $closure);
    }

    public static function createStaticMethod(string $method, Closure $closure): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->createStaticMethod($method, $closure);
    }

    public static function createMacro(string $description, Closure $closure): void
    {
        $macro = new Macro($description, $closure);

        $currentTestCase = Record::getCurrentTestCase();

        if (! $currentTestCase) {
            Record::addGlobalMacro($macro);
        } else {
            $currentTestCase->addMacro($macro);
        }
    }

    public static function useMacro(string $description, array $args): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $node = $testCase;
        while ($node) {
            $macro = $node->getMacro($description);
            if ($macro) {
                break;
            }

            $node = $node->getParent();
        }

        if (! $macro) {
            $macro = Record::getGlobalMacro($description);
        }

        if (! $macro) {
            throw new MacroNotFoundException($description);
        }

        call_user_func_array($macro->getClosure(), $args);
    }

    public static function setUpBeforeClassOnce(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setSetUpBeforeClass($closure, true);
        $testCase->setInvokeParentInSetUpBeforeClass($invokeParent);
    }

    public static function tearDownAfterClassOnce(Closure $closure, bool $invokeParent = true): void
    {
        $testCase = Record::getCurrentTestCase();

        if (! $testCase instanceof TestCase) {
            throw new InvalidContextException;
        }

        $testCase->setTearDownAfterClass($closure, true);
        $testCase->setInvokeParentInTearDownAfterClass($invokeParent);
    }

    public static function testIncomplete(string $description): void
    {
        $currentTestCase = Record::getCurrentTestCase();
        if (! $currentTestCase) {
            $currentTestCase = new TestCase('DefaultTestCase');
            Record::addTestCase($currentTestCase);
            Record::setCurrentTestCase($currentTestCase);
        }

        if (! static::$incompleteClosure) {
            static::$incompleteClosure = function () use ($description) {
                $this->markTestIncomplete($description);
            };
        }
        $closure = static::$incompleteClosure;

        $test = new Test($description, $closure);
        $currentTestCase->addTest($test);
    }

    public static function addComment(string $comment): void
    {
        Record::addComment($comment);
    }

    public static function removeTest(string $description): void
    {
        $currentTestCase = Record::getCurrentTestCase();

        $currentTestCase->removeTest($description);
    }

    public static function removeTestCase(string $description): void
    {
        $currentTestCase = Record::getCurrentTestCase();

        $currentTestCase->removeTestCase($description);
    }
}
