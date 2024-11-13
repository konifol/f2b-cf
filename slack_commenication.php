<?php

require 'config.php';

//Script for the creation of the message based on the checks and ban results. 
//The notification will be sent into Slack using the webhook. Webhook should be defined in config.php
function PrepareMessage($b_result,$ipinfo,$ip){
    $name = $ipinfo['c_name'];
    $code = $ipinfo['c_code'];
    $ip = $ipinfo['ip'];

    if ($ipinfo['reports'] == 0){
        $abuse_info = "This IP is not presented in the abuseipdb.com base.";
    }
    else {
        $abuse_info = "This IP is presented in the abuseipdb.com base. To get more information follow this link:\n
        https://www.abuseipdb.com/check/$ip";
    }

    $flag = "flag-$code";

    $text = "The IP :$flag:$ip($name) was banned by the Fail2Ban NGINX_301 rule.\n$abuse_info\n
    Ban action result:\n$b_result";

    $message = array('payload' => json_encode(array('text' => $text)));

    return $message;
}

function SendNotification($message){

    $send = curl_init(SLACK_HOOK);
    
    curl_setopt($send, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($send, CURLOPT_POST, true);
    curl_setopt($send, CURLOPT_POSTFIELDS, $message);

    $result = curl_exec($send);

    return $result;
}

function Slack_Action($b_result,$ipinfo,$ip){

    $n_notify = PrepareMessage($b_result,$ipinfo,$ip);

    $s_notify = SendNotification($n_notify);

    return $s_notify;
}