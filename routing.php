<?php

/*
 * Michael Zijlstra 11/15/2014
 */

/* * **************************************
 * Setup routing arrays
 * ************************************** */
// Requests that don't need a controller aka 'view controllers'
$view_ctrl = array(
    "|^/$|" => "welcome.php",
    "|^/index.php$|" => "welcome.php",
    "|^/login$|" => "login.php",
    "|^/car/add$|" => "carDetail.php",
);

// Get requests that need a controller
$get_ctrl = array(
    "|^/car$|" => "CarCtrl.all",
    "|^/car/(\d+)$|" => "CarCtrl.get",
    "|^/logout$|" => "UserCtrl.logout",
);

// Post requests that need a controller
$post_ctrl = array(
    "|^/login$|" => "UserCtrl.login",
    "|^/car/add$|" => "CarCtrl.add",
    "|^/car/(\d+)$|" => "CarCtrl.upd",
    "|^/car/(\d+)/del$|" => "CarCtrl.del",
);

/* * **************************************
 * Do actual routing
 * ************************************** */
require 'context.php';

// Do the actual dispatch, which will invoke the helper methods below
switch ($SF_METHOD) {
    case "GET":
        // check for redirect flash attributes
        if (isset($_SESSION['redirect']) && $_SESSION['redirect'] == $SF_URI) {
            foreach ($_SESSION['flash_data'] as $key => $val) {
                $VIEW_DATA[$key] = $val;
            }
            unset($_SESSION['redirect']);
            unset($_SESSION['flash_data']);
        }

        // check view controlers
        foreach ($view_ctrl as $pattern => $file) {
            if (preg_match($pattern, $SF_URI, $URI_PARAMS)) {
                applyView($file);
            }
        }
        // check get controllers
        matchUriToCtrl($get_ctrl);
        break;
    case "POST":
        // check post controlers
        matchUriToCtrl($post_ctrl);
        break;
    case "PUT":
    case "DELETE":
    default:
        http_response_code(403);
        echo "403 Access Forbidden";
        exit();
}


function applyView($view) {
    global $VIEW_DATA;
    global $SF_METHOD;
    global $SF_BASE;
    $uri = array();

    // check if it's a redirect, or display indicated view file
    if (preg_match("|^Location: (.*)|", $view, $uri)) {
        // view_data for a redirect becomes flash data
        if ($VIEW_DATA) {
            $_SESSION['redirect'] = $uri[1];
            $_SESSION['flash_data'] = $VIEW_DATA;
        }
        // change absolute uri's to be absolute to our project
        if ($uri[1][0] === "/") {
            $view = preg_replace("|Location: /|", "Location: $SF_BASE/", $view);
        }
        header($view);
    } else { 
        // don't display a view on post
        if ($SF_METHOD == "POST") {
            die("Please Use the Post/Redirect/Get Pattern");
        }
        // make keys in VIEW_DATA available as regular variables inside view
        foreach ($VIEW_DATA as $key => $value) {
            $$key = $value;
        }
        require "view/$view";
    }
    // always exit after displaying the view, do we want a hook?
    exit();
}

function matchUriToCtrl($ctrls) {
    global $SF_URI;
    global $URI_PARAMS;

    // check given controlers
    foreach ($ctrls as $pattern => $dispatch) {
        if (preg_match($pattern, $SF_URI, $URI_PARAMS)) {
            list($class, $method) = explode(".", $dispatch);
            $view = invokeCtrlMethod($class, $method);
            if ($view) {
                applyView($view);
            }
        }
    }
    // page not found (security mapping exists, but no ctrl mapping)
    http_response_code(404);
    echo "404 Page Not Found";
    exit();
}

function invokeCtrlMethod($class, $method) {
    $context = new Context();
    $getControler = new ReflectionMethod("Context", "get" . $class);
    $controler = $getControler->invoke($context);
    $doMethod = new ReflectionMethod($class, $method);

    try {
        return $doMethod->invoke($controler, $method);
    } catch (Exception $e) {
        // TODO: have some user setting for debug mode
        error_log($e->getMessage());
        http_response_code(500);
        echo "500 Internal Server Error";
        exit();
    }
}

