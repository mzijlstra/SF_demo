<?php
/**
 * Description of UserCtrl
 *
 * @author mzijlstra
 * 
 * @Controller
 */
class UserCtrl {
    
    /**
     * 
     * @GET(uri="^/$")
     */
    public function welcome() {
        return "welcome.php";
    }
    
    
    /**
     * 
     * @GET(uri="^/login$")
     */
    public function get_login() {
        return "login.php";
    }
    
    /**
     * @POST(uri="^/login$")
     * 
     * @return string
     */
    public function login() {
        global $MY_BASE;
        // start session, and clean any login errors 
        unset($_SESSION['error']);

        $user = filter_input(INPUT_POST, "user");
        $pass = filter_input(INPUT_POST, "pass");

        if ($user === "admin" && $pass === "admin") {
            // prevent session fixation
            session_regenerate_id();

            // set current user details
            $_SESSION['user'] = array(
                "id" => 1,
                "first" => "admin",
                "last" => "istrator",
                "role" => "admin"
            );
            
            // default return location
            $location = "$MY_BASE/car";
            if (isset($_SESSION['location'])) {
                $location = $_SESSION['location'];
                unset($_SESSION['location']);
            }

            return "Location: $location";
        } else {
            $_SESSION['error'] = "Invalid email / pass combo";
            auditLog("login failed user: $user");
            return "Location: /login";
        }

    }
    
    // GET /logout
    /**
     * @GET(uri="^/logout$")
     * 
     * @return string
     */
    public function logout() {
        session_destroy();
        $_SESSION['error'] = "Logged Out";
        return "Location: /";
    }
    

}
