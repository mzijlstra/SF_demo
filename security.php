<?php

/*
 * Michael Zijlstra 11/14/2014
 */

// helper function to checks if user is logged in
function isLoggedIn() {
    global $MY_BASE;
    if (!isset($_SESSION['user'])) {
        // Then show login page
        $_SESSION['error'] = "Please Login:";
        header("Location: ${MY_BASE}/login");
        exit();
    }
}

function isAuthorized($role_policy) {
    global $SEC_ROLES;
    $user_role = $_SESSION['user']['role'];
    $sub_roles = $SEC_ROLES[$user_role];
    if ($user_role !== $role_policy && !in_array($role_policy, $sub_roles)) {
        require "view/error/403.php";
        exit();
    }
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
    isLoggedIn();
    isAuthorized($role_policy);
}
