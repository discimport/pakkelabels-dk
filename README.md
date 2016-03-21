# Pakkelabels.dk PHP SDK
[![Build Status](https://travis-ci.org/discimport/pakkelabels-dk.svg?branch=master)](https://travis-ci.org/discimport/pakkelabels-dk) [![Coverage Status](https://coveralls.io/repos/discimport/pakkelabels-dk/badge.svg)](https://coveralls.io/r/discimport/pakkelabels-dk) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/discimport/pakkelabels-dk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/discimport/pakkelabels-dk/?branch=master) [![Latest Stable Version](https://poser.pugx.org/discimport/pakkelabels/v/stable)](https://packagist.org/packages/discimport/pakkelabels) [![Total Downloads](https://poser.pugx.org/discimport/pakkelabels/downloads)](https://packagist.org/packages/discimport/pakkelabels) [![License](https://poser.pugx.org/discimport/pakkelabels/license)](https://packagist.org/packages/discimport/pakkelabels)

PHP SDK for [Pakkelabels.dk API](https://www.pakkelabels.dk/integration/api/) from the Danish shipping service [Pakkelabels.dk](https://www.pakkelabels.dk). 

This is a modified version of the officially supported version on [Pakkelabels.dk API](https://www.pakkelabels.dk/integration/api/). This version has composer integration, tests, following PSR2 coding standards and better error handling.

## Examples

The API for this PHP-SDK is still in development. There are some examples in the 'tests' directory.

## Getting started

Below is a simple PHP script which illustrate the minimum amount of code needed to getting started.

    <?php
    use Pakkelabels\Pakkelabels;

    try {
      $label = new Pakkelabels('api_user', 'api_key');
    } catch (PakkelabelsException $e) {
      echo $e->getMessage();
    }

Once the $label object is created, you can begin to use the API.

To see the current balance:

    echo $label->balance();

To list all Post Danmark shipments sent to to Denmark:

    $labels = $label->shipments(array('shipping_agent' => 'pdk', 'receiver_country' => 'DK'));
    print_r($labels);

To display the PDF for the shipment ID with 42 inline in the browser:

    $base64 = $label->pdf(42);
    $pdf = base64_decode($base64);
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="label.pdf"');
    echo $pdf;

To create a test shipment with Post Danmark, and then output the Track&Trace number of the newly created shipment:

    $data = array(
      'shipping_agent' => 'pdk',
      'weight' => '1000',
      'receiver_name' => 'John Doe',
      'receiver_address1' => 'Some Street 42',
      'receiver_zipcode' => '5230',
      'receiver_city' => 'Odense M',
      'receiver_country' => 'DK',
      'sender_name' => 'John Wayne',
      'sender_address1' => 'The Batcave 1',
      'sender_zipcode' => '5000',
      'sender_city' => 'Odense C',
      'sender_country' => 'DK',
      'delivery' => 'true',
      'test' => 'true' // Change to false when going live
    );

    $shipment = $label->createShipment($data);
    echo 'Track&Trace: ' . $shipment['pkg_no'];
