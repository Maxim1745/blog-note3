<?php
namespace MxmUser;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'controllers' => [
        'factories' => [
            Controller\ListController::class => Factory\Controller\ListControllerFactory::class,
            Controller\WriteController::class => Factory\Controller\WriteControllerFactory::class,
            Controller\DeleteController::class => Factory\Controller\DeleteControllerFactory::class,
            Controller\AuthController::class => Factory\Controller\AuthControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\UserServiceInterface::class => Service\UserService::class,
            Service\DateTimeInterface::class => Service\DateTime::class,
            Mapper\MapperInterface::class => Mapper\ZendDbSqlMapper::class,
            Model\UserInterface::class => Model\User::class,
        ],
        'factories' => [
            Service\UserService::class => Factory\Service\UserServiceFactory::class,
            Service\DateTime::class => Factory\Service\DateTimeFactory::class,
            Mapper\ZendDbSqlMapper::class => Factory\Mapper\ZendDbSqlMapperFactory::class,
            Model\User::class => Factory\Model\UserFactory::class,
            \Zend\Db\Adapter\Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            Hydrator\User\UserHydrator::class => Factory\Hydrator\UserHydratorFactory::class,
            Hydrator\User\DatesHydrator::class => Factory\Hydrator\DatesHydratorFactory::class,
            Hydrator\User\TimezoneHydrator::class => Factory\Hydrator\TimezoneHydratorFactory::class,
            //Zend\Validator\Date::class => Factory\Validator\DateValidatorFactory::class,
            //Zend\Hydrator\Aggregate\AggregateHydrator::class => Factory\Hydrator\AggregateHydratorFactory::class,
            AggregateHydrator::class => Factory\Hydrator\AggregateHydratorFactory::class,
            Date::class => Factory\Validator\DateValidatorFactory::class,
            //Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            
        ],
        'invokables' => [
            //Hydrator\TimezoneHydrator::class => InvokableFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\UserForm::class => Factory\Form\UserFormFactory::class,
            Form\UserFieldset::class => Factory\Form\UserFieldsetFactory::class,
            Form\TimezoneFieldset::class => Factory\Form\TimezoneFieldsetFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
            'login' => [
                'type'    => 'Literal',
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/login',
                    'defaults' => [
                        'controller'    => Controller\AuthController::class,
                        'action'        => 'login',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],
            'listUsers' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/users[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'listUsers'
                    ],
                ],
            ],
            'detailUser' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/detail/user/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'detailUser'
                    ],
                ],
            ],
            'addUser' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/add/user',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'addUser'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmUser' => __DIR__ . '/../view',
        ],
    ],
];