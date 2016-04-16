<?php
namespace PMVC\PlugIn\http;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\http';
\PMVC\initPlugin(['controller'=>null]);

class http 
    extends \PMVC\PlugIn
    implements \PMVC\RouterInterface
{
    public function init()
    {
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
            $isJsonInput = ('application/json'===getenv('CONTENT_TYPE'));
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
    }

    public function getMethod()
    {
        $method = getenv('REQUEST_METHOD');
        $cros_method = getenv('HTTP_ACCESS_CONTROL_REQUEST_METHOD');
        if ($method === 'OPTIONS' && $cros_method) {
            $method = $cros_method;
        }
        return $method;
    }

    public function buildCommand($url, $params)
    {
        $parsed_url = parse_url($url);
        if (!empty($params)) {
            if (!empty($parsed_url['query'])) {
                parse_str($parsed_url['query'], $parsed_query);
            } else {
                $parsed_query = array();
            }
            $parsed_url['query'] = http_build_query(
                array_merge(
                    $parsed_query,
                    $params
                )
            );
        }
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : ''; 
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
        $pass     = ($user || $pass) ? "$pass@" : ''; 
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
        $fragment = isset($parsed_url['fragment'])?'#'. $parsed_url['fragment']: ''; 
        return "$scheme$user$pass$host$port$path$query$fragment"; 
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
