<?php
namespace Lapurd\Exception;

interface HttpInterface
{
    public function showErrorPage();
}

abstract class Http extends \RuntimeException implements HttpInterface
{
    private $header;

    public function __construct($header, $message)
    {
        $this->header = $header;

        parent::__construct($message);
    }

    public function sendHeader()
    {
        header($this->header);
    }
}
