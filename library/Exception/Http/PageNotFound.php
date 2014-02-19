<?php
namespace Lapurd\Exception\Http;

use Lapurd\Exception\Http;

class PageNotFound extends Http
{
    public function __construct()
    {
        parent::__construct('HTTP/1.0 404 Not Found', '');
    }

    public function showErrorPage()
    {
        echo "<h1>404 Page Not Found</h1>";
        echo "The page you requested can not be found.";
    }
}
