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

use MxmUser\Form\EditEmailForm;
use Zend\InputFilter\InputFilter;

class EditEmailFormTest extends \PHPUnit_Framework_TestCase
{
    private $form;
    private $data;

    public function setUp()
    {
        $this->data = array(
            'id' => '',
            'newEmail' => '',
            'confirmEmail' => '',
            'password' => ''
        );

        $this->form = new EditEmailForm(new InputFilter());

        parent::setUp();
    }



    public function testEmptyValues()
    {
        $form = $this->form;
        $data = $this->data;

        $this->assertFalse($form->setData($data)->isValid());

        $data['id'] = 1;
        $this->assertFalse($form->setData($data)->isValid());

        $data['newEmail'] = 'Email@testmail.ru';
        $this->assertFalse($form->setData($data)->isValid());

	$data['confirmEmail'] = 'Email@testmail.ru';
        $this->assertFalse($form->setData($data)->isValid());

        $data['password'] = 'password';
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testNewEmailElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
        $data['confirmEmail'] = 'Email@testmail.ru';
	$data['password'] = 'password';

        $data['newEmail'] = "test";
        $this->assertFalse($form->setData($data)->isValid());

        $data['newEmail'] = 'Email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testConfirmEmailElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
        $data['newEmail'] = 'Email@testmail.ru';
	$data['password'] = 'password';

        $data['confirmEmail'] = "test";
        $this->assertFalse($form->setData($data)->isValid());

        $data['confirmEmail'] = 'Email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testIdenticalEmails()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
	$data['password'] = 'password';

        $data['confirmEmail'] = 'Email@testmail.ru';
	$data['newEmail'] = 'newEmail@testmail.ru';
        $this->assertFalse($form->setData($data)->isValid());

	$data['newEmail'] = 'Email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testPasswordElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
        $data['newEmail'] = 'Email@testmail.ru';
	$data['confirmEmail'] = 'Email@testmail.ru';


        $data251 = '12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901';
        $data['password'] = $data251;
	$this->assertFalse($form->setData($data)->isValid());

        $data250 = '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $data['password'] = $data250;
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testStringTrim()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
	$data['newEmail'] = ' Email@testmail.ru ';
        $data['confirmEmail'] = ' Email@testmail.ru ';
	$data['password'] = ' password ';

	$form->setData($data)->isValid();
        $validatedData = $form->getData();

	$this->assertSame('Email@testmail.ru', $validatedData['newEmail']);
	$this->assertSame('Email@testmail.ru', $validatedData['confirmEmail']);
	$this->assertSame('password', $validatedData['password']);
    }
}