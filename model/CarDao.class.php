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
     *
     * @var String name of the table
     */
    private $table = "Car";

    /**
     *
     * @var PDO PDO database connection object 
     * @Inject("DB")
     */
    public $db;

}
