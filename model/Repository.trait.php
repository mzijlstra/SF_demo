<?php

/**
 * Helper class used in Repository to represent constraining conditions for a 
 * result set.
 */
class Condition {

    public $col; // column
    public $op; // operator
    public $val; // value

}

/**
 * Repository Trait 
 * 
 * @author mzijlstra 2018-02-27
 */
trait Repository {
    /**
     * Helper function to turn an array of condition objects into a string
     * 
     * @param array $conditions
     * @return string SQL for the conditions
     */
    private function columns($conditions) {
        $cols = array();
        foreach ($conditions as $c) {
            $cols[] = "{$c->col} {$c->op} {$c->val}";
        }
        return join(" AND ", $cols);
    }

    /**
     * Helper function to either save or update a single entity
     * 
     * @param array $e an array whose key / value pairs represent an entity
     */
    private function save_or_update(&$e) {
        $columns = array();
        foreach (array_keys($e) as $k) {
            $columns[] = "$k = :$k ";
        }
        $cols = join(", ", $columns);
        
        // if an id attribute exists it's been created already
        // and therefore we can savely assume it's an update
        if ($e['id']) {
            $upd = $this->db->prepare(
                    "UPDATE `{$this->table}` SET $cols WHERE id = :id");
            $upd->execute($e);
        } else {
            $ins = $this->db->prepare("INSERT INTO `{$this->table}` SET $cols");
            $ins->execute($e);
            $e['id'] = $this->db->lastInsertId();
        }
    }

    /**
     * Saves or updates an entity or an array of entities
     * 
     * @param array $entity either an array with its key / value pairs 
     * representing a single entity, or an array of such arrays (many entities)
     */
    public function save(&$entity) {
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
     * it returns all rows. Conditions can be used to restrict rows, the order
     * param can be used to order them, and the size and offset can be used for
     * pagination.
     * 
     * @param array of Condition objects $conditions
     * @param String $order
     * @param Number $size
     * @param Number $offset
     * @return resultset (array of arrays)
     */
    public function find($conditions = Null, $order = Null, $size = 0, $offset = 0) {
        $query = "SELECT * FROM {$this->table} ";
        if ($conditions) {
            $cols = $this->columns($conditions);
            $query .= " WHERE $cols";
        }
        if ($order) {
            $query .= " ORDER BY :_order";
            $conditions['_order'] = $order;
        }
        if ($size) {
            $query .= " LIMIT :_offset, :_size ";
            $conditions['_size'] = $size;
            $conditions['_offset'] = $offset;
        }
        $find = $this->db->prepare($query);
        $find->execute($conditions);
        return $find->fetchAll();
    }

    /**
     * Find one or more entities by id.
     * @param Number or array of numbers $id
     * @return a single result or array of results
     */
    public function findById($id) {
        if (is_array($id)) {
            $find = $this->db->prepare(
                    "SELECT * FROM {$this->table} WHERE id IN (:ids)");
            $find->execute(array("ids" => join(",", $id)));
            return $find->fetchAll();
        } else {
            $find = $this->db->prepare(
                    "SELECT * FROM {$this->table} WHERE id = :id");
            $find->execute(array("id" => $id));
            return $find->fetch();
        }
    }

    /**
     * Returns a count of how many rows in total exist with these conditions. 
     * This is usefull for pagination.
     * 
     * @param array of Condition objects $conditions
     * @return number
     */
    public function count($conditions = Null) {
        $query = "COUNT (*) from {$this->table} ";
        if ($conditions) {
            $cols = $this->columns($conditions);
            $query .= "WHERE $cols ";
        }
        $find = $this->db->prepare($query);
        $find->execute($conditions);
        return $find->fetch();
    }

    /**
     * Removes the given entity (array with key / value pairs) from the DB
     * 
     * @param array $entity (key value pairs representing an entity)
     */
    public function delete($entity) {
        $this->deleteById($entity->id);
    }

    /**
     * Removes the entity/entities with the given id(s) from the database table.
     * 
     * @param number or array of numbers $id
     */
    public function deleteById($id) {
        if (is_array($id)) {
            $ids = join(",", $id);
        } else {
            $ids = "$id";
        }
        $del = $this->db->prepare(
                "DELETE FROM {$this->table} WHERE id IN (:ids)");
        $del->execute(array("ids" => $ids));
    }

}
