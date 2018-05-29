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

namespace MxmAdmin\Service;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmAdmin\Exception\NotAuthorizedException;
use MxmAdmin\Exception\RuntimeException;
use Zend\Http\Response;
use MxmAdmin\Exception\InvalidArgumentException;
use Zend\Filter\StaticFilter;
use Zend\Stdlib\ErrorHandler;

class AdminService implements AdminServiceInterface
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
     * @var Zend\Config\Config;
     */
    protected $config;

    /**
     * @var Zend\Http\Response
     */
    protected $response;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Response $response,
        Config $config
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->response = $response;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllLogs()
    {
        $this->authenticationService->checkIdentity();

        if (! $this->authorizationService->isGranted('find.logs')) {
            throw new NotAuthorizedException('Access denied. Permission "find.logs" is required.');
        }

        //$dir = __DIR__ . '/../../../../data/logs/';
        $dir = $this->config->mxm_admin->logs->path;

        if (! is_dir($dir)) {
            throw new RuntimeException($dir . ' is not directory.');
        }

        if (! $dirHandle = opendir($dir)) {
            throw new RuntimeException('Can not open directory ' . $dir . '.');
        }

        $files = [];

        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $dir . $file;

            $files[] = [
                'file' => $file,
                'size' => filesize($filePath),
                'date' => filemtime($filePath),
                'type' => filetype($filePath),
                'path' => $filePath
            ];
        }

        closedir($dirHandle);

        $paginator = new Paginator(new ArrayAdapter($files));

        return $paginator;
    }

    public function downloadLogFile($file)
    {
        $this->authenticationService->checkIdentity();

        if (! $this->authorizationService->isGranted('download.log')) {
            throw new NotAuthorizedException('Access denied. Permission "download.log" is required.');
        }

        if (! is_string($file)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($file) ? get_class($file) : gettype($file))
            ));
        }

        $path = $this->config->mxm_admin->logs->path . StaticFilter::execute($file, 'Zend\Filter\BaseName');

        if (!is_readable($path)) {
            throw new RuntimeException('Path "' . $path . '" is not readable.');
        }

        if (! is_file($path)) {
            throw new RuntimeException('File "' . $path . '" does not exist.');
        }

        $headers = $this->response->getHeaders();
        $headers->addHeaderLine("Content-type: application/octet-stream");
        $headers->addHeaderLine("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
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

    public function deleteLogs($files)
    {
        $this->authenticationService->checkIdentity();

        if (! $this->authorizationService->isGranted('delete.logs')) {
            throw new NotAuthorizedException('Access denied. Permission "delete.logs" is required.');
        }

        if (! is_string($files) && ! is_array($files)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string or array; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        if (is_string($files)) {
            $files = explode(' ', $files);
        }
//        \Zend\Debug\Debug::dump($files);
        foreach ($files as $file) {
            $path = $this->config->mxm_admin->logs->path . StaticFilter::execute($file, 'Zend\Filter\BaseName');
//\Zend\Debug\Debug::dump($path);
//            $path = 'C:\xampp\htdocs\blog-note3\data\logs\MxmRbac.log';

            if (!is_readable($path)) {
                throw new RuntimeException('Path "' . $path . '" is not readable.');
            }

            if (! is_file($path)) {
                throw new RuntimeException('File "' . $path . '" does not exist.');
            }
//chmod($path, 0777);
//            \Zend\Debug\Debug::dump($path);
            //ErrorHandler::start(E_WARNING);
            unlink($path);
            //ErrorHandler::stop();
        }

        return;
    }
}