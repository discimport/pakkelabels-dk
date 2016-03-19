<?php

namespace Pakkelabels;

class Client
{
    /**
     * Constructor
     *
     * @param string $api_user
     * @param string $api_key
     *
     * @throws \PakkelabelsException
     */
    public function __construct($api_user, $api_key)
    {
        $this->api_user = $api_user;
        $this->api_key = $api_key;
        $this->login();
    }
}