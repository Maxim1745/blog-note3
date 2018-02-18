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

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class LoginUserForm extends Form implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;

    public function __construct(
        InputFilter $inputFilter,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        $name = "login_user",
        $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post')
            ->setInputFilter($inputFilter);

        $this->setAttribute('class', 'form-signin');

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;

        $this->add([
            'type' => 'text',
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Email',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Email'),
                'label_attributes' => [
                    'class' => 'sr-only',
                ]
            ]
        ]);

        $this->add([
            'type' => 'password',
            'name' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Password',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Password'),
                'label_attributes' => [
                    'class' => 'sr-only',
                ]
            ]
        ]);

        $this->add([
            'type' => 'csrf',
            'name' => 'login_csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
            ],
        ]);

        $this->add([
            'type'  => 'hidden',
            'name' => 'redirect'
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'class' => 'btn btn-default',
                'value' => $this->translator->translate('Send')
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'email' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck' => false,
                            'translator' => $this->validatorTranslator
                        ],
                    ],
                ]
            ],
            'password' => [
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
                            'max' => 35,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'redirect' => [
                'required' => false,
                'filters'  => [
                    ['name'=>'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
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