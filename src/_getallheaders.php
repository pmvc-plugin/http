<?php

namespace PMVC\PlugIn\http;

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\GetAllHeaders';

class GetAllHeaders
{
    public function __invoke()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        } else {
            return array_filter($_SERVER, function($key) {
                return substr($key,0,5)=='HTTP_';
            }, ARRAY_FILTER_USE_KEY);
        }
    }
}

