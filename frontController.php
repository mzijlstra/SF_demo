<?php
/*
 * Michael Zijlstra 11/14/2014
 */

/* ************************************************************
 * -- FRONT CONTROLLER --
 * This is the entry point for every page in the application
 * ********************************************************** */

/* ******************************
 * Initialize Globals
 * **************************** */

// important internal global variables ($SF_*)
$__self = filter_input(INPUT_SERVER, "PHP_SELF", FILTER_SANITIZE_URL);
$__self_match = array();
preg_match("|(.*)/frontController.php|", $__self, $__self_match);
$SF_BASE = $__self_match[1];

$__the_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
$__uri_match = array();
preg_match("|$SF_BASE(/.*)|", $__the_uri, $__uri_match);

$SF_URI = $__uri_match[1];
$SF_METHOD = filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_STRING);

// create global variables for use inside controller methods
$URI_PARAMS = array(); // populated with URI parameters on URI match in routing
$VIEW_DATA = array(); // populated by controller, and used by view

// always start the session
session_start();

/* ****************************** 
 * Check Security based on URI
 * **************************** */
require 'security.php';

/* ****************************** 
 * Do Routing based on URI
 * **************************** */
require 'routing.php';
