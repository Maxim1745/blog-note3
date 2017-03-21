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

namespace MxmUser\Form;

use \DateTimeZone;
use Zend\Hydrator\HydratorInterface;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class TimebeltFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(
        DateTimeZone $timezone,
        HydratorInterface $hydrator,
        $name = "timebelt",
        $options = array()
    ) {
        parent::__construct($name, $options);
        
        $timezones = $timezone::listIdentifiers(\DateTimeZone::PER_COUNTRY, 'RU'); //TODO move to factory
        
        $this->setHydrator($hydrator);
        $this->setObject($timezone);
        
        $this->add(array(
            'name'=>'timezoneId',
            'type' => 'Zend\Form\Element\Select',
            'attributes'=>array(
                'type'=>'select',
                'required' => 'required',
                'class' => 'form-control',
            ),
            'options'=>array(
                'label'=>'Timezone',
                //'disable_inarray_validator' => true,
                'value_options' => $timezones,
            ),
        ));
        
    }
    
    /**
     * Should return an array specification compatible with
     * {@link ZendInputFilterFactory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'timezoneId' => array(
                'filters'=>array(
                    array(
                        'name' => 'Int'
                    ),
                ),
            ),
        );
    }
}