<?php

require 'config.php';

// Communication with the AbuseIP database. Required the difination of the IPabuse API token. 
// To create the token use the instruction from here: https://docs.abuseipdb.com/#introduction
function GetIPinfo($ip){
    $url = "https://api.abuseipdb.com/api/v2/check?ipAddress=$ip&verbose&maxAgeInDays=30";
    $request = curl_init();
    
    $curl_headers = array();
    $curl_headers[] = "Key: IPABUSE_KEY";
    $curl_headers[] = "Content-Type: application/json";

    curl_setopt($request, CURLOPT_HTTPHEADER,$curl_headers);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($request, CURLOPT_URL, "$url");

    $result = curl_exec($request);

    $data_tmp = json_decode($result);

    $data = json_decode(json_encode($data_tmp), true);

    print_r($data);

    $ipinfo = array(
        'c_name' => $data['data']['countryName'],
        'ip' => $data['data']['ipAddress'],
        'c_code' => $data['data']['countryCode'],
        'reports' => $data['data']['totalReports']
    );
    print_r($ipinfo);

    return $ipinfo;
}


