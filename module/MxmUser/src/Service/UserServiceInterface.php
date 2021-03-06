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

namespace MxmUser\Service;

use MxmUser\Model\UserInterface;
use Laminas\EventManager\EventManagerAwareInterface;

interface UserServiceInterface extends EventManagerAwareInterface
{
    /**
     * Должен вернуть Paginator
     *
     * @return Laminas\Paginator\Paginator
     * @throws NotAuthenticatedUserException
     * @throw NotAuthorizedException
     */
    public function findAllUsers();

    /**
     * Должен вернуть один объект по id, реализующий UserInterface
     *
     * @param int $id
     * @return UserInterface
     * @throws NotAuthenticatedUserException
     * @throw RecordNotFoundUserException
     * @throw NotAuthorizedException
     */
    public function findUserById($id);

    /**
     * Должен сохранять объект, реализующий UserInterface и возвращать его же.
     *
     * @param  UserInterface $user
     * @return UserInterface
     * @throws AlreadyExistsUserException
     */
    public function insertUser(UserInterface $user);

    /**
     * Должен обновить объект, реализующий UserInterface и возвращать его же.
     *
     * @param  UserInterface $user
     * @return UserInterface
     *
     * @throws NotAuthenticatedUserException
     * @throw NotAuthorizedException
     */
    public function updateUser(UserInterface $user);

    /**
     * Должен удалить полученный объект, реализующий UserInterface
     * и вернуть true (если удалено) или false (если неудача).
     *
     * @param  UserInterface $user
     *
     * @return bool
     * @throw NotAuthenticatedUserException
     * @throw NotAuthorizedException
     */
    public function deleteUser(UserInterface $user);

    /**
     * Изменить пароль
     *
     * @param string $oldPassword
     * @param string $newPassword
     *
     * @return UserInterface
     *
     * @throws NotAuthenticatedUserException
     * @throws InvalidArgumentUserException
     * @throw NotAuthorizedException
     * @throws InvalidPasswordUserException Если текущий пароль введенный пользователем не совпадает с текущим паролем в БД.
     */
    public function editPassword($oldPassword, $newPassword);

    /**
     * Установить новый пароль после сброса старого.
     *
     * @param string $newPassword
     * @param string $token
     *
     * @return UserInterface
     *
     * @throws RecordNotFoundUserException
     * @throws InvalidArgumentUserException
     * @throws ExpiredUserException
     */
    public function setPassword($newPassword, $token);

    /**
     * Изменить email
     *
     * @param string $email
     * @param string $password
     *
     * @return UserInterface
     *
     * @throws NotAuthenticatedUserException
     * @throws InvalidArgumentUserException
     * @throw NotAuthorizedException
     * @throws InvalidPasswordUserException Если текущий пароль введенный пользователем не совпадает с текущим паролем в БД.
     */
    public function editEmail($email, $password);

    /**
     *
     * @param string $email
     * @param string $password
     *
     * @return Laminas\Authentication\Result
     * @throws InvalidArgumentUserException
     * @throws RuntimeUserException
     * @throws RecordNotFoundUserException
     * @throws ExpiredUserException
     */
    public function loginUser($email, $password);

    /**
     * @return $this
     * @throws RuntimeUserException
     */
    public function logoutUser();

    /**
     * Сбросить пароль (если забыли).
     *
     * @param string $email
     *
     * @return $this
     *
     * @throws RecordNotFoundUserException
     * @throws InvalidArgumentUserException
     */
    public function resetPassword($email);

    /**
     * Подтверждение email пользователем по ссылке, отправленной
     * ему на email. Если у токена не истек срок действия, то
     * устанавливаем email как верифицированный.
     *
     * @param string $token
     *
     * @return UserInterface
     *
     * @throws RecordNotFoundUserException
     * @throws InvalidArgumentUserException
     * @throws ExpiredUserException
     */
	public function confirmEmail($token);
}