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

namespace MxmUser\Model;

use \DateTimeInterface;
use \DateTimeZone;

class User implements UserInterface
{
    /**
     * @var int ID
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $username;
    
    protected $email;
    
    protected $password;
    
    protected $passwordSalt;
    
    protected $role;
    
    protected $timezone;
    
    protected $created;

    /**
     *  {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setUsername($username)
    {
        $this->username = $username;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setPassword($password)
    {
        $this->password = $password;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getPasswordSalt()
    {
        return $this->passwordSalt;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setPasswordSalt($passwordSalt)
    {
        $this->passwordSalt = $passwordSalt;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setRole($role)
    {
        $this->role = $role;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setTimezone(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
        
        return $this;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
     *  {@inheritDoc}
     */
    public function setCreated(DateTimeInterface $created)
    {
        $this->created = $created;
        
        return $this;
    }
}