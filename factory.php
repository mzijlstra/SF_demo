<?php
/**
 * Context and Dependency Injection (CDI)
 * Factory class for creating controler and dao objects
 * And wiring up their dependencies
 *
 * @author mzijlstra 11/15/2014
 */

require 'model/CarDAO.php';
require 'control/CarCtrl.php';
require 'control/UserCtrl.php';

class Context {
    private $db;
    private $carDao;
    private $carCtrl;
    private $userCtrl;

    public function getDB() {
        if ($this->db == NULL) {
            $this->db = new PDO("mysql:dbname=sf_demo;host=localhost", "root");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->db;
    }

    public function getCarDao() {

        if ($this->carDao == NULL) {
            $this->carDao = new CarDAO();
            $this->carDao->db = $this->getDB();
        }
        return $this->carDao;
    }

    public function getCarCtrl() {
        if ($this->carCtrl == NULL) {
            $this->carCtrl = new CarCtrl();
            $this->carCtrl->carDao = $this->getCarDao();
        }
        return $this->carCtrl;
    }
    
    public function getUserCtrl() {
        if ($this->userCtrl == NULL) {
            $this->userCtrl = new UserCtrl();
        }
        return $this->userCtrl;
    }
}
