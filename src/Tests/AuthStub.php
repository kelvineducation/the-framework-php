<?php

namespace K\Tests;

class AuthStub implements \K\AuthInterface
{
    public function getAuthUrl()
    {
        return 'http://testing/auth';
    }
    public function getAuthState()
    {
        return 'state';
    }
    public function getUser(string $code)
    {
        return ['1234', 'zach@kelvin.education'];
    }
}
