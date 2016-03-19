<?php

namespace Pakkelabels;

class Response
{
    protected $output;

    public function __construct($output)
    {
        $this->output = $output;
    }

    public function get()
    {
        return $this->output;
    }
}
