<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CarCtrl
 *
 * @author mzijlstra
 * 
 * @Controller
 */
class CarCtrl {

    /**
     * @Inject('CarService')
     */
    public $carService;
    

    /**
     * 
     * @global array $VIEW_DATA
     * @return string containing view
     * 
     * @GET(uri="/car$", sec="user")
     */
    public function all() {
        global $VIEW_DATA;
        $VIEW_DATA['cars'] = $this->carService->getCars();
        return "carList.php";
    }
    
    /**
     * 
     * @global array $VIEW_DATA
     * @global array $URI_PARAMS
     * @return string containing view
     * 
     * @GET(uri="/car/(\d+)", sec="user")
     */
    public function one() {
        global $VIEW_DATA;
        global $URI_PARAMS;
        $cid = $URI_PARAMS[1];
        $VIEW_DATA['car'] = $this->carService->getCar($cid);
        $VIEW_DATA['types'] = $this->carService->getCarTypes();
        return "carDetail.php";
    }

    /**
     * @GET(uri="/car/add", sec="admin")
     */
    public function viewAdd() {
        global $VIEW_DATA;
        $VIEW_DATA['types'] = $this->carService->getCarTypes();
        return "carDetail.php";
    }
    
    /**
     * 
     * @global array $VIEW_DATA
     * @return string containing view
     * 
     * @POST(uri="/car/add", sec="admin")
     */
    public function add() {
        global $VIEW_DATA;
        // get input
        $make = filter_input(INPUT_POST, "make");
        $model = filter_input(INPUT_POST, "model");
        $year = filter_input(INPUT_POST, "year");
        $color = filter_input(INPUT_POST, "color");
        $type = filter_input(INPUT_POST, "type");
        $car = array("make" => $make, "model" => $model, "year" => $year,
            "color" => $color, "type" => $type);

        // process input
        $cid = $this->carService->saveCar($car);

        // display output
        $VIEW_DATA['action'] = 'added';
        $VIEW_DATA['hl'] = $cid;

        return "Location: ../car";
    }

    /**
     * 
     * @global array $URI_PARAMS
     * @global array $VIEW_DATA
     * @return string containing view
     * 
     * @POST(uri="/car/(\d+)$", sec="admin")
     */
    public function upd() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $cid = $URI_PARAMS[1];
        $make = filter_input(INPUT_POST, "make");
        $model = filter_input(INPUT_POST, "model");
        $year = filter_input(INPUT_POST, "year");
        $color = filter_input(INPUT_POST, "color");
        $car = array("id" => $cid, "make" => $make, "model" => $model,
            "year" => $year, "color" => $color);

        $this->carService->saveCar($car);

        $VIEW_DATA['action'] = 'updated';
        $VIEW_DATA['hl'] = $cid;

        return "Location: ../car";
    }

    /**
     * 
     * @global array $VIEW_DATA
     * @global array $URI_PARAMS
     * @return string containing view
     * 
     * @POST(uri="/car/(\d+)/del$", sec="admin")
     */
    public function del() {
        global $VIEW_DATA;
        global $URI_PARAMS;

        $cid = $URI_PARAMS[1];
        $this->carService->deleteCar($cid);

        $VIEW_DATA['action'] = 'deleted';
        return "Location: ../../car";
    }

}
