<?php

namespace App\Services;

class MobiKwikService
{
    protected $base_url;
    protected $uid;
    protected $password;
    protected $hash_key;

    public function __construct()
    {
        $this->base_url = 'https://alpha3.mobikwik.com';
        $this->uid = 'testalpha1@gmail.com';
        $this->password = 'testalpha1@123';
        $this->hash_key = 'abcd@123';
    }

    public function getAllPlans($operator_id, $circle_id)
    {

        $url = $this->base_url . "/recharge/v1/rechargePlansAPI//$operator_id/$circle_id";
        $method = 'GET';
        $header = [
            'Content-Type: application/json',
            'X-MClient: 14',
        ];
        $parameters = [];
        $response = $this->sendCurlPost($url, $header, $parameters, $method);
        $res = json_decode($response, true);
        if (isset($res['success'])) {
            if ($res['success']) {
                return ['status' => 'success', 'message' => $res['message'], 'data' => $res['data']];
            } else {
                return ['status' => 'failure', 'message' => isset($res['message']['text']) ? $res['message']['text'] : 'Recharge plans not available for the specified operator, circle.'];
            }
        } else {
            return ['status' => 'failure', 'message' => 'Someting went wrong'];
        }

    }

    public function sendCurlPost($url, $header, $api_request_parameters, $method)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $api_request_parameters,
            CURLOPT_HTTPHEADER => $header,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
