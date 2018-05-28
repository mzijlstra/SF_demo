<?php

/*
 * Michael Zijlstra 11/14/2014
 */
/* * *****************************
 * Configuration variables
 * **************************** */
define("DEVELOPMENT", true);
define("DSN", "mysql:dbname=sf_demo;host=localhost");
define("DB_USER", "root");
define("DB_PASS", "root");

$SEC_ROLES = array(
    "none" => [],
    "user" => ["none"],
    "admin" => ["user", "none"]
);

/* * *****************************
 * Initialize Globals
 * **************************** */
$__self = filter_input(INPUT_SERVER, "PHP_SELF", FILTER_SANITIZE_URL);
$matches = array();
preg_match("|(.*)/frontController.php|", $__self, $matches);
$MY_BASE = $matches[1];

$the_uri = filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);
preg_match("|$MY_BASE(/.*)|", $the_uri, $matches);
$MY_URI = $matches[1];

$MY_METHOD = filter_input(INPUT_SERVER, "REQUEST_METHOD", FILTER_SANITIZE_STRING);
$URI_PARAMS = array(); // populated with URI parameters on URI match in routing
$VIEW_DATA = array(); // populated by controller, and used by view
$DB = new PDO(DSN, DB_USER, DB_PASS);
$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* * ****************************
 * Setup autoloading for classes
 * **************************** */
function case_sensitive_autoloader($class) {
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $p) {
        if (file_exists("$p/{$class}.class.php")) {
            include "$p/{$class}.class.php";
        }
    }
}

if (PHP_OS === "Linux") {
    spl_autoload_register("case_sensitive_autoloader");
} else {
    spl_autoload_extensions(".class.php");
    spl_autoload_register();
}

/* * ****************************
 * Include the (generated) application context
 * **************************** */
if (DEVELOPMENT) {
    require 'AnnotationReader.class.php';
    $ac = new AnnotationReader();
    $ac->scan()->create_context();
    $ac->write("context.php");  # uncomment to generate file
    eval($ac->context);
} else {
    require 'context.php';
}

// always start the session context
session_start();

/* * ***************************** 
 * Check Security based on context security array
 * **************************** */
require 'security.php';

/* * ***************************** 
 * Do Routing based on context routing arrays
 * **************************** */
require 'routing.php';
