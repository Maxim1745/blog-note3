<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmApi\Service;

use Zend\Config\Config;
use Zend\Crypt\Password\Bcrypt;
use MxmUser\Model\UserInterface;
use MxmUser\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\AlreadyExistsException;
use Zend\Validator\Db\RecordExists;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use MxmApi\Mapper\MapperInterface as ApiMapperInterface;
use MxmApi\Model\ClientInterface;
use Zend\Log\Logger;
use MxmApi\Exception\RuntimeException;
use Zend\Http\Response;
use MxmFile\Mapper\MapperInterface as FileMapperInterface;

class ApiService implements ApiServiceInterface
{
    /**
     * @var DateTimeInterface
     */
    protected $datetime;

    /**
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Crypt\Password\Bcrypt
     */
    protected $bcrypt;

    /**
     * @var Zend\Validator\Db\RecordExists
     */
    protected $clientExistsValidator;

    /**
     * @var Zend\Config\Config;
     */
    protected $grantTypes;

    /**
     * @var MxmUser\Mapper\MapperInterface
     */
    protected $userMapper;

    /**
     * @var MxmApi\Mapper\MapperInterface
     */
    protected $apiMapper;

    /**
     * @var MxmFile\Mapper\MapperInterface
     */
    protected $fileMapper;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var Zend\Http\Response
     */
    protected $response;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        RecordExists $clientExistsValidator,
        UserMapperInterface $userMapper,
        ApiMapperInterface $apiMapper,
        FileMapperInterface $fileMapper,
        Config $grantTypes,
        Logger $logger,
        Response $response
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
        $this->clientExistsValidator = $clientExistsValidator;
        $this->userMapper = $userMapper;
        $this->apiMapper = $apiMapper;
        $this->fileMapper = $fileMapper;
        $this->grantTypes = $grantTypes;
        $this->logger = $logger;
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function addClient($data)
    {
        if (! $data instanceof ClientInterface) {
            throw new InvalidArgumentException(sprintf(
                'The data must be ClientInterface object; received "%s"',
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }

        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('add.client.rest');

        if ($this->clientExistsValidator->isValid($data->getClientId())) {
            throw new AlreadyExistsException("Client with " . $data->getClientId() . " already exists");
        }

        $data->setClientSecret($this->bcrypt->create($data->getClientSecret()));
        $data->setUserId($this->authenticationService->getIdentity()->getId());

        return $this->apiMapper->insertClient($data);

    }

    public function findClientById($clientId)
    {
        if (empty($clientId)) {
            throw new InvalidArgumentException('The client_id cannot be empty.');
        }

        $this->authenticationService->checkIdentity();

        $client = $this->apiMapper->findClientById($clientId);

        $user = $this->userMapper->findUserById($client->getUserId());

        $this->authorizationService->checkPermission('find.client.rest', $user);

	return $client;
    }

    public function revokeToken($client)
    {
        if (empty($client)) {
            throw new InvalidArgumentException('The client cannot be empty.');
        }

        $this->authenticationService->checkIdentity();

        $user = $this->userMapper->findUserById($client->getUserId());

        $this->authorizationService->checkPermission('revoke.token.rest', $user);

        return $this->apiMapper->deleteToken($client);
    }

    public function revokeTokens($clients)
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('revoke.tokens.rest');

        if (! is_array($clients)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be array; received "%s"',
                (is_object($clients) ? get_class($clients) : gettype($clients))
            ));
        }

        if (empty($clients)) {
            throw new InvalidArgumentException('The data array is empty');
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof ClientInterface) {
                return $value->getClientId();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of ClientInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $clientIdArray = array_map($func, $clients);

        return $this->apiMapper->deleteTokens($clientIdArray);
    }

    public function findAllClients()
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('find.clients.rest');

        return $this->apiMapper->findAllClients();;
    }

    public function findClientsByUser(UserInterface $user)
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('find.clients.rest', $user);

        return $this->apiMapper->findClientsByUser($user);
    }

    public function deleteClient(ClientInterface $client)
    {
        $this->authenticationService->checkIdentity();

        $user = $this->userMapper->findUserById($client->getUserId());

        $this->authorizationService->checkPermission('delete.client.rest', $user);

        $this->revokeToken($client);

        return $this->apiMapper->deleteClient($client);
    }

    public function deleteClients($clients)
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('delete.clients.rest');

        $this->apiMapper->deleteTokens($clients);
        $this->apiMapper->deleteClients($clients);

        return;
    }

    public function findAllFiles()
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('fetch.files.rest');

        return $this->apiMapper->findAllFiles();
    }

    public function findAllFilesByUser(UserInterface $user = null)
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('fetch.files.rest', $user);

        return $this->apiMapper->findAllFiles($user);
    }

    public function deleteFiles($files)
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('delete.files.rest');

        $this->apiMapper->deleteFiles($files);

        return;
    }

    public function downloadFile($fileId)
    {
         $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('download.file.rest');

        if (! is_string($fileId)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($fileId) ? get_class($fileId) : gettype($fileId))
            ));
        }

        $file = $this->fileMapper->findFileById($fileId);

        $path = $file->getPath();

        if (!is_readable($path)) {
            throw new RuntimeException('Path "' . $path . '" is not readable.');
        }

        if (! is_file($path)) {
            throw new RuntimeException('File "' . $path . '" does not exist.');
        }

        $headers = $this->response->getHeaders();
        $headers->addHeaderLine("Content-type: application/octet-stream");
        $headers->addHeaderLine("Content-Disposition: attachment; filename=\"" . $file->getFilename() . "\"");
        $headers->addHeaderLine("Content-length: " . filesize($path));
//        $headers->addHeaderLine("Cache-control: private"); //use this to open files directly

        $fileContent = file_get_contents($path);
        if ($fileContent !== false) {
            $this->response->setContent($fileContent);
        } else {
            throw new RuntimeException("Can't read file");
        }

        return $this->response;
    }
}