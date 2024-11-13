<?php

require 'config.php';

//Communication with the CloudFlare. It checks the existen rules and create a new one for the IP.
//The scrip uses the CloudFlare API token. It should be defined in config.php
//The currect creation API token is described in the instruction. 

function GetRuleList($cf_url,$cf_token){
    $request = curl_init($cf_url);

    $curl_headers = array();
    $curl_headers[] = "Authorization: Bearer $cf_token";
    $curl_headers[] = "Content-Type: application/json";

    curl_setopt($request, CURLOPT_HTTPHEADER,$curl_headers);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($request);

    $rule_list = json_decode($result);

    return $rule_list;
}

function CheckIPinList($ip,$current_rules){
    $data = json_decode(json_encode($current_rules), true);

    $result = $data['result'];

    $rule_id = '';

    foreach ($result as $key => $val){
        if($val["configuration"]["value"] == $ip){
            $rule_id = $val["id"];
            break;
        }
    }

    if(strlen($rule_id) >=1){
        $check_result = "IN";
        exit;
    }

    else{
        $check_result = "OK";
    }
    
    return $check_result;
}

function PrepareNewRule($ip){
    $config = array("target"=>"ip","value"=>"$ip");
    $new_rule = array("mode"=>"block","notes"=>"IP $ip is banned by the Fail2Ban rule","configuration"=>$config);

    $result = json_encode($new_rule);

    return $result;
}


function CreateNewRule($new_rule,$cf_url,$cf_token){
    $request = curl_init($cf_url);

    $curl_headers = array();
    $curl_headers[] = "Authorization: Bearer $cf_token";
    $curl_headers[] = "Content-Type: application/json";

    curl_setopt($request, CURLOPT_HTTPHEADER,$curl_headers);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($request, CURLOPT_POSTFIELDS, $new_rule);

    $result_tmp = curl_exec($request);

    $result = json_decode($result_tmp);

    return $result;
}

function CheckResultCreation($data_raw,$ip){
    $data = json_decode(json_encode($data_raw));

    $result = $data_raw->success;
    
    if ($result == "true") {
        $message = "The IP $ip was added to the CloudFlare IP access rule \n";
    }

    else {
        $error = $data_raw->errors->message;
        $message = "The IP $ip was not added to the CloudFlare IP access rule because $error \n";
    }

    return $message;
}

function BanIP($ip) {
    $get_current_rules = GetRuleList();

    $check_in_list = CheckIPinList();


    if ($check_in_list == 'IN') {
        
        $message = "$ip Already exists in rules on the CloudFlare side.\n
        Please check manually the rules.\n";

    }

    else {
        $new_rule = PrepareNewRule($ip);

        $create_rule = CreateNewRule($new_rule,$cf_url,$cf_token);

        $message = CheckResultCreation($create_rule,$ip);
    }

    return $message;
}