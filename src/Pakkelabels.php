<?php
namespace Pakkelabels;

use Pakkelabels\Exception\Pakkelabels_Exception;

/*
Usage:
----------------
The first thing required is to login
$label = new Pakkelabels('api_user', 'api_key');

This will login and fetch the required token.
The token is then automatically added to any subsequent calls.

To see the generated token you can use:
echo $label->getToken();

Examples:
----------------
// Get all Post Danmark labels shipped to Denmark
$labels = $label->shipments(array('shipping_agent' => 'pdk', 'receiver_country' => 'DK'));

// Display the PDF for a specific label
$base64 = $label->pdf(31629);
$pdf = base64_decode($base64);
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="label.pdf"');
echo $pdf;
*/

class Pakkelabels {
    const API_ENDPOINT = 'https://app.pakkelabels.dk/api/public/v1';

    private $_api_user;
    private $_api_key;
    private $_token;

    public function __construct($api_user, $api_key){
        $this->_api_user = $api_user;
        $this->_api_key = $api_key;
        $this->login();
    }

    private function login() {
        $result = $this->_make_api_call('users/login', true, array('api_user' => $this->_api_user, 'api_key' => $this->_api_key));
        $this->_token = $result['token'];
    }

    public function balance() {
        $result = $this->_make_api_call('users/balance');
        return $result['balance'];
    }

    public function pdf($id) {
        $result = $this->_make_api_call('shipments/pdf', false, array('id' => $id));
        return $result['base64'];
    }

    public function shipments($params = array()) {
        $result = $this->_make_api_call('shipments/shipments', false, $params);
        return $result;
    }

    public function imported_shipments($params = array()) {
        $result = $this->_make_api_call('shipments/imported_shipments', false, $params);
        return $result;
    }

    public function create_imported_shipment($params) {
        $result = $this->_make_api_call('shipments/imported_shipment', true, $params);
        return $result;
    }

    public function create_shipment($params) {
        $result = $this->_make_api_call('shipments/shipment', true, $params);
        return $result;
    }

    public function freight_rates() {
        $result = $this->_make_api_call('shipments/freight_rates');
        return $result;
    }

    public function payment_requests() {
        $result = $this->_make_api_call('users/payment_requests');
        return $result;
    }

    public function gls_droppoints($params) {
        $result = $this->_make_api_call('shipments/gls_droppoints', false, $params);
        return $result;
    }

    public function getToken() {
        return $this->_token;
    }

    private function _make_api_call($method, $doPost = false, $params = array()) {
        $ch = curl_init();
        $params['token'] = $this->_token;

        $query = http_build_query($params);
        if ($doPost){
            curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT . '/' . $method);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        } else {
            curl_setopt($ch, CURLOPT_URL, self::API_ENDPOINT . '/' . $method . '?' . $query);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec ($ch);
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        $output = json_decode($output, true);
        if ($http_code != 200){
            if (is_array($output['message'])){
                throw new Pakkelabels_Exception($output['message']);
            } else {
                throw new Pakkelabels_Exception($output['message']);
            }
        }
        return $output;
    }
}
