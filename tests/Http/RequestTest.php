<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LetsConnect\Common\Tests\Http;

use LetsConnect\Common\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testGetServerName()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('vpn.example', $request->getServerName());
    }

    public function testGetRequestMethod()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('GET', $request->getRequestMethod());
    }

    /**
     * @expectedException \LetsConnect\Common\Http\Exception\HttpException
     * @expectedExceptionMessage missing header "REQUEST_METHOD"
     */
    public function testMissingHeader()
    {
        new Request([]);
    }

    public function testGetPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/foo/bar',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/foo/bar', $request->getPathInfo());
    }

    public function testMissingPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getPathInfo());
    }

    public function testNoPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/index.php',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getPathInfo());
    }

    public function testGetQueryParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?user_id=foo',
                'SCRIPT_NAME' => '/index.php',
            ],
            [
                'user_id' => 'foo',
            ]
        );
        $this->assertSame('foo', $request->getQueryParameter('user_id'));
    }

    /**
     * @expectedException \LetsConnect\Common\Http\Exception\HttpException
     * @expectedExceptionMessage missing required field "user_id"
     */
    public function testGetMissingQueryParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
      ]
        );
        $request->getQueryParameter('user_id');
    }

    public function testDefaultQueryParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('bar', $request->getQueryParameter('user_id', false, 'bar'));
    }

    public function testGetPostParameter()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ],
            [],
            [
                'user_id' => 'foo',
            ]
        );
        $this->assertSame('foo', $request->getPostParameter('user_id'));
    }

    public function testRequireHeader()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_ACCEPT' => 'text/html',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('text/html', $request->requireHeader('HTTP_ACCEPT'));
    }

    public function testOptionalHeader()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_ACCEPT' => 'text/html',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('text/html', $request->optionalHeader('HTTP_ACCEPT'));
        $this->assertNull($request->optionalHeader('HTTP_FOO'));
    }

    public function testRequestUri()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('http://vpn.example/', $request->getUri());
    }

    public function testHttpsRequestUri()
    {
        $request = new Request(
            [
                'REQUEST_SCHEME' => 'https',
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 443,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('https://vpn.example/', $request->getUri());
    }

    public function testNonStandardPortRequestUri()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 8080,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('http://vpn.example:8080/', $request->getUri());
    }

    public function testGetRootSimple()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getRoot());
    }

    public function testGetRootSame()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/connection',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getRoot());
    }

    public function testGetRootPathInfo()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/admin/foo/bar',
                'SCRIPT_NAME' => '/admin/index.php',
            ]
        );
        $this->assertSame('/admin/', $request->getRoot());
    }

    public function testScriptNameInRequestUri()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/admin/index.php/foo/bar',
                'SCRIPT_NAME' => '/admin/index.php',
            ]
        );
        $this->assertSame('/admin/', $request->getRoot());
        $this->assertSame('/foo/bar', $request->getPathInfo());
    }

    public function testGetRootQueryString()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/?foo=bar',
                'SCRIPT_NAME' => '/index.php',
            ]
        );
        $this->assertSame('/', $request->getRoot());
        $this->assertSame('/', $request->getPathInfo());
    }

    public function testGetRootPathInfoQueryString()
    {
        $request = new Request(
            [
                'SERVER_NAME' => 'vpn.example',
                'SERVER_PORT' => 80,
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/admin/foo/bar?foo=bar',
                'SCRIPT_NAME' => '/admin/index.php',
            ]
        );
        $this->assertSame('/admin/', $request->getRoot());
        $this->assertSame('/foo/bar', $request->getPathInfo());
    }
}
