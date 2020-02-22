<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmGame\Form;

use Laminas\Config\Config;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterProviderInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use Laminas\Hydrator\ReflectionHydrator;
use MxmGame\Model\GameInterface;
use Laminas\Hydrator\HydratorInterface;

class GameForm extends Form implements InputFilterProviderInterface {

    protected $translator;
    protected $validatorTranslator;

    public function __construct(
            InputFilter $inputFilter,
            TranslatorInterface $translator,
            ValidatorTranslatorInterface $validatorTranslator,
            GameInterface $game,
            HydratorInterface $hydrator,
            $name = "game",
            $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setHydrator($hydrator);
        $this->setObject($game);

        $this->setAttribute('method', 'post')
                ->setInputFilter($inputFilter);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;

        $this->add([
            'type' => 'text',
            'name' => 'title',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Game title')
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'description',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Game description')
            ]
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'isPublished',
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'label' => $this->translator->translate('Publish'),
                'checked_value' => 1,
                'unchecked_value' => 0,
            ],
        ]);

        $this->add([
            'type' => 'hidden',
            'name' => 'redirect'
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => $this->translator->translate('Send'),
                'class' => 'btn btn-default'
            ]
        ]);
    }

    public function getInputFilterSpecification() {
        return [
            'title' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 80,
                            'translator' => $this->validatorTranslator
                        ]
                    ],
                ]
            ],
            'description' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 2000,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'isPublished' => [
                'required' => false,
                'filters' => [
                    [
                        'name' => 'Int'
                    ],
                ],
            ],
            'redirect' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2048
                        ]
                    ],
                ],
            ],
        ];
    }

}
