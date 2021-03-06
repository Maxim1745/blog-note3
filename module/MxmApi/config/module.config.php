<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmApi;

return [
    'defaults' => [

    ],
    'mxm_api' => [
        'grant_types' => [
            '',
            'client_credentials',
            'authorization_code',
            'password',
            'refresh_token'
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmApi.log',
        ],
        'download' => [
            'path' => __DIR__ . '/../../../data/files/'
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\ApiController::class => Controller\ApiControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\ApiServiceInterface::class => Service\ApiService::class,
            Mapper\MapperInterface::class => Mapper\ZendTableGatewayMapper::class,
        ],
        'factories' => [
            \MxmApi\V1\Rest\Post\PostResource::class => \MxmApi\V1\Rest\Post\PostResourceFactory::class,
            \MxmApi\V1\Rest\Category\CategoryResource::class => \MxmApi\V1\Rest\Category\CategoryResourceFactory::class,
            \MxmApi\V1\Rest\Tag\TagResource::class => \MxmApi\V1\Rest\Tag\TagResourceFactory::class,
            \MxmApi\V1\Rest\User\UserResource::class => \MxmApi\V1\Rest\User\UserResourceFactory::class,
            \MxmApi\V1\Rest\File\FileResource::class => \MxmApi\V1\Rest\File\FileResourceFactory::class,
            Service\ApiService::class => Service\ApiServiceFactory::class,
            Mapper\ZendTableGatewayMapper::class => Mapper\ZendTableGatewayMapperFactory::class,
            Hydrator\ClientFormHydrator::class => Hydrator\ClientFormHydrator\ClientFormHydratorFactory::class,
            Hydrator\ClientMapperHydrator::class => Hydrator\ClientMapperHydrator\ClientMapperHydratorFactory::class,
            Logger::class => Logger\LoggerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\AddClientForm::class => Form\AddClientFormFactory::class
        ]
    ],
    'hydrators' => [
        'factories' => [
            \MxmApi\V1\Rest\User\UserHydrator::class => \MxmApi\V1\Rest\User\UserHydratorFactory::class,
            \MxmApi\V1\Rest\Client\ClientHydrator::class => \MxmApi\V1\Rest\Client\ClientHydratorFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmApi' => __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            'addClient' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/api/add/client',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action' => 'addClient'
                    ],
                ],
            ],
            'listClients' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/list/clients[page/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'page' => 1,
                        'controller' => Controller\ApiController::class,
                        'action' => 'listClients'
                    ],
                ],
            ],
            'listClientsByUser' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/list/clients/user/:id[page/:page]',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'page' => 1,
                        'controller' => Controller\ApiController::class,
                        'action' => 'listClientsByUser'
                    ],
                ],
            ],
            'detailClient' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/detail/client/:client_id',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action' => 'detailClient'
                    ],
                ],
            ],
            'revokeToken' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/revoke/token/:client_id',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action' => 'revokeToken'
                    ],
                ],
            ],
            'deleteClient' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/delete/client/:client_id',
                    'defaults' => [
                        'controller' => Controller\ApiController::class,
                        'action' => 'deleteClient'
                    ],
                ],
            ],
            'mxm-api.rest.post' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/post[/:post_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\Post\\Controller',
                    ],
                ],
            ],
            'mxm-api.rest.user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/user[/:user_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\User\\Controller',
                    ],
                ],
            ],
            'mxm-api.rest.category' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/category[/:category_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\Category\\Controller',
                    ],
                ],
            ],
            'mxm-api.rest.tag' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/tag[/:tag_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\Tag\\Controller',
                    ],
                ],
            ],
            'mxm-api.rest.file' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/file[/:file_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\File\\Controller',
                    ],
                ],
            ],
            'mxm-api.rest.client' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/client[/:client_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\Client\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'api-tools-oauth2' => array(
        'db' => [
            'dsn' => 'mysql:dbname=blog-note;host=localhost',
            'username' => 'root',
            'password' => '',
        ],
        'storage' => 'Laminas\ApiTools\OAuth2\Adapter\PdoAdapter',
        "grant_types" => [
            "client_credentials" => true,
//            "authorization_code" => true,
//            "password" => true,
//            "refresh_token" => true,
//            "jwt" => true
        ],
//        'allow_implicit' => true,
        'access_lifetime' => 60*60*24*1,  //a value in seconds for access tokens lifetime (s*m*h*d)
//        'enforce_state'  => true,
//        'storage_settings' => array(
//           'user_table' => 'users',
//        ),
//         'options' => array(
//            'always_issue_new_refresh_token' => true,
//        ),
    ),
    'api-tools-mvc-auth' => [
        'authentication' => [
            'adapters' => [
                'MxmApi' => [
                    'adapter' => \Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter::class,
                    'storage' => [
                        'adapter' => \PDO::class,
                        'dsn' => 'mysql:dbname=blog-note;host=localhost',
                        'route' => '/oauth',
                        'username' => 'root',
                        'password' => '',
                    ],
                ],
            ],
            'map' => [
                'MxmApi\\V1' => 'oauth2',
            ],
        ],
        'authorization' => [
            'MxmApi\\V1\\Rest\\File\\Controller' => [
                'collection' => [
                    'GET' => true,
                    'POST' => true,
                    'PUT' => true,
                    'PATCH' => true,
                    'DELETE' => true,
                ],
                'entity' => [
                    'GET' => true,
                    'POST' => true,
                    'PUT' => true,
                    'PATCH' => true,
                    'DELETE' => true,
                ],
            ],
        ],
    ],
    'api-tools-versioning' => [
        'uri' => [
            0 => 'mxm-api.rest.post',
            1 => 'mxm-api.rest.user',
            2 => 'mxm-api.rest.category',
            3 => 'mxm-api.rest.tag',
            4 => 'mxm-api.rest.file',
        ],
    ],
    'api-tools-rest' => [
        'MxmApi\\V1\\Rest\\Post\\Controller' => [
            'listener' => \MxmApi\V1\Rest\Post\PostResource::class,
            'route_name' => 'mxm-api.rest.post',
            'route_identifier_name' => 'post_id',
            'collection_name' => 'post',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmBlog\Model\Post::class,
            'collection_class' => \MxmApi\V1\Rest\Post\PostCollection::class,
            'service_name' => 'post',
        ],
        'MxmApi\\V1\\Rest\\User\\Controller' => [
            'listener' => \MxmApi\V1\Rest\User\UserResource::class,
            'route_name' => 'mxm-api.rest.user',
            'route_identifier_name' => 'user_id',
            'collection_name' => 'user',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmApi\V1\Rest\User\UserEntity::class,
            'collection_class' => \MxmApi\V1\Rest\User\UserCollection::class,
            'service_name' => 'user',
        ],
        'MxmApi\\V1\\Rest\\Category\\Controller' => [
            'listener' => \MxmApi\V1\Rest\Category\CategoryResource::class,
            'route_name' => 'mxm-api.rest.category',
            'route_identifier_name' => 'category_id',
            'collection_name' => 'category',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmBlog\Model\Category::class,
            'collection_class' => \MxmApi\V1\Rest\Category\CategoryCollection::class,
            'service_name' => 'category',
        ],
        'MxmApi\\V1\\Rest\\Tag\\Controller' => [
            'listener' => \MxmApi\V1\Rest\Tag\TagResource::class,
            'route_name' => 'mxm-api.rest.tag',
            'route_identifier_name' => 'tag_id',
            'collection_name' => 'tag',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmBlog\Model\Tag::class,
            'collection_class' => \MxmApi\V1\Rest\Tag\TagCollection::class,
            'service_name' => 'tag',
        ],
        'MxmApi\\V1\\Rest\\File\\Controller' => [
            'listener' => \MxmApi\V1\Rest\File\FileResource::class,
            'route_name' => 'mxm-api.rest.file',
            'route_identifier_name' => 'file_id',
            'collection_name' => 'file',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => ['user'],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmFile\Model\File::class,
            'collection_class' => \MxmApi\V1\Rest\File\FileCollection::class,
            'service_name' => 'file',
        ],
        'MxmApi\\V1\\Rest\\Client\\Controller' => [
            'listener' => \MxmApi\V1\Rest\Client\ClientResource::class,
            'route_name' => 'mxm-api.rest.client',
            'route_identifier_name' => 'client_id',
            'collection_name' => 'client',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmApi\Model\Client::class,
            'collection_class' => \MxmApi\V1\Rest\Client\ClientCollection::class,
            'service_name' => 'client',
        ],
    ],
    'api-tools-content-negotiation' => [
        'controllers' => [
            'MxmApi\\V1\\Rest\\Post\\Controller' => 'HalJson',
            'MxmApi\\V1\\Rest\\Category\\Controller' => 'HalJson',
            'MxmApi\\V1\\Rest\\Tag\\Controller' => 'HalJson',
            'MxmApi\\V1\\Rest\\User\\Controller' => 'HalJson',
            'MxmApi\\V1\\Rest\\File\\Controller' => 'HalJson',
            'MxmApi\\V1\\Rest\\Client\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'MxmApi\\V1\\Rest\\Post\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\Category\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\Tag\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\User\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\File\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\Client\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'MxmApi\\V1\\Rest\\Post\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\Category\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\Tag\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\User\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\File\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
                2 => 'multipart/form-data',
            ],
            'MxmApi\\V1\\Rest\\Client\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
                2 => 'multipart/form-data',
            ],
        ],
    ],
    'api-tools-hal' => [
        'metadata_map' => [
            \MxmBlog\Model\Post::class => [
                'entity_identifier_name' => 'id',                       //name of the class property (after serialization) used for the identifier
                'route_name' => 'mxm-api.rest.post',                    //a reference to the route name used to generate `self` relational links for the collection or entity
                'route_identifier_name' => 'post_id',                   //the identifier name used in the route that will represent the entity identifier in the URI path
                'hydrator' => \Laminas\Hydrator\ClassMethodsHydrator::class,
            ],
            \MxmUser\Model\User::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.user',
                'route_identifier_name' => 'user_id',
                'hydrator' => \MxmApi\V1\Rest\User\UserHydrator::class,
            ],
            \MxmBlog\Model\Category::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.category',
                'route_identifier_name' => 'category_id',
                'hydrator' => \Laminas\Hydrator\ClassMethodsHydrator::class,
            ],
            \Laminas\Tag\ItemList::class => [
                //'entity_identifier_name' => 'id',
                //'route_name' => 'mxm-api.rest.tag',
                //'route_identifier_name' => 'tag_id',
                //'hydrator' => \Laminas\Hydrator\ClassMethods::class,
                'is_collection' => true,
            ],
            \MxmBlog\Model\Tag::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.tag',
                'route_identifier_name' => 'tag_id',
                'hydrator' => \Laminas\Hydrator\ClassMethodsHydrator::class,
            ],
            \MxmApi\V1\Rest\Post\PostCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',
                'route_identifier_name' => 'post_id',
                'is_collection' => true,
            ],
            \MxmFile\Model\File::class => [
                'entity_identifier_name' => 'file_id',
                'route_name' => 'mxm-api.rest.file',
                'route_identifier_name' => 'file_id',
                'hydrator' => \Laminas\Hydrator\ClassMethodsHydrator::class,
            ],
            \MxmApi\V1\Rest\File\FileCollection::class => [
                'entity_identifier_name' => 'file_id',
                'route_name' => 'mxm-api.rest.file',
                'route_identifier_name' => 'file_id',
                'is_collection' => true,
            ],
            \MxmApi\Model\Client::class => [
                'entity_identifier_name' => 'client_id',
                'route_name' => 'mxm-api.rest.client',
                'route_identifier_name' => 'client_id',
                'hydrator' => \MxmApi\V1\Rest\Client\ClientHydrator::class,
            ],
        ],
    ],
    'api-tools-content-validation' => [
        'MxmApi\\V1\\Rest\\File\\Controller' => [
            'input_filter' => 'MxmApi\\V1\\Rest\\File\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'MxmApi\\V1\\Rest\\File\\Validator' => [
            0 => [
                'name' => 'file',
                'required' => true,
                'validators' => [
                    0 => [
                        'name' => \Laminas\Validator\File\Extension::class,
                        'options' => [
                            'extension' => [
                                'log', 'jpg', 'jpeg', 'png'
                            ],
                        ],
                    ],
                    1 => [
                        'name'=>'Laminas\Validator\File\Size',
                        'options' => [
                            //'min' => '10kB',
                            'max' => '4MB'
                        ]
                    ],
                ],
                'filters' => [
                    0 => [
                        'name' => \Laminas\Filter\File\RenameUpload::class,
                        'options' => [
                            'randomize' => true,
                            'target' => __DIR__ . '/../../../data/files/file.txt',
                        ],
                    ],
                ],
                'description' => 'file upload',
                'type' => \Laminas\InputFilter\FileInput::class,
                'error_message' => 'file upload fail',
                'field_type' => 'multipart/form-data',
            ],
//            1 => [
//                'name' => 'filename',
//                'required' => true,
//                'filters' => [],
//                'validators' => [],
//                'allow_empty' => false,
//                'continue_if_empty' => false,
//            ],
            1 => [
                'name' => 'description',
                'required' => true,
                'filters' => [
                    0 => [
                        'name' => 'StripTags',
                        'options' => []
                    ],
                    1 => [
                        'name' => 'StringTrim',
                        'options' => []
                    ],
                ],
                'validators' => [
                    0 => [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 250
                        ]
                    ]
                ],
                'allow_empty' => false,
                'continue_if_empty' => false,
            ],

        ],
    ],
];
