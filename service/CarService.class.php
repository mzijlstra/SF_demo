<?php

/**
 * Description of CarService
 *
 * @author mzijlstra
 * @date 2018-may-20
 * 
 * @Service
 */
class CarService {

    /**
     * @Inject('CarDao')
     */
    public $carDao;

    /**
     * @Inject('CarTypeDao')
     */
    public $carTypeDao;

    /**
     * Returns all the different car types
     * @return array of arrays (PDO resultset)
     * 
     * @Service("nolog")
     * @Security("user")
     */
    public function getCarTypes() {
        return $this->carTypeDao->find();
    }

    /**
     * Returns a car based on given id
     * @param int $id
     * @return array (single result row)
     * 
     * @Security("user")
     */
    public function getCar($id) {
        return $this->carDao->findById($id);
    }

    /**
     * Returns cars based on the given contraints
     * 
     * @param array $columns of Contraint objects 
     * @param array $other key / value pairs giving other constraints (like order and size)
     * @return array of arrays (PDO resultset)
     * 
     * @Security("user")
     */
    public function getCars($columns=null, $other=null) {
        return $this->carDao->find($columns, $other);
    }

    /**
     * Insert or Update a car object
     * @param array $car (key / value pairs representing the entity)
     * 
     * @Security("admin")
     */
    public function saveCar(&$car) {
        $this->carDao->save($car);
    }

    /**
     * Deletes car based on the given id
     * @param int $id
     * 
     * @Security("admin")
     */
    public function deleteCar($id) {
        $this->carDao->deleteById($id);
    }
}
