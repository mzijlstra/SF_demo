<?php

/**
 * Description of CarDao
 *
 * @author mzijlstra
 * @date 2018-04-22
 * 
 * @Repository
 */
class CarDao {

    use Repository;

    /**
     * Constructor
     */
    public function CarDao() {
        $this->table = "Car";
    }
}
