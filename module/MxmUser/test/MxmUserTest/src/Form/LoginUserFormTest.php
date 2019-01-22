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

namespace MxmUserTest\Form;

use MxmUser\Form\LoginUserForm;
use Zend\InputFilter\InputFilter;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class LoginUserFormTest extends \PHPUnit\Framework\TestCase
{
    private $form;
    private $data;
    private $csrfValidator;
    private $translator;
    private $validatorTranslator;
    
    public function setUp()
    {
        $this->data = array(
            'email' => '',
            'password' => '',
            'redirect' => '',
            'login_csrf' => ''
        );

        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->validatorTranslator = $this->prophesize(ValidatorTranslatorInterface::class);

        $this->form = new LoginUserForm(new InputFilter(), $this->translator->reveal(), $this->validatorTranslator->reveal());
        $csrf = $this->form->get('login_csrf')->getValue();
        $this->data['login_csrf'] = $csrf;

        parent::setUp();
    }

    public function testEmptyValues()
    {
        $form = $this->form;
        $data = $this->data;



        $this->assertFalse($form->setData($data)->isValid());

        $data['email'] = 'qwer5697@google.com';
        $this->assertFalse($form->setData($data)->isValid());

        $data['password'] = 'password';
        $this->assertTrue($form->setData($data)->isValid());

        $data['redirect'] = 'redirect';
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testEmailElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data['password'] = 'password';

        $data['email'] = "test";
        $this->assertFalse($form->setData($data)->isValid());

        $data['email'] = 'Email@mail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testPasswordElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data['email'] = 'Email@mail.ru';

        $data36 = '123456789012345678901234567890123456';
        $data['password'] = $data36;
        $this->assertFalse($form->setData($data)->isValid());

        $data35 = '12345678901234567890123456789012345';
        $data['password'] = $data35;
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testRedirectElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data2049 = '123456789012345678901234567890123456789012345678912345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $data2048 = '12345678901234567890123456789012345678901234567891234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789';

        $data['email'] = 'Email@mail.ru';
        $data['password'] = '12345678901234567890123456789012345';

        $data['redirect'] = $data2049;
        $this->assertFalse($form->setData($data)->isValid());

        $data['redirect'] = $data2048;
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testStringTrim()
    {
        $form = $this->form;
        $data = $this->data;

        $data['email'] = ' Email@mail.ru ';
        $data['password'] = ' redirect ';
        $data['redirect'] = ' redirect ';

        $form->setData($data)->isValid();
        $validatedData = $form->getData();

        $this->assertSame('Email@mail.ru', $validatedData['email']);
        $this->assertSame('redirect', $validatedData['password']);
        $this->assertSame('redirect', $validatedData['redirect']);
    }
}