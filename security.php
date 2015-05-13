<?php
/*
 * Michael Zijlstra 11/14/2014
 */

/* **************************
 * Security to URL 
 * ************************ */
$sec = array(
    "GET@|^/$|" => "none",
    "GET@|^/index.php$|" => "none",    
    "GET@|^/login$|" => "none",
    "GET@|^/logout$|" => "none",
    "POST@|^/login$|" => "none",
    "GET@|^/car$|" => "none",
    "GET@|^/car/\d+$|" => "admin",
    "GET@|^/car/add$|" => "admin",
    "POST@|^/car/add$|" => "admin",
    "POST@|^/car/\d+$|" => "admin",
    "POST@|^/car/\d+\del$|" => "admin",
);

/* ****************************
 * Do actual Security checks
 * ************************** */
// set default policy -- in case we don't find a match in $sec
$my_policy = "admin";

foreach ($sec as $pattern => $policy) {
    list($meth, $uri) = explode("@", $pattern); 
    if ($meth === $SF_METHOD && preg_match($uri, $SF_URI)) {
        $my_policy = $policy;
        break;
    }
}

switch ($my_policy) {
    case "none":
        break;
    case "admin":
    default:
        isLoggedIn();
        if (!isset($_SESSION['admin'])) {
            http_response_code(403);
            echo "403 Access Forbidden";
            exit();
        }
}

function isLoggedIn() {
    global $SF_BASE;
    global $SF_URI;
    if (!isset($_SESSION['admin'])) {
        // store the original request URI, so we can return after login
        $_SESSION['location'] = $SF_URI;
        $_SESSION['error'] = "Please Login First";
        header("Location: $SF_BASE/login");
        exit();
    }    
}

