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

namespace MxmBlog\Factory\Hydrator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Hydrator\Aggregate\AggregateHydrator;
use MxmBlog\Hydrator\PostMapperHydrator\TagsHydrator;
use MxmBlog\Hydrator\PostMapperHydrator\CategoryHydrator;
use MxmBlog\Hydrator\PostMapperHydrator\PostHydrator;
use MxmBlog\Hydrator\PostMapperHydrator\DatesHydrator;
use MxmBlog\Hydrator\PostMapperHydrator\UserHydrator;
use MxmBlog\Model\CategoryInterface;
use MxmUser\Model\UserInterface;
use Laminas\Config\Config;
use MxmBlog\Model\TagInterface;
use Laminas\Tag\ItemList;
use MxmBlog\Date;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;

class PostMapperHydratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = new Config($container->get('config'));

        $postHydrator = new PostHydrator();

        $category = $container->get(CategoryInterface::class);
        $categoryHydrator = new CategoryHydrator($category);

        $item = $container->get(TagInterface::class);
        $itemList = new ItemList();
        $tagsHydrator = new TagsHydrator($item, $itemList);

        $datetime = $container->get('datetime');
        $dateValidator = $container->get(Date::class);
        $datesHydrator = new DatesHydrator($datetime, $dateValidator, $config);

        $user = $container->get(UserInterface::class);
        $userMapper = $container->get(UserMapperInterface::class);
        $userHydrator = new UserHydrator($userMapper, $user);

        $aggregatehydrator = new AggregateHydrator();
        $aggregatehydrator->setEventManager($container->get('EventManager'));
        $aggregatehydrator->add($postHydrator);
        $aggregatehydrator->add($categoryHydrator);
        $aggregatehydrator->add($tagsHydrator);
        $aggregatehydrator->add($datesHydrator);
        $aggregatehydrator->add($userHydrator);

        return $aggregatehydrator;
    }
}