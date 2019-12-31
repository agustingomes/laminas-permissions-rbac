<?php

/**
 * @see       https://github.com/laminas/laminas-permissions-rbac for the canonical source repository
 * @copyright https://github.com/laminas/laminas-permissions-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-permissions-rbac/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Permissions\Rbac\Assertion;

use Laminas\Permissions\Rbac;
use PHPUnit\Framework\TestCase;

class CallbackAssertionTest extends TestCase
{
    /**
     * Ensures constructor throws InvalidArgumentException if not callable is provided
     */
    public function testConstructorThrowsExceptionIfNotCallable()
    {
        $this->expectException(Rbac\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid callback provided; not callable');
        new Rbac\Assertion\CallbackAssertion('I am not callable!');
    }

    /**
     * Ensures callback is set in object
     */
    public function testCallbackIsSet()
    {
        $callback   = function () {
        };
        $assert     = new Rbac\Assertion\CallbackAssertion($callback);
        $this->assertAttributeSame($callback, 'callback', $assert);
    }

    /**
     * Ensures assert method provides callback with rbac as argument
     */
    public function testAssertMethodPassRbacToCallback()
    {
        $rbac   = new Rbac\Rbac();
        $that   = $this;
        $assert = new Rbac\Assertion\CallbackAssertion(function ($rbacArg) use ($that, $rbac) {
            $that->assertSame($rbacArg, $rbac);
            return false;
        });
        $foo  = new Rbac\Role('foo');
        $foo->addPermission('can.foo');
        $rbac->addRole($foo);
        $this->assertFalse($rbac->isGranted($foo, 'can.foo', $assert));
    }

    /**
     * Ensures assert method returns callback's function value
     */
    public function testAssertMethod()
    {
        $rbac = new Rbac\Rbac();
        $foo  = new Rbac\Role('foo');
        $bar  = new Rbac\Role('bar');

        $assertRoleMatch = function ($role) {
            return function ($rbac) use ($role) {
                return $role->getName() == 'foo';
            };
        };

        $roleNoMatch = new Rbac\Assertion\CallbackAssertion($assertRoleMatch($bar));
        $roleMatch   = new Rbac\Assertion\CallbackAssertion($assertRoleMatch($foo));

        $foo->addPermission('can.foo');
        $bar->addPermission('can.bar');

        $rbac->addRole($foo);
        $rbac->addRole($bar);

        $this->assertFalse($rbac->isGranted($bar, 'can.bar', $roleNoMatch));
        $this->assertFalse($rbac->isGranted($bar, 'can.foo', $roleNoMatch));
        $this->assertTrue($rbac->isGranted($foo, 'can.foo', $roleMatch));
    }
}
