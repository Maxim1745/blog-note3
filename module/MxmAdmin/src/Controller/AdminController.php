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

namespace MxmAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\NotAuthorizedUserException;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmBlog\Exception\NotAuthorizedBlogException;
use MxmBlog\Exception\NotAuthenticatedBlogException;
use MxmUser\Service\UserServiceInterface;
use Zend\Config\Config;
use Zend\Log\Logger;
use MxmApi\Service\ApiServiceInterface;
use MxmApi\Exception\NotAuthorizedException;
use MxmApi\Exception\NotAuthenticatedException;
use MxmBlog\Service\PostServiceInterface;
use MxmAdmin\Service\AdminServiceInterface;
use MxmAdmin\Exception\NotAuthenticatedException as NotAuthenticatedAdminException;
use MxmAdmin\Exception\NotAuthorizedException as NotAuthorizedAdminException;

class AdminController  extends AbstractActionController
{
    /**
     * @var Zend\Config\Config
     */
    protected $config;

	/**
     * @var Zend\Log\Logger
     */
    protected $logger;

	/**
     * @var \MxmUser\Service\UserServiceInterface
     */
    protected $userService;

    /**
     * @var \MxmApi\Service\ApiServiceInterface
     */
    protected $apiService;

	/**
     * @var \MxmAdmin\Service\AdminServiceInterface
     */
    protected $adminService;

    public function __construct(
        UserServiceInterface $userService,
        ApiServiceInterface $apiService,
        PostServiceInterface $postService,
        AdminServiceInterface $adminService,
        Config $config,
        Logger $logger
    ) {
        $this->userService = $userService;
        $this->apiService = $apiService;
        $this->postService = $postService;
        $this->adminService = $adminService;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function manageUsersAction()
    {
        try {
            $paginator = $this->userService->findAllUsers();
        } catch (NotAuthenticatedUserException $e) {
            $redirectUrl = $this->url()->fromRoute('manageUsers', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }
        $this->configurePaginator($paginator);

        return new ViewModel([
            'users' => $paginator,
            'route' => 'manageUsers'
        ]);
    }

    public function manageFilesAction()
    {
        try {
            $paginator = $this->apiService->findAllFiles();
        } catch (NotAuthenticatedException $e) {
            $redirectUrl = $this->url()->fromRoute('manageFiles', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }
        $this->configurePaginator($paginator);

        return new ViewModel([
            'files' => $paginator,
            'route' => 'manageFiles'
        ]);
    }

    public function manageClientsAction()
    {
        try {
            $paginator = $this->apiService->findAllClients();
        } catch (NotAuthenticatedException $e) {
            $redirectUrl = $this->url()->fromRoute('manageClients', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }
        $this->configurePaginator($paginator);

        return new ViewModel([
            'clients' => $paginator,
            'route' => 'manageClients'
        ]);
    }

    public function managePostsAction()
    {
        try {
            $paginator = $this->postService->findAllPosts(false);
        } catch (NotAuthenticatedBlogException $e) {
            $redirectUrl = $this->url()->fromRoute('managePosts', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedBlogException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }
        $this->configurePaginator($paginator);

        return new ViewModel([
            'posts' => $paginator,
            'route' => 'managePosts'
        ]);
    }

    public function manageCategoriesAction()
    {
        try {
            $paginator = $this->postService->findAllCategories();
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        $this->configurePaginator($paginator);

        return new ViewModel([
            'categories' => $paginator,
            'route' => 'manageCategories'
        ]);
    }

    public function manageTagsAction()
    {
        try {
            $paginator = $this->postService->findAllTags();
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        $this->configurePaginator($paginator);

        return new ViewModel([
            'tags' => $paginator,
            'route' => 'manageTags'
        ]);
    }

    public function manageLogsAction()
    {
        try {
            $paginator = $this->adminService->findAllLogs();
        } catch (NotAuthenticatedAdminException $e) {
            $redirectUrl = $this->url()->fromRoute('manageLogs', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedAdminException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        $this->configurePaginator($paginator);

        return new ViewModel([
            'logs' => $paginator,
            'route' => 'manageLogs'
        ]);
    }

    public function downloadLogFileAction()
    {
        $file = $this->params()->fromRoute('file', '');
        //$path = urldecode($this->params()->fromQuery('file', ''));
        try {
            $response = $this->adminService->downloadLogFile($file);
        } catch (NotAuthenticatedAdminException $e) {
            $redirectUrl = $this->url()->fromRoute('downloadLogFile', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedAdminException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        return $response;
    }

    private function configurePaginator(Paginator $paginator)
    {
        $page = (int) $this->params()->fromRoute('page');
        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->config->mxm_admin->adminController->ItemCountPerPage);

        return $this;
    }
}