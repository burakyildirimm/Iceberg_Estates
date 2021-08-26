<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function makeGetRequest($url, $params) {
        $client = new \GuzzleHttp\Client();
        $request = $client->get($url, [
            'query' => $params
        ]);
        $response = json_decode($request->getBody()->getContents());
        return $response;
    }

    public function makePostRequest($url, $params) {
        $client = new \GuzzleHttp\Client();
        $request = $client->post($url, [
            'form_params' => $params
        ]);
        $response = json_decode($request->getBody()->getContents());
        
        return $response;
    }

    public function obtainAddresses($postcodes) {
        $url = 'https://api.postcodes.io/postcodes';
        $params['postcodes'] = $postcodes;

        return $this->makePostRequest($url, $params);
    }

    public function obtainDistance($key, $origin, $destination) {
        $url = 'https://api.distancematrix.ai/maps/api/distancematrix/json';
        $params['origins'] = $origin;
        $params['destinations'] = $destination;
        $params['key'] = $key;

        return $this->makeGetRequest($url, $params);
    }

    public function obtainOneAddress($postcode) {
        $url = 'https://api.postcodes.io/postcodes';
        $params['q'] = $postcode;

        return $this->makeGetRequest($url, $params);
    }

}
