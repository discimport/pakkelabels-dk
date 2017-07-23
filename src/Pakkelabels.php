<?php
/**
 * Pakkelabels
 *
 * PHP version 5
 *
 * @category  Pakkelabels
 * @package   Pakkelabels
 * @author    Lars Olesen <lars@intraface.dk>
 * @author    Hasse Ramlev Hansen <hasse@ramlev.dk>
 * @copyright 2015 Lars Olesen
 * @license   MIT Open Source License https://opensource.org/licenses/MIT
 * @version   GIT: <git_id>
 * @link      http://github.com/discimport/pakkelabels-dk
 */
namespace Pakkelabels;

use Pakkelabels\Exception\PakkelabelsException;

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
    const API_ENDPOINT = 'https://app.pakkelabels.dk/api/public/v2';

    /**
     * API Endpoint URL
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
        $result = $this->makeApiCall(
            'users/login',
            true,
            array('api_user' => $this->api_user, 'api_key' => $this->api_key)
        );
        $this->token = $result['token'];
    }

    /**
     * Get balance
     *
     * @return void
     * @throws \PakkelabelsException
     */
    public function balance()
    {
        $result = $this->makeApiCall('users/balance');
        return $result['balance'];
    }

    /**
     * Get PDF
     *
     * @return base64 encoded string
     * @throws \PakkelabelsException
     */
    public function pdf($id)
    {
        $result = $this->makeApiCall('shipments/pdf', false, array('id' => $id));
        return $result['base64'];
    }

    /**
     * Get pickup points.
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function getPickupPoints($params = array())
    {
        return $this->makeApiCall('pickup_points', false, $params);
    }

    /**
     * Search shipments
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function shipments($params = array())
    {
        $result = $this->makeApiCall('shipments/shipments', false, $params);
        return $result;
    }

    /**
     * Get imported shipments
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function importedShipments($params = array())
    {
        $result = $this->makeApiCall('shipments/imported_shipments', false, $params);
        return $result;
    }

    /**
     * Create imported shipment
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function createImportedShipment($params)
    {
        $result = $this->makeApiCall('shipments/imported_shipment', true, $params);
        return $result;
    }

    /**
     * Create shipment
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function createShipment($params)
    {
        $result = $this->makeApiCall('shipments/shipment', true, $params);
        return $result;
    }

    /**
     * Get freight rates
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function freightRates($params = array())
    {
        $result = $this->makeApiCall('shipments/freight_rates', false, $params);
        return $result;
    }

    /**
     * Get payment requests
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function paymentRequests()
    {
        $result = $this->makeApiCall('users/payment_requests');
        return $result;
    }

    /**
     * Get GLS Droppoints
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function glsDroppoints($params)
    {
        $result = $this->makeApiCall('shipments/gls_droppoints', false, $params);
        return $result;
    }

    /**
     * Get PostDK Droppoints
     *
     * @param array $params
     *
     * @return mixed
     * @throws \PakkelabelsException
     */
    public function pdkDroppoints($params)
    {
        return $this->makeApiCall('shipments/pdk_droppoints', false, $params);
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
     * @throws \PakkelabelsException
     */
    protected function makeApiCall($method, $doPost = false, $params = array())
    {
        $ch = curl_init();
        $params['token'] = $this->token;
        $params['user_agent'] = 'pdk_php_library v' . self::VERSION;

        $query = http_build_query($params);
        if ($doPost === true) {
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
        return $output;
    }
}
