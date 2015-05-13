<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CarDao
 *
 * @author mzijlstra
 */
class CarDao {

    public $db;

    public function all() {
        $stmt = $this->db->prepare("SELECT * FROM car ORDER BY make ");
        $stmt->execute();

        $cars = array();
        foreach ($stmt as $row) {
            $cars[] = $row;
        }
        return $cars;
    }
    
    public function get($id) {
        $stmt = $this->db->prepare(
                "SELECT * FROM car "
                . "WHERE id = :id");
        $stmt->execute(array("id" => $id));
        return $stmt->fetch();
    }
    
    public function add($make, $model, $year, $color) {
        $stmt = $this->db->prepare("INSERT INTO car "
                . "VALUES(NULL, :make, :model, :year, :color)");
        $stmt->execute(array("make" => $make, "model" => $model,
            "year" => $year, "color" => $color));
        return $this->db->lastInsertId();
    }
    
    public function upd($id, $make, $model, $year, $color) {
        $stmt = $this->db->prepare("UPDATE car SET "
                . "make = :make, model = :model, year = :year, color = :color "
                . "WHERE id = :id");
        $stmt->execute(array("make" => $make, "model" => $model,
            "year" => $year, "color" => $color, "id" => $id));        
    }
    
    public function del($id) {
        $stmt = $this->db->prepare("DELETE FROM car WHERE id = :id");
        $stmt->execute(array("id" => $id));
    }

}
