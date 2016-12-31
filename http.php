<?php
namespace PMVC\PlugIn\http;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\http';
\PMVC\initPlugin(['controller'=>null]);

const REQUEST_METHOD = '--REQUEST_METHOD';

class http 
    extends \PMVC\PlugIn
    implements \PMVC\RouterInterface
{
    public function init()
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
        $method = $this->getMethod();
        $request->setMethod($method);
        if ('GET'===$method) {
            $inputs =& $_GET;
        } else {
            $isJsonInput = ('application/json'===$env->get('CONTENT_TYPE'));
            if ($isJsonInput || 'PUT'===$method) {
                $input = file_get_contents("php://input");
                if ($isJsonInput) {
                    $inputs = (array)\PMVC\fromJson($input);
                } else {
                    parse_str($input, $inputs);
                }
            } else {
                $inputs =& $_POST;
            }
        }
        \PMVC\set($request,$inputs);
        if (isset($request[REQUEST_METHOD])) {
            $request->setMethod($request[REQUEST_METHOD]);
        }
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
        $url = \PMVC\plug('url')->getUrl($url);
        $url->query($params);

        return (string)$url;
    }

    /**
     * Process Header
     */
    public function processHeader($headers)
    {
        foreach ($headers as $h) {
            header($h);
        }
    }

    /**
     * execute other php
     */
    public function go($path)
    {
        $this->processHeader(array("Location: $path"));
    }
}
