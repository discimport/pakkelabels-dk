<?php
/**
 * Pakkelabels
 *
 * PHP version 5
 *
 * @category  Pakkelabels
 * @package   Pakkelabels
 * @author    Lars Olesen <lars@intraface.dk>
 * @copyright 2015 Lars Olesen
 * @license   MIT Open Source License https://opensource.org/licenses/MIT
 * @version   GIT: <git_id>
 * @link      http://github.com/discimport/pakkelabels-dk
 */
namespace Pakkelabels;

use Pakkelabels\Exception\Pakkelabels_Exception;

/**
 * Class Pakkelabels
 *
 * Usage:
 * ----------------
 * The first thing required is to login
 * $label = new Pakkelabels('api_user', 'api_key');
 *
 * This will login and fetch the required token.
 * The token is then automatically added to any subsequent calls.
 *
 * To see the generated token you can use:
 * echo $label->getToken();
 *
 * Examples:
 * ----------------
 * // Get all Post Danmark labels shipped to Denmark
 * $labels = $label->shipments(array('shipping_agent' => 'pdk', 'receiver_country' => 'DK'));
 *
 * // Display the PDF for a specific label
 * $base64 = $label->pdf(31629);
 * $pdf = base64_decode($base64);
 * header('Content-type: application/pdf');
 * header('Content-Disposition: inline; filename="label.pdf"');
 * echo $pdf;
 *
 * @category  Pakkelabels
 * @package   Pakkelabels
 * @author    Lars Olesen <lars@intraface.dk>
 * @copyright 2015 Lars Olesen
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/discimport/pakkelabels-dk
 */

class Pakkelabels
{

    /**
     * API Endpoint URL
     *
     * @var string
     */
    const API_ENDPOINT = 'https://app.pakkelabels.dk/api/public/v1';

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
     * @throws \Pakkelabels_Exception
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
     * @throws \Pakkelabels_Exception
     */
    protected function login()
    {
        $result = $this->make_api_call('users/login', true, array('api_user' => $this->api_user, 'api_key' => $this->api_key));
        $this->token = $result['token'];
    }

    /**
     * Get balance
     *
     * @return void
     * @throws \Pakkelabels_Exception
     */
    public function balance()
    {
        $result = $this->make_api_call('users/balance');
        return $result['balance'];
    }

    /**
     * Get PDF
     *
     * @return base64 encoded string
     * @throws \Pakkelabels_Exception
     */
    public function pdf($id)
    {
        $result = $this->make_api_call('shipments/pdf', false, array('id' => $id));
        return $result['base64'];
    }

    /**
     * Search shipments
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function shipments($params = array())
    {
        $result = $this->make_api_call('shipments/shipments', false, $params);
        return $result;
    }

    /**
     * Get imported shipments
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function imported_shipments($params = array())
    {
        $result = $this->make_api_call('shipments/imported_shipments', false, $params);
        return $result;
    }

    /**
     * Create imported shipment
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function create_imported_shipment($params)
    {
        $result = $this->make_api_call('shipments/imported_shipment', true, $params);
        return $result;
    }

    /**
     * Create shipment
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function create_shipment($params)
    {
        $result = $this->make_api_call('shipments/shipment', true, $params);
        return $result;
    }

    /**
     * Get freight rates
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function freight_rates()
    {
        $result = $this->make_api_call('shipments/freight_rates');
        return $result;
    }

    /**
     * Get payment requests
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function payment_requests()
    {
        $result = $this->make_api_call('users/payment_requests');
        return $result;
    }

    /**
     * Get GLS Droppoints
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    public function gls_droppoints($params)
    {
        $result = $this->make_api_call('shipments/gls_droppoints', false, $params);
        return $result;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Make API Call
     *
     * @param string  $method
     * @param boolean $doPost
     * @param array   $params
     *
     * @return mixed
     * @throws \Pakkelabels_Exception
     */
    protected function make_api_call($method, $doPost = false, $params = array())
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
            throw new Pakkelabels_Exception($output['message']);
        }
        return $output;
    }
}
