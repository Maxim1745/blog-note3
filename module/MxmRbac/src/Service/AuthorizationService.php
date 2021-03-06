<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MxmRbac\Service;

use MxmUser\Model\UserInterface;
use Laminas\Permissions\Rbac\AssertionInterface;
use Laminas\Permissions\Rbac\RoleInterface;
use Laminas\Permissions\Rbac\Rbac;
use MxmRbac\Assertion\AssertionPluginManager;
use Laminas\Config\Config;
use Laminas\Validator\InArray;
use Laminas\Log\Logger;
use MxmRbac\Exception\InvalidArgumentException;
use RecursiveIteratorIterator;
use MxmRbac\Exception\NotAuthorizedException;

class AuthorizationService implements AuthorizationServiceInterface
{
    /*
     * @var идентификационные данные (относительно чего идентифицировать)
     */
    protected $identity;

    /*
     * @var Laminas\Permissions\Rbac\Rbac
     */
    protected $rbac;

    /*
     * @var MxmRbac\Assertion\AssertionPluginManager
     */
    protected $assertionPluginManager;

    /**
     * @var Laminas\Config\Config
     */
    protected $config;

    /*
     * var Laminas\Validator\InArray
     */
    protected $inArrayValidator;

    /**
     * @var Laminas\Log\Logger
     */
    protected $logger;

    public function __construct
    (
        Rbac $rbac,
        AssertionPluginManager $assertionPluginManager,
        Config $config,
        InArray $inArrayValidator,
        Logger $logger,
        $identity = null
    ) {
        $this->identity = $identity;
        $this->rbac = $rbac;
        $this->assertionPluginManager = $assertionPluginManager;
        $this->config = $config;
        $this->inArrayValidator = $inArrayValidator;
        $this->logger = $logger;

        if (! $this->config->roles) {
            throw new InvalidArgumentException('There are no roles in config.');
        }

        if (!$this->config->assertions) {
            throw new InvalidArgumentException('There are no assertions in config.');
        }
    }

    /**
     * Returns true if the permission is granted to the current identity. Throw exception if it is not granted.
     *
     * @return true
     * @throws NotAuthorizedException
     */
    public function checkPermission($permission, $content = null)
    {
        if (! $this->isGranted($permission, $content)) {
            throw new NotAuthorizedException('Access denied. Permission ' . $permission . ' is required.');
        }

        return true;
    }

    /**
     * Check if the permission is granted to the current identity
     *
     * @param string $permission
     * @param mixed $content
     *
     * @return bool
     */
    public function isGranted($permission, $content = null)
    {

        if (empty($this->identity)) {
            return false;
        }

        $role = $this->identity->getRole();
        if (!$this->rbac->hasRole($role)) {
            return false;
        }

        if ($this->config->roles->$role->get('no_assertion', false) === true) {             //option 'no_assertion' is present?
            return $this->checkIsGranted($role, $permission);
        }

        $isGranted = true;
        $assertionNames = $this->getAssertionNames($permission);                    //get assertions for given permission

        if (count($assertionNames) < 1) {                                           //assertions for given permission is absent?
            if ($content !== null) {
                throw new InvalidArgumentException("There are no assertions for the permission $permission.");
            }

            $isGranted = $this->checkIsGranted($role, $permission);

        } else {
            if ($content === null) {
                return false;
            }

            foreach ($assertionNames as $assertionName) {
                $assertion = $this->assertionPluginManager->get($assertionName);
                $assertion->setIdentity($this->identity);
                $assertion->setContent($content);

                $isGranted = $isGranted && $this->checkIsGranted($role, $permission, $assertion);

                if ($isGranted !== true) {
                    break;
                }
            }
        }

        return $isGranted;
    }

    /**
     * {@inheritDoc}
     */
    public function matchIdentityRoles($objectOrName)
    {
        if (! is_string($objectOrName) && ! $objectOrName instanceof RoleInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected string or implement \Laminas\Permissions\Rbac\RoleInterface; received "%s"',
                (is_object($objectOrName) ? get_class($objectOrName) : gettype($objectOrName))
            ));
        }

        if (is_object($objectOrName)) {
            $matchRole = $objectOrName->getName();
        } else {
            $matchRole = $objectOrName;
        }

        if (empty($this->identity)) {
            return false;
        }

        $roleName = $this->identity->getRole();
        if (! $this->rbac->hasRole($roleName)) {
            return false;
        }

        $identityRole = $this->rbac->getRole($roleName);

        if ($identityRole->getName() === $matchRole) {
            return true;
        }

        $it = new RecursiveIteratorIterator($identityRole, RecursiveIteratorIterator::CHILD_FIRST); //check children roles
        foreach ($it as $leaf) {
            if ($leaf->getName() === $matchRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function matchUserIds($object)
    {
        if (! $object instanceof UserInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected MxmUser\Model\UserInterface; received "%s"',
                (is_object($object) ? get_class($object) : gettype($object))
            ));
        }

        if (empty($this->identity)) {
            return false;
        }

        return $this->identity->getId() === $object->getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setIdentity(UserInterface $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    private function checkIsGranted($role, $permission, $assertion = null)
    {
	try {
            $isGranted = $this->rbac->isGranted($role, $permission, $assertion);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            $isGranted = false;
        }

	return $isGranted;
    }

    /**
     * Get assertion from module.config
     *
     * @param string $permission
     *
     * @return array $assertions
     */
    private function getAssertionNames($permission)
    {

        $assertions = [];

	foreach ($this->config->assertions as $assertion => $permissions) {

            if ($permissions->permissions) {
		$this->inArrayValidator->setHaystack($permissions->permissions->toArray());

                if ($this->inArrayValidator->isValid($permission)) {
                    $assertions[] = $assertion;
		}
            }
	}

	return $assertions;
    }
}
