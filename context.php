<?php
$security = array(
	'GET' => array(
		'|^/login|' => 'none',
		'|^/$|' => 'none',
		'|/logout$|' => 'none',
		'|/car$|' => 'user',
		'|/car/(\d+)|' => 'user',
		'|/car/add|' => 'admin',
	),
	'POST' => array(
		'|/login$|' => 'none',
		'|/car/add|' => 'admin',
		'|/car/(\d+)$|' => 'admin',
		'|/car/(\d+)/del$|' => 'admin',
	)
);
$view_ctrl = array(
	'|^/login|' => 'login.php',
	'|^/$|' => 'welcome.php',
);
$get_ctrl = array(
	'|/car$|' => 'CarCtrl@all',
	'|/car/(\d+)|' => 'CarCtrl@one',
	'|/car/add|' => 'CarCtrl@viewAdd',
	'|^/logout$|' => 'UserCtrl@logout',
);
$post_ctrl = array(
	'|/car/add|' => 'CarCtrl@add',
	'|/car/(\d+)$|' => 'CarCtrl@upd',
	'|/car/(\d+)/del$|' => 'CarCtrl@del',
	'|^/login$|' => 'UserCtrl@login',
);
class Context {
    private $objects = array();
    
    public function Context() {
        $db = new PDO("mysql:dbname=sf_demo;host=localhost", "root", "root");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->objects["DB"] = $db;
    }

    public function get($id) {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        }
        if ($id === "CarDao") {
            $this->objects["CarDao"] = new CarDao();
            $this->objects["CarDao"]->db = $this->get("DB");
        }
        if ($id === "CarTypeDao") {
            $this->objects["CarTypeDao"] = new CarTypeDao();
            $this->objects["CarTypeDao"]->db = $this->get("DB");
        }
        if ($id === "CarCtrl") {
            $this->objects["CarCtrl"] = new CarCtrl();
            $this->objects["CarCtrl"]->carDao = $this->get("CarDao");
            $this->objects["CarCtrl"]->carTypeDao = $this->get("CarTypeDao");
        }
        if ($id === "UserCtrl") {
            $this->objects["UserCtrl"] = new UserCtrl();
        }
        return $this->objects[$id];
    } // close get method
} // close Context class