<?php

    $config = array();
    
    //Database
    $config["database"] = array("host" => "127.0.0.1",
                                "user" => "root",
                                "pass" => "pass",
                                "db"   => "lan-auth");
                                
    //API
    $config["api"] = array("key" => 'key',
                           "auth_url" => "http://lan.lsucs.org.uk/index.php?page=api&action=lanauth");
                           
    //Network
    $config["network"] = array("user" => "lsucs",
                               "password" => 'pass',
                               "core1" => "192.168.0.2",
                               "core2" => "192.168.0.3",
                               "port" => 22);

?>
