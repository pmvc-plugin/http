<?php

namespace PMVC\PlugIn\http;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\http';
\PMVC\initPlugin(['controller'=>null, 'getenv'=>null]);

if (!class_exists(${_INIT_CONFIG}[_CLASS])) {
class http 
    extends \PMVC\PlugIn
    implements \PMVC\RouterInterface
{
    public function init()
    {
        \PMVC\callPlugin(
            'dispatcher',
            'attach',
            [
                $this,
                \PMVC\Event\MAP_REQUEST
            ]
        );
    }

    public function onMapRequest()
    {
        $env = \PMVC\plug('getenv');
        $controller = \PMVC\plug('controller');
        if (empty($controller)) {
            return !trigger_error(
                'Need initial controller first',
                E_USER_WARNING
            );
        }
        $request = $controller->getRequest();
        $uri = \PMVC\plug('url')->getPath();
        $uris = explode('/', $uri);
        array_shift($uris);
        for ($i=0, $j=count($uris); $i<$j; $i++) {
            $request[$i] = urldecode($uris[$i]);
        }
        $method = $this->getMethod();
        $request->setMethod($method);
        if ('GET'===$method) {
            $inputs =& $_GET;
        } else {
            $isJsonInput = ('application/json'===$env->get('CONTENT_TYPE'));
            if ($isJsonInput || 'PUT'===$method) {
                $input = file_get_contents("php://input");
                if ($isJsonInput) {
                    $inputs = \PMVC\fromJson($input, true);
                } else {
                    parse_str($input, $inputs);
                }
            } else {
                $inputs =& $_REQUEST;
            }
        }
        \PMVC\set($request,$inputs);
    }

    public function getMethod()
    {
        $env = \PMVC\plug('getenv');
        $method = $env->get('REQUEST_METHOD');
        $cros_method = $env->get('HTTP_ACCESS_CONTROL_REQUEST_METHOD');
        if ($method === 'OPTIONS' && $cros_method) {
            $method = $cros_method;
        }
        return $method;
    }

    public function buildCommand($url, $params)
    {
        $pUrl = \PMVC\plug('url');
        if (!is_object($url)) {
            $url = $pUrl->getUrl($url);
        }
        $url->query($params);
        return (string)$pUrl->pathToUrl($url);
    }

    /**
     * Process Header
     */
    public function processHeader(array $headers)
    {
        http_response_code(
            \PMVC\getOption(
                'httpResponseCode',
                http_response_code()
            )
        );
        foreach ($headers as $h) {
            header($h);
        }
    }

    /**
     * execute other php
     */
    public function go($path, $isClientLocation = false)
    {
        if (\PMVC\getOption(_VIEW_ENGINE)==='json') {
            if (!$isClientLocation) {
                $this->processHeader(["Location: $path"]);
            }
            return;
        }
        $this->processHeader(["Location: $path"]);
        echo '<meta http-equiv="refresh" content="0; url='.$path.'">';
        echo '<script>location.replace('.$path.')</script>';
    }
}}
