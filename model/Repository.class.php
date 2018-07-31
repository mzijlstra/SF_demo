<?php

/**
 * Helper class used in Repository to represent constraining conditions for a 
 * result set.
 */
class Constraint {
    /**
     * @var string 
     */
    public $col; // column
    /**
     * @var string
     */
    public $op; // operator
    /**
     * @var string
     */
    public $val; // value

}

/**
 * Repository Trait 
 * 
 * @author mzijlstra 2018-02-27
 */
trait Repository {
    /**
     * @var string 
     */
    private $table;

    /**
     * Helper function to turn an array of condition objects into a string
     * 
     * @param array $conditions
     * @return array<string, string> SQL for the conditions
     */
    private function columns(array $conditions) {
        $cols = array();
        $map = array();
        foreach ($conditions as $c) {
            $cols[] = "{$c->col} {$c->op} {$c->val}";
            $map[$c->col] = $c->val; 
        }
        $result = array();
        $result['string']  = join(" AND ", $cols);
        $result['map'] = $map;
        return $result;
    }

    /**
     * Helper function to either save or update a single entity
     * 
     * @global PDO $DB
     * @param array $e an array whose key / value pairs represent an entity
     */
    private function save_or_update(array &$e) {
        global $DB;
        $columns = array();
        foreach (array_keys($e) as $k) {
            $columns[] = "$k = :$k ";
        }
        $cols = join(", ", $columns);

        // if an id attribute exists it's been created already
        // and therefore we can savely assume it's an update
        if ($e['id']) {
            $upd = $DB->prepare(
                    "UPDATE `{$this->table}` SET $cols WHERE id = :id");
            $upd->execute($e);
        } else {
            $ins = $$DB->prepare("INSERT INTO `{$this->table}` SET $cols");
            $ins->execute($e);
            $e['id'] = $DB->lastInsertId();
        }
    }

    /**
     * Saves or updates an entity or an array of entities
     * 
     * @param array $entity either an array with its key / value pairs 
     * representing a single entity, or an array of such arrays (many entities)
     */
    public function save(array &$entity) {
        // check if it has no string keys (an associative array)
        // and that the numbered indexes are sequential (normal array)
        if (count(array_filter(array_keys($entity), 'is_string')) > 0 &&
                array_keys($entity) !== range(0, count($entity) - 1)) {
            $this->save_or_update($entity);
        } else { // we savely assume its a list of entities
            foreach ($entity as $e) {
                $this->save_or_update($e);
            }
        }
    }

    /**
     * Function to retrieve results from the database table. Without any params
     * it returns all rows. Conditions can be used to restrict rows.
     * 
     * 
     * @global PDO $DB
     * @param array $columns of Contraint objects 
     * @param array $other key / value pairs giving other constraints (like order and size)
     * @return array PDO resultset (array of arrays)
     */
    public function find($columns = Null, $other = Null) {
        global $DB;
        $query = "SELECT * FROM {$this->table} ";
        $constraints = array();

        if ($columns) {
            $cols = $this->columns($columns);
            $query .= " WHERE " . $cols['string'];
            $constraints = $cols['map'];
        }
        if ($other && isset($other['order'])) {
            $query .= " ORDER BY :_order ";
            $constraints['_order'] = $other['order'];
            if (isset($other['direction']) 
                    && $other['direction'] == "DESC") {
                $query .= " DESC ";
            }
        }
        if ($other && isset($other['size'])) {
            $constraints['_size'] = $other['size'];
            if (isset($other['offset'])) {
                $query .= " LIMIT :_offset, :_size ";
                $constraints['_offset'] = $other['offset'];
            } else {
                $query .= " LIMIT :_size ";
            }
        }
        $find = $DB->prepare($query);
        $find->execute($constraints);
        return $find->fetchAll();
    }

    /**
     * Find one or more entities by id.
     * @global PDO $DB
     * @param mixed $id number or array of numbers 
     * @return a single result or array of results
     */
    public function findById($id) {
        global $DB;
        if (is_array($id)) {
            $find = $DB->prepare(
                    "SELECT * FROM {$this->table} WHERE id IN (:ids)");
            $find->execute(array("ids" => join(",", $id)));
            return $find->fetchAll();
        } else {
            $find = $DB->prepare(
                    "SELECT * FROM {$this->table} WHERE id = :id");
            $find->execute(array("id" => $id));
            return $find->fetch();
        }
    }

    /**
     * Returns a count of how many rows in total exist with these conditions. 
     * This is usefull for pagination.
     * 
     * @global PDO $DB
     * @param array $conditions of Constraint objects
     * @return int
     */
    public function count($conditions = Null) {
        global $DB;
        $query = "COUNT (*) from {$this->table} ";
        if ($conditions) {
            $cols = $this->columns($conditions);
            $query .= "WHERE {$cols['string']} ";
        }
        $find = $DB->prepare($query);
        $find->execute($conditions);
        return $find->fetch();
    }

    /**
     * Removes the given entity (array with key / value pairs) from the DB
     * 
     * @param array $entity (key value pairs representing an entity)
     */
    public function delete($entity) {
        $this->deleteById($entity['id']);
    }

    /**
     * Removes the entity/entities with the given id(s) from the database table.
     * 
     * @global PDO $DB
     * @param mixed $id or array of numbers $id
     */
    public function deleteById($id) {
        global $DB;
        if (is_array($id)) {
            $ids = join(",", $id);
        } else {
            $ids = "$id";
        }
        $del = $DB->prepare(
                "DELETE FROM {$this->table} WHERE id IN (:ids)");
        $del->execute(array("ids" => $ids));
    }

}
