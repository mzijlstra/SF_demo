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
 */
class CarCtrl {

    public $carDao;

    // GET /car$
    public function all() {
        global $VIEW_DATA;
        $VIEW_DATA['cars'] = $this->carDao->all();
        return "carList.php";
    }

    // GET /car/(\d+)
    public function get() {
        global $VIEW_DATA;
        global $URI_PARAMS;
        $cid = $URI_PARAMS[1];
        $VIEW_DATA['car'] = $this->carDao->get($cid);
        return "carDetail.php";
    }

    // POST /car/add
    public function add() {
        global $VIEW_DATA;
        // get input
        $make = filter_input(INPUT_POST, "make");
        $model = filter_input(INPUT_POST, "model");
        $year = filter_input(INPUT_POST, "year");
        $color = filter_input(INPUT_POST, "color");

        // process input
        $cid = $this->carDao->add($make, $model, $year, $color);

        // display output
        $VIEW_DATA['action'] = 'added';
        $VIEW_DATA['hl'] = $cid;
        
        return "Location: /car";
    }

    // POST /car/(\d+)$
    public function upd() {
        global $URI_PARAMS; 
        global $VIEW_DATA;
        
        $cid = $URI_PARAMS[1];
        $make = filter_input(INPUT_POST, "make");
        $model = filter_input(INPUT_POST, "model");
        $year = filter_input(INPUT_POST, "year");
        $color = filter_input(INPUT_POST, "color");

        $this->carDao->upd($cid, $make, $model, $year, $color);
        
        $VIEW_DATA['action'] = 'updated';
        $VIEW_DATA['hl'] = $cid;
        
        return "Location: /car";
    }

    // POST /car/(\d+)/del
    public function del() {
        global $VIEW_DATA;
        global $URI_PARAMS;
        
        $cid = $URI_PARAMS[1];
        $this->carDao->del($cid);
        
        $VIEW_DATA['action'] = 'deleted';
        return "Location: /car";
    }

}
