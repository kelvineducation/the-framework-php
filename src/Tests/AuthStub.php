<?php

namespace The\Tests;

class AuthStub implements \The\AuthInterface
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
