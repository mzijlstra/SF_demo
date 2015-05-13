<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserCtrl
 *
 * @author mzijlstra
 */
class UserCtrl {
    // POST /login
    public function login() {
        // start session, and clean any login errors 
        unset($_SESSION['error']);

        $user = filter_input(INPUT_POST, "user");
        $pass = filter_input(INPUT_POST, "pass");

        if ($user === "admin" && $pass === "admin") {
            // prevent session fixation
            session_regenerate_id();

            // set admin
            $_SESSION['admin'] = true;
            
            // default return location
            $location = "/car";
            if (isset($_SESSION['location'])) {
                $location = $_SESSION['location'];
                unset($_SESSION['location']);
            }

            return "Location: $location";
        } else {
            $_SESSION['error'] = "Invalid email / pass combo";
            return "Location: /login";
        }

    }
    
    // GET /logout
    public function logout() {
        session_destroy();
        $_SESSION['error'] = "Logged Out";
        return "Location: /";
    }
    

}
