<?php

/*
 * Michael Zijlstra 11/15/2014
 */

/**
 * Helper function to check what kind of view should be displayed
 * 
 * @param type $data either string for HTML view or data for JSON
 */
function view($data) {
    if (!$data) {
        // page not found (security mapping exists, but no ctrl mapping)
        htmlmView("error/404.php");
    } else if (is_string($data)) {
        htmlView($data);
    } else {
        print json_encode($data);
    }
    // always exit after displaying the view, do we want a hook?
    exit();
}

/**
 * Helper to call htmlspecial char on all strings sent to view (including keys 
 * in arrays) recursively calls itself in order to process arrays
 * 
 * @param mixed $data
 * @return mixed
 */
function recHtmlSpecial(&$data) {
    if (is_string($data)) {
        return htmlspecialchars($data);
    }
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $data[htmlspecialchars($k)] = recHtmlSpecial($v);
        }
    }
    return $data;
}

/**
 * Helper function to redirect to a GET or display an HTML page
 * 
 * @global array $VIEW_DATA any data that the view may need in order to render
 * @param string $view the name of the view file to include before exiting, 
 * or alternately for redirects a location header string
 */
function htmlView($view) {
    global $VIEW_DATA;
    if (preg_match("/^Location: /", $view)) {
        if ($VIEW_DATA) {
            $_SESSION['flash_data'] = $VIEW_DATA;
        }
        header($view);
    } else {
        // make keys in VIEW_DATA available as regular variables
        foreach ($VIEW_DATA as $key => $value) {
            $$key = recHtmlSpecial($value);
        }
        require "view/$view";
    }
}

/**
 * Helper function that tries to match the request URI to a controller method 
 * and invoke it, returning the view string returned from the controller method
 * 
 * @global string $MY_URI the request URI as shown in the browser
 * @global array $URI_PARAMS empty array into which the matched controller
 * can put key/value pairs for any URI params it may extract
 * @param array $ctrls the $get_ctrl or $post_ctrl array containing URI to 
 * controller @ method mappings
 * @return string the view string returned by the matched controller
 */
function matchUriToMethod($ctrls) {
    global $MY_URI;
    global $URI_PARAMS;

    // check controler mappings
    foreach ($ctrls as $pattern => $dispatch) {
        if (preg_match($pattern, $MY_URI, $URI_PARAMS)) {
            // finding match completes method
            list($class, $method) = explode("@", $dispatch);
            return invokeMethod($class, $method);
        }
    }
    // was not able to find a match here
    return null;
}

/**
 * Helper function that actually invokes the found controller method
 * 
 * @param string $class Class name of the controller
 * @param string $method method name that should be invoked
 * @return string the view string returned by the controller method
 */
function invokeMethod($class, $method) {
    try {
        $context = new Context();
        $controler = $context->get($class);
        return $controler->{$method}();
    } catch (AuthorizationException $e) {
        auditLog("DENIED ACCESS: " . $e->getMessage());
        return "view/error/403.php";
    } catch (Exception $e) {
        error_log($e->getMessage());
        return "error/500.php";
    }
}

// The logic to do the actual routing dispatch, using the above helper functions
// and the $view_ctrl, $get_ctrl, $post_ctrl arrays from the context
switch ($MY_METHOD) {
    case "GET":
        // check for redirect flash attributes
        if (isset($_SESSION['flash_data'])) {
            foreach ($_SESSION['flash_data'] as $key => $val) {
                $VIEW_DATA[$key] = $val;
            }
            unset($_SESSION['flash_data']);
        }

        // check view controlers
        foreach ($view_ctrl as $pattern => $file) {
            if (preg_match($pattern, $MY_URI, $URI_PARAMS)) {
                view($file);
            }
        }

        // check get controllers
        view(matchUriToMethod($get_ctrl));
        break;
    case "POST":
        // check post controlers
        view(matchUriToMethod($post_ctrl));
        break;
    default:
        view("error/500.php");
}
