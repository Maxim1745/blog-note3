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

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\NotAuthorizedUserException;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmBlog\Exception\NotAuthorizedBlogException;
use MxmBlog\Exception\NotAuthenticatedBlogException;
use MxmUser\Service\UserServiceInterface;
use Laminas\Config\Config;
use Laminas\Log\Logger;
use MxmApi\Service\ApiServiceInterface;
use MxmApi\Exception\NotAuthorizedException;
use MxmApi\Exception\NotAuthenticatedException;
use MxmBlog\Service\PostServiceInterface;
use MxmAdmin\Service\AdminServiceInterface;
use MxmAdmin\Exception\NotAuthenticatedException as NotAuthenticatedAdminException;
use MxmAdmin\Exception\NotAuthorizedException as NotAuthorizedAdminException;
use Zend\i18n\Translator\TranslatorInterface;
use MxmFile\Service\FileServiceInterface;

class AdminController  extends AbstractActionController
{
    /**
     * @var Laminas\Config\Config
     */
    protected $config;

    /**
     * @var Laminas\Log\Logger
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

    /**
     * @var \MxmFile\Service\FileServiceInterface
     */
    protected $fileService;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(
        UserServiceInterface $userService,
        ApiServiceInterface $apiService,
        PostServiceInterface $postService,
        AdminServiceInterface $adminService,
        FileServiceInterface $fileService,
        Config $config,
        Logger $logger,
        TranslatorInterface $translator
    ) {
        $this->userService = $userService;
        $this->apiService = $apiService;
        $this->postService = $postService;
        $this->adminService = $adminService;
        $this->fileService = $fileService;
        $this->config = $config;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function indexAction()
    {
        $allUsersCount = $this->userService->findAllUsers();
        $adminCount = $this->userService->findUsersByRole('admin');
        $moderatorCount = $this->userService->findUsersByRole('moderator');
        $authorCount = $this->userService->findUsersByRole('author');
        $userCount = $this->userService->findUsersByRole('user');
        $fileCount = $this->fileService->findAllFiles();
        $clientCount = $this->apiService->findAllClients();
        $postCount = $this->postService->findAllPosts(false);
        $categoryCount = $this->postService->findAllCategories();
        $tagCount = $this->postService->findAllTags();
        $logCount = $this->adminService->findAllLogs();

        return new ViewModel([
            'allUsers' => $allUsersCount,
            'admins' => $adminCount,
            'moderators' => $moderatorCount,
            'authors' => $authorCount,
            'users' => $userCount,
            'files' => $fileCount,
            'clients' => $clientCount,
            'posts' => $postCount,
            'categories' => $categoryCount,
            'tags' => $tagCount,
            'logs' => $logCount,
        ]);
    }

    public function manageUsersAction()
    {
        $paginator = $this->userService->findAllUsers();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'users' => $paginator,
            'route' => 'manageUsers'
        ]);
    }

    public function manageFilesAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $action = $request->getPost('action', $this->translator->translate('No'));
            if ($action === $this->translator->translate('Delete')) {
                try {
                    $this->fileService->deleteFiles($request->getPost('checkbox', []));
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot delete files.',
                        'route' => 'manageFiles'
                    ]);
                    $model->setTemplate('mxm-admin/admin/error');

                    return $model;
                }
            }
        }

        $paginator = $this->fileService->findAllFiles();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'files' => $paginator,
            'route' => 'manageFiles'
        ]);
    }

    public function manageClientsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $action = $request->getPost('action', $this->translator->translate('No'));
            if ($action === $this->translator->translate('Delete')) {
                try {
                    $this->apiService->deleteClients($request->getPost('checkbox', []));
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot delete clients.',
                        'route' => 'manageClients'
                    ]);
                    $model->setTemplate('mxm-admin/admin/error');

                    return $model;
                }
            }
        }

        $paginator = $this->apiService->findAllClients();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'clients' => $paginator,
            'route' => 'manageClients'
        ]);
    }

    public function managePostsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $action = $request->getPost('action', $this->translator->translate('No'));
            if ($action === $this->translator->translate('Delete')) {
                try {
                    $this->postService->deletePosts($request->getPost('checkbox', []));
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot delete posts.',
                        'route' => 'managePosts'
                    ]);
                    $model->setTemplate('mxm-admin/admin/error');

                    return $model;
                }
            }
        }

        $paginator = $this->postService->findAllPosts(false);
        $this->configurePaginator($paginator);

        return new ViewModel([
            'posts' => $paginator,
            'route' => 'managePosts'
        ]);
    }

    public function manageCategoriesAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $action = $request->getPost('action', $this->translator->translate('No'));
            if ($action === $this->translator->translate('Delete')) {
                try {
                    $this->postService->deleteCategories($request->getPost('checkbox', []));
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot delete categories.',
                        'route' => 'manageCategories'
                    ]);
                    $model->setTemplate('mxm-admin/admin/error');

                    return $model;
                }
            }
        }

        $paginator = $this->postService->findAllCategories();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'categories' => $paginator,
            'route' => 'manageCategories'
        ]);
    }

    public function manageTagsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $action = $request->getPost('action', $this->translator->translate('No'));
            if ($action === $this->translator->translate('Delete')) {
                try {
                    $this->postService->deleteTags($request->getPost('checkbox', []));
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot delete tags.',
                        'route' => 'manageTags'
                    ]);
                    $model->setTemplate('mxm-admin/admin/error');

                    return $model;
                }
            }
        }

        $paginator = $this->postService->findAllTags();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'tags' => $paginator,
            'route' => 'manageTags'
        ]);
    }

    public function manageLogsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $action = $request->getPost('action', $this->translator->translate('No'));
            if ($action === $this->translator->translate('Delete')) {
                try {
                    $this->adminService->deleteLogs($request->getPost('checkbox', []));
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot delete logs.',
                        'route' => 'manageLogs'
                    ]);
                    $model->setTemplate('mxm-admin/admin/error');

                    return $model;
                }
            }
        }

        $paginator = $this->adminService->findAllLogs();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'logs' => $paginator,
            'route' => 'manageLogs'
        ]);
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
