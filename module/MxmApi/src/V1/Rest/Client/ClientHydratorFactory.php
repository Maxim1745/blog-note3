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

namespace MxmApi\V1\Rest\Client;

use Laminas\Hydrator\ClassMethodsHydrator as ClassMethods;
use Laminas\Hydrator\Filter\MethodMatchFilter;
use Laminas\Hydrator\Filter\FilterComposite;
use Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy;

class ClientHydratorFactory
{
    public function __invoke($services)
    {
        $hydrator = new ClassMethods(false);
        $hydrator->setNamingStrategy(new UnderscoreNamingStrategy());
        $filters = [
            'clientSecret' => new MethodMatchFilter('getClientSecret'),
            'grantTypes' => new MethodMatchFilter('getGrantTypes'),
            'scope' => new MethodMatchFilter('getScope'),
            'userId' => new MethodMatchFilter('getUserId'),
        ];

        $composite = new FilterComposite([], $filters);

        $hydrator->addFilter('excludes', $composite, FilterComposite::CONDITION_AND);

        return $hydrator;
    }
}
