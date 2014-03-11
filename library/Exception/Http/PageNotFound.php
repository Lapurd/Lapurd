<?php
namespace Lapurd\Exception\Http;

use Lapurd\View;
use Lapurd\Exception\Http;

class PageNotFound extends Http
{
    public function __construct()
    {
        parent::__construct('HTTP/1.0 404 Not Found', '');
    }

    public function showErrorPage()
    {
        $view = new View('page-not-found');
        print $view->theme('');
    }
}
