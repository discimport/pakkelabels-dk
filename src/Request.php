<?php

namespace Pakkelabels;

use Pakkelabels\Exception\PakkelabelsException;

class Request
{
    /**
     * API Endpoint URL
     *
     * @var string
     */
    const API_ENDPOINT = 'https://app.pakkelabels.dk/api/public/v1';

   /**
     * Token
     *
     * @var string
     */
    protected $token;

    function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Make API Call
     *
     * @param string  $method
     * @param boolean $doPost
     * @param array   $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function call($method, $doPost = false, $params = array())
    {
        $ch = curl_init();
        $params['token'] = $this->token;

        $query = http_build_query($params);
        if ($doPost) {
            curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT . '/' . $method);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        } else {
            curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT . '/' . $method . '?' . $query);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $output = json_decode($output, true);
        if ($http_code != 200) {
            throw new PakkelabelsException($output['message']);
        }
        return new Response($output);
    }
}
