<?php
require_once 'model/Repository.trait.php';

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
     *
     * @var String name of the table
     */
    private $table = "CarType";

    /**
     *
     * @var PDO PDO database connection object 
     * @Inject("DB")
     */
    public $db;

}
