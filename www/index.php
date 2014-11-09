<?php
    
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);
    
    //Config
    include "config.php";

    //Set up database
    $db = new mysqli($config["database"]["host"], $config["database"]["user"], $config["database"]["pass"], $config["database"]["db"]);
    if (mysqli_connect_errno()) die("Unable to connect to SQL Database: " . mysqli_connect_error());
    
    //Get ip
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else $ip= $_SERVER['REMOTE_ADDR'];
    
    //Get action
    $action = isset($_GET["action"])?strtolower($_GET["action"]):"";
    
    
    //Auth
    if ($action == "auth") {
    
        //Check if already loading
        $res = query("SELECT * FROM `lan-auth` WHERE ip='%s'", $ip)->fetch_assoc();
        if ($res) return;
    
        //Validate
        if (!isset($_POST["username"]) || !isset($_POST["password"]) || !isset($_POST["seat"])) errorJSON("Invalid post content");
        
        $fields = array("api_key" => $config["api"]["key"],
                        "username" => $_POST["username"],
                        "password" => $_POST["password"],
                        "seat" => $_POST["seat"]);   
        $fields_string = "";
        foreach($fields as $key=>$value) $fields_string .= $key.'='.$value.'&';
        rtrim($fields_string, '&');
        
        //Set up cURL and request
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $config["api"]["auth_url"]);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        $data = json_decode(curl_exec($ch), true);
        
        //Error?
        if (isset($data["error"])) errorJSON($data["error"]);
        if ($data != 1) errorJSON("Unexpected error");
        
        //Update table
        query("INSERT INTO `lan-auth` (ip, username, imported) VALUES ('%s', '%s', 0)", $ip, $_POST["username"]);
    
    }
    
    //Check ip
    else if ($action == "check") {
       
        //Get entry from db
        $res = query("SELECT * FROM `lan-auth` WHERE ip='%s'", $ip)->fetch_assoc();
        
        //Return values
        if (!$res) echo json_encode(array("re_auth" => true));
        else if ($res["imported"] == 0) echo json_encode(array("importing" => true));
        else echo json_encode(array("imported" => true));
        
    }
    
    //Main
    else {
    
        if (strpos(getenv("HTTP_USER_AGENT"), "MSIE") > -1) {
            preg_match("/MSIE (\d\d?)\./", getenv("HTTP_USER_AGENT"), $matches);
            if ($matches[1] < 8) Header("Location: no-ie.html");
        }
        
        ?>
            
            <html>
            
                <head>
                    <noscript>
                        <meta http-equiv="refresh" content="0; url=no-js.html" />
                    </noscript>
                    <title>LSUCS LAN Authentication</title>
                    <link rel="shortcut icon" href="favicon.ico" />
                    <link rel="stylesheet" type="text/css" href="css/jquery-ui/jquery-ui.custom.css" />
                    <link rel="stylesheet" type="text/css" href="css/styles.css" />
                    <script type="text/javascript" src="js/jquery.min.js"></script>
                    <script type="text/javascript" src="js/jquery-ui.custom.min.js"></script>
                    <script type="text/javascript" src="js/scripts.js"></script>
                </head>
                
                <body>
                
                    <img id="background" src="images/main-bg.png" />
                    
                    <div id="wrapper">
        
                        <div id="header">
                            
                            <div id="title-container">
                                <div id="title">
                                    LSU<span>CS</span>
                                </div>
                                <div id="subtitle">
                                    LAN Authentication
                                </div>
                            </div>
                  

                        </div>

                        <div id="overlay" class="content-overlay">
                            <div id="overlay-content">
                            </div>
                            <div id="close-overlay">
                            </div>
                        </div>
                        <div id="screen">
                        </div>
                        
                        <div id="content">
                
                            <div id="info">
                                This page is used to authenticate LAN attendees and grant you internet access. You can only sign in using this page if:
                                <ul>
                                    <li>You have a forum account</li>
                                    <li>You have bought a ticket to the LAN and it is assigned to your forum account</li>
                                    <li>You have registered at the front desk and had your ticket activated</li>
                                </ul>
                                If you think you have done all of the above and you still can't log in, please contact a committee member.
                            </div>
                
                            <div id="login-form">
                                <div>
                                    <label for="username">Username: </label>
                                    <input type="text" id="username" />
                                </div>
                                <div>
                                    <label for="password">Password: </label>
                                    <input type="password" id="password" />
                                </div>
                                <div>
                                    <label for="seat">Seat: </label>
                                    <input type="text" id="seat" />
                                </div>
                                <button id="login-button">Sign In</button>
                            </div>
                            
                            <div id="authenticated">
                                User authenticated, access granted. Redirecting to LAN website...
                            </div>
                            
                            <div id="loading">
                            </div>
                            
                            <div id="loading-text">
                                Waiting for authentication to process, this can take up to 30 seconds
                            </div>
                            
                        </div>
                        
                        <div id="push">
                        </div>
                        
                    </div>
                    
                    <div id="footer">
                        <a href="http://lsucs.org.uk">&copy; LSU Computer Society 2012</a>
                    </div>
                
                </body>

            </html>
        
        
        <?php
    }
    
    //DB function
    function query() {
        global $db;
        $args = func_get_args();
        $sql = array_shift($args);
        foreach ($args as $key => $value) $args[$key] = $db->real_escape_string(trim($value));
        $res = $db->query(vsprintf($sql, $args));
        if (!$res) die("MySQLi Error: " . mysqli_error($db));
        else return $res;
    }
    
    //Error function
    function errorJson($error) {
        $json["error"] = $error;
        die(json_encode($json));
    }
    
?>
