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
    const API_ENDPOINT = 'https://app.pakkelabels.dk/api/public/v2';

    /**
     * API Version
     *
     * @var string
     */
    const VERSION = '1.1';

    /**
     * API user
     *
     * @var string
     */
    protected $api_user;

    /**
     * API key
     *
     * @var string
     */
    protected $api_key;

   /**
     * Token
     *
     * @var string
     */
    protected $token;

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

    /**
     * Login
     *
     * @return void
     * @throws \PakkelabelsException
     */
    protected function login()
    {
        $result = $this->call(
            'users/login',
            true,
            array('api_user' => $this->api_user, 'api_key' => $this->api_key)
        );
        $this->token = $result['token'];
    }

    /**
     * Make API Call
     *
     * @param string  $method
     * @param boolean $doPost
     * @param array   $params
     *
     * @return \Pakkelabels\Response
     * @throws \PakkelabelsException
     */
    public function call($method, $doPost = false, $params = array())
    {
        $ch = curl_init();
        $params['token'] = $this->token;
        $params['user_agent'] = 'pdk_php_library v' . self::VERSION;

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
