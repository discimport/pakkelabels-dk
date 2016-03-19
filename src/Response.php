<?php

namespace Pakkelabels;

class Response
{
    protected $output;

    function __construct($output)
    {
        $this->output = $output;
    }
}
