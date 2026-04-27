<?php

namespace Tests\Unit;

use App\Core\Request;

class RequestTest
{
    public function testGetReturnsDefaultWhenKeyNotExists(): void
    {
        $_GET = [];
        $request = new Request();
        $result = $request->get('nonexistent', 'default');
        assert($result === 'default', 'Expected get() to return default value');
    }

    public function testGetReturnsValueWhenKeyExists(): void
    {
        $_GET = ['key' => 'value'];
        $request = new Request();
        $result = $request->get('key');
        assert($result === 'value', 'Expected get() to return the value');
    }

    public function testHasReturnsTrueWhenKeyExists(): void
    {
        $_GET = ['key' => 'value'];
        $request = new Request();
        $result = $request->has('key');
        assert($result === true, 'Expected has() to return true');
    }

    public function testHasReturnsFalseWhenKeyNotExists(): void
    {
        $_GET = [];
        $request = new Request();
        $result = $request->has('key');
        assert($result === false, 'Expected has() to return false');
    }

    public function testGetMethodReturnsRequestMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        $result = $request->getMethod();
        assert($result === 'POST', 'Expected getMethod() to return POST');
    }

    public function testIsPostReturnsTrueForPostRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        $result = $request->isPost();
        assert($result === true, 'Expected isPost() to return true');
    }

    public function testIsGetReturnsTrueForGetRequest(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        $result = $request->isGet();
        assert($result === true, 'Expected isGet() to return true');
    }
}
