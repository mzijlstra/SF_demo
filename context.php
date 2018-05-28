<?php
$security = array(
	'GET' => array(
		'|/logout$|' => 'none',
		'|^/login|' => 'none',
		'|^/$|' => 'none',
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
class ProxyCarService {
    public $actual;
    public function getCarTypes(){
        global $DB;
        if (!isAuthorized('user')) {
            throw new AuthorizationException('CarService.getCarTypes');
        }
        try {
            $DB->beginTransaction();
            $result = $this->actual->getCarTypes();
            $DB->commit();
        } catch (Exception $e) {
             $DB->rollBack();
            throw $e;
        }
        return $result;    }
    public function getCar($id){
        global $DB;
        if (!isAuthorized('user')) {
            throw new AuthorizationException('CarService.getCar');
        }
        try {
            $DB->beginTransaction();
            auditLog('CarService.getCar');
            $result = $this->actual->getCar($id);
            $DB->commit();
        } catch (Exception $e) {
             $DB->rollBack();
            throw $e;
        }
        return $result;    }
    public function getCars($columns=false, $other=false){
        global $DB;
        if (!isAuthorized('user')) {
            throw new AuthorizationException('CarService.getCars');
        }
        try {
            $DB->beginTransaction();
            auditLog('CarService.getCars');
            $result = $this->actual->getCars($columns, $other);
            $DB->commit();
        } catch (Exception $e) {
             $DB->rollBack();
            throw $e;
        }
        return $result;    }
    public function saveCar($car){
        global $DB;
        if (!isAuthorized('admin')) {
            throw new AuthorizationException('CarService.saveCar');
        }
        try {
            $DB->beginTransaction();
            auditLog('CarService.saveCar');
            $result = $this->actual->saveCar($car);
            $DB->commit();
        } catch (Exception $e) {
             $DB->rollBack();
            throw $e;
        }
        return $result;    }
    public function deleteCar($id){
        global $DB;
        if (!isAuthorized('admin')) {
            throw new AuthorizationException('CarService.deleteCar');
        }
        try {
            $DB->beginTransaction();
            auditLog('CarService.deleteCar');
            $result = $this->actual->deleteCar($id);
            $DB->commit();
        } catch (Exception $e) {
             $DB->rollBack();
            throw $e;
        }
        return $result;    }
}
class Context {
    private $objects = array();
    
    public function Context() {
        global $DB;
        $this->objects["DB"] = $DB;
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
            $this->objects["CarCtrl"]->carService = $this->get("CarService");
        }
        if ($id === "UserCtrl") {
            $this->objects["UserCtrl"] = new UserCtrl();
        }
        if ($id === "CarService") {
            $proxy = new ProxyCarService();
            $this->objects["CarService"] = $proxy;
            $actual = new CarService();
            $actual->carDao = $this->get("CarDao");
            $actual->carTypeDao = $this->get("CarTypeDao");
           $proxy->actual = $actual;        }
        return $this->objects[$id];
    } // close get method
} // close Context class