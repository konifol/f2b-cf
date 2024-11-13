<?php

require 'config.php';
require 'cf_communication.php';
require 'checks.php';
require 'slack_communication.php';

$short_options = "i:a:h";
$long_options = ["ip-address:", "action:", "help"];
$options = getopt($short_options, $long_options);

$usage = "Here is the list of the available options: \n
-i, --ip-address        Define the IP, that will be banned\n
-a, --action            Supports two action ban - ban action, notify - notification without ban \n
-h, --help              Show the script usage\n";

if (isset($options["h"]) || isset($options["help"])){
    print($usage);
    exit;
}

$ip = OptionChecker($options,$so = "i",$lo = "ip-address",$usage);
$action = OptionChecker($options,$so = "a",$lo = "action",$usage);


//Check the IP in the AbuseIP database
$checks = GetIPinfo($ip);

$mess = "Notification without ban\n";

//Ban IP action
if ($action == 'ban'){

    $mess = BanIP($ip);
    
}

//Notification into Slack
$send_result = Slack_Action($mess,$checks,$ip);