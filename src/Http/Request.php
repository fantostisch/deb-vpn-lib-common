<?php
/**
 *  Copyright (C) 2016 SURFnet.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SURFnet\VPN\Common\Http;

use SURFnet\VPN\Common\Http\Exception\HttpException;

class Request
{
    /** @var array */
    private $serverData;

    /** @var array */
    private $getData;

    /** @var array */
    private $postData;

    public function __construct(array $serverData, array $getData = [], array $postData = [])
    {
        $requiredHeaders = [
            'REQUEST_METHOD',
            'SERVER_NAME',
            'SERVER_PORT',
            'REQUEST_URI',
        ];

        foreach ($requiredHeaders as $key) {
            if (!array_key_exists($key, $serverData)) {
                // this indicates something wrong with the interaction between
                // the web server and PHP, these headers MUST always be available
                throw new HttpException(sprintf('missing header "%s"', $key), 500);
            }
        }
        $this->serverData = $serverData;
        $this->getData = $getData;
        $this->postData = $postData;
    }

    public function getAuthority()
    {
        // scheme
        if (!array_key_exists('REQUEST_SCHEME', $this->serverData)) {
            $requestScheme = 'http';
        } else {
            $requestScheme = $this->serverData['REQUEST_SCHEME'];
        }

        // server_name
        $serverName = $this->serverData['SERVER_NAME'];

        // port
        $serverPort = $this->serverData['SERVER_PORT'];

        $usePort = false;
        if ('https' === $requestScheme && 443 !== $serverPort) {
            $usePort = true;
        }
        if ('http' === $requestScheme && 80 !== $serverPort) {
            $usePort = true;
        }

        if ($usePort) {
            return sprintf('%s://%s:%d', $requestScheme, $serverName, $serverPort);
        }

        return sprintf('%s://%s', $requestScheme, $serverName);
    }

    public function getUri()
    {
        $requestUri = $this->serverData['REQUEST_URI'];

        return sprintf('%s%s', $this->getAuthority(), $requestUri);
    }

    public function getRoot()
    {
        $requestUri = $this->serverData['REQUEST_URI'];
        $pathInfo = $this->getPathInfo();
        // remove QUERY_STRING
        $hasQueryString = mb_strpos($requestUri, '?');
        if (false !== $hasQueryString) {
            $requestUri = mb_substr($requestUri, 0, $hasQueryString);
        }
        // remove PATH_INFO
        if ('/' !== $pathInfo) {
            $requestUri = mb_substr($requestUri, 0, mb_strlen($requestUri) - mb_strlen($pathInfo) + 1);
        }

        return $requestUri;
    }

    public function getRootUri()
    {
        return sprintf('%s%s', $this->getAuthority(), $this->getRoot());
    }

    public function getRequestMethod()
    {
        return $this->serverData['REQUEST_METHOD'];
    }

    public function getServerName()
    {
        return $this->serverData['SERVER_NAME'];
    }

    public function isBrowser()
    {
        if (!array_key_exists('HTTP_ACCEPT', $this->serverData)) {
            return false;
        }

        return false !== mb_strpos($this->serverData['HTTP_ACCEPT'], 'text/html');
    }

    public function getPathInfo()
    {
        if (!array_key_exists('PATH_INFO', $this->serverData)) {
            return '/';
        }

        return $this->serverData['PATH_INFO'];
    }

    public function getQueryParameter($key, $isRequired = true, $defaultValue = null)
    {
        return Utils::getValueFromArray($this->getData, $key, $isRequired, $defaultValue);
    }

    public function getPostParameter($key, $isRequired = true, $defaultValue = null)
    {
        return Utils::getValueFromArray($this->postData, $key, $isRequired, $defaultValue);
    }

    public function getHeader($key, $isRequired = true, $defaultValue = null)
    {
        return Utils::getValueFromArray($this->serverData, $key, $isRequired, $defaultValue);
    }
}