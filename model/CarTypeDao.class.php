<?php

/**
 * Description of CarTypeDao
 *
 * @author mzijlstra
 * @date 2018-04-26
 * 
 * @Repository
 */
class CarTypeDao {
    use Repository;

    /**
     * Constructor
     */
    public function CarTypeDao() {
        $this->table = "CarType";
    }
}
