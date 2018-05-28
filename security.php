<?php

/*
 * Michael Zijlstra 11/14/2014
 */

class AuthorizationException extends Exception {
    
}

/**
 * helper function to checks if user is logged in
 * @return boolean
 */
function isLoggedIn() {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    return true;
}

/**
 * Checks if a user is authorized for the provided role
 * 
 * @global array of arrays $SEC_ROLES The security roles, with their sub roles
 * @param string $role_policy the role the user should have
 * @return boolean
 * 
 * TODO: perhaps make $role_policy contain multiple possible matches?
 */
function isAuthorized($role_policy) {
    global $SEC_ROLES;
    $user_role = $_SESSION['user']['role'];
    $sub_roles = $SEC_ROLES[$user_role];
    if ($user_role !== $role_policy && !in_array($role_policy, $sub_roles)) {
        return false;
    }
    return true;
}

/**
 * Logs (almost) every call by a user to a service method to the audit table
 * 
 * @global PDO $DB database connection
 * @param string $msg usually class and method name of the service call
 */
function auditLog($msg) {
    global $DB;
    $id = 0;
    if (isset($_SESSION['user'])) {
        $id = $_SESSION['user']['id'];
    }
    $data = array("id" => $id, "call" => $msg);
    $ins = $DB->prepare("INSERT INTO AuditLog VALUES(NULL, NOW(), :id, :call)");
    $ins->execute($data);
}

// find the security policy for the current URI using $security from context
$role_policy = "none"; // default policy if no URI found
foreach ($security[$MY_METHOD] as $pattern => $policy) {
    if (preg_match($pattern, $MY_URI)) {
        $role_policy = $policy;
        break;
    }
}

// apply the (found) security policy
if ($role_policy !== 'none') {
    if (!isLoggedIn()) {
        // Then show login page
        $_SESSION['error'] = "Please Login:";
        header("Location: ${MY_BASE}/login");
        exit();
    }
    if (!isAuthorized($role_policy)) {
        require "view/error/403.php";
        exit();
    }
}
