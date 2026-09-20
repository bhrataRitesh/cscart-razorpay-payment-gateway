<?php
/***************************************************************************
*                                                                          *
*   (c) 2026 Razorpay Payment Gateway Addon for CS-Cart                    *
*                                                                          *
****************************************************************************/

namespace Tygh\Addons\Razorpay\Model;

use Tygh\Database\Connection;
use Tygh\Tygh;

/**
 * Abstract Base Model providing query construction and database CRUD operations
 */
abstract class BaseModel
{
    /**
     * @var string Database table name with '?:' prefix (e.g. '?:razorpay_transactions')
     */
    protected $table;

    /**
     * @var string Primary key field name (e.g. 'order_id')
     */
    protected $primary_key = 'id';

    /**
     * @var Connection
     */
    protected $db;

    /**
     * BaseModel constructor.
     *
     * @param Connection|null $db
     */
    public function __construct(Connection $db = null)
    {
        $this->db = $db ?: Tygh::$app['db'];
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get primary key column name
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primary_key;
    }

    /**
     * Create a new fluent QueryBuilder for this model's table
     *
     * @param string $alias Optional table alias (e.g. 't')
     * @return QueryBuilder
     */
    public function query($alias = '')
    {
        return new QueryBuilder($this->db, $this->table, $alias);
    }

    /**
     * Alias for query()
     *
     * @param string $alias
     * @return QueryBuilder
     */
    public function newQuery($alias = '')
    {
        return $this->query($alias);
    }

    /**
     * Perform a joined query using structured join array
     *
     * @param array        $joins Array of ['table' => ..., 'first' => ..., 'operator' => ..., 'second' => ..., 'type' => ...]
     * @param array        $conditions
     * @param array|string $fields
     * @param string       $alias
     * @return array
     */
    public function joinWith(array $joins, array $conditions = [], $fields = ['*'], $alias = '')
    {
        $qb = $this->query($alias)->select($fields)->where($conditions);

        foreach ($joins as $join) {
            $type = !empty($join['type']) ? $join['type'] : 'INNER';
            $operator = !empty($join['operator']) ? $join['operator'] : '=';
            $qb->join($join['table'], $join['first'], $operator, $join['second'], $type);
        }

        return $qb->get();
    }

    /**
     * Insert a new record into table
     * Builds: INSERT INTO {table} ?e
     *
     * @param array $data Column-value associative array
     * @return int|bool Insert ID or query result
     */
    public function insert(array $data)
    {
        $query = "INSERT INTO {$this->table} ?e";
        return $this->db->query($query, $data);
    }

    /**
     * Insert or replace a record into table
     * Builds: REPLACE INTO {table} ?e
     *
     * @param array $data Column-value associative array
     * @return int|bool Query result
     */
    public function replace(array $data)
    {
        $query = "REPLACE INTO {$this->table} ?e";
        return $this->db->query($query, $data);
    }

    /**
     * Find a single record matching conditions
     * Builds: SELECT {fields} FROM {table} WHERE ?w LIMIT 1
     *
     * @param array $conditions Associative array of conditions (key => val)
     * @param array|string $fields Fields to select (array or string)
     * @return array Record array or empty array if not found
     */
    public function find(array $conditions = [], $fields = ['*'])
    {
        $fields_str = is_array($fields) ? implode(', ', $fields) : $fields;

        if (!empty($conditions)) {
            $query = "SELECT {$fields_str} FROM {$this->table} WHERE ?w LIMIT 1";
            $row = $this->db->getRow($query, $conditions);
        } else {
            $query = "SELECT {$fields_str} FROM {$this->table} LIMIT 1";
            $row = $this->db->getRow($query);
        }

        return !empty($row) ? $row : [];
    }

    /**
     * Find a single record by its primary key
     *
     * @param int|string $id Primary key value
     * @param array|string $fields
     * @return array
     */
    public function findById($id, $fields = ['*'])
    {
        return $this->find([$this->primary_key => $id], $fields);
    }

    /**
     * Find all records matching conditions
     * Builds: SELECT {fields} FROM {table} WHERE ?w ORDER BY ?p LIMIT ?i, ?i
     *
     * @param array $conditions
     * @param array|string $fields
     * @param string $order_by E.g. 'order_id DESC'
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll(array $conditions = [], $fields = ['*'], $order_by = '', $limit = 0, $offset = 0)
    {
        $fields_str = is_array($fields) ? implode(', ', $fields) : $fields;
        $args = [];

        $query = "SELECT {$fields_str} FROM {$this->table}";

        if (!empty($conditions)) {
            $query .= " WHERE ?w";
            $args[] = $conditions;
        }

        if (!empty($order_by)) {
            $query .= " ORDER BY ?p";
            $args[] = $order_by;
        }

        if ($limit > 0) {
            $query .= " LIMIT ?i, ?i";
            $args[] = (int) $offset;
            $args[] = (int) $limit;
        }

        return $this->db->getArray($query, ...$args);
    }

    /**
     * Update records matching conditions
     * Builds: UPDATE {table} SET ?u WHERE ?w
     *
     * @param array $conditions Associative array of conditions
     * @param array $data Associative array of column => new value
     * @return int|bool Affected rows or query result
     */
    public function update(array $conditions, array $data)
    {
        if (empty($conditions) || empty($data)) {
            return false;
        }

        $query = "UPDATE {$this->table} SET ?u WHERE ?w";
        return $this->db->query($query, $data, $conditions);
    }

    /**
     * Update record by primary key
     *
     * @param int|string $id
     * @param array $data
     * @return int|bool
     */
    public function updateById($id, array $data)
    {
        return $this->update([$this->primary_key => $id], $data);
    }

    /**
     * Delete records matching conditions
     * Builds: DELETE FROM {table} WHERE ?w
     *
     * @param array $conditions Associative array of conditions
     * @return int|bool Affected rows or query result
     */
    public function delete(array $conditions)
    {
        if (empty($conditions)) {
            return false;
        }

        $query = "DELETE FROM {$this->table} WHERE ?w";
        return $this->db->query($query, $conditions);
    }

    /**
     * Delete record by primary key
     *
     * @param int|string $id
     * @return int|bool
     */
    public function deleteById($id)
    {
        return $this->delete([$this->primary_key => $id]);
    }

    /**
     * Count records matching conditions
     * Builds: SELECT COUNT(*) FROM {table} WHERE ?w
     *
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = [])
    {
        if (!empty($conditions)) {
            $query = "SELECT COUNT(*) FROM {$this->table} WHERE ?w";
            return (int) $this->db->getField($query, $conditions);
        }

        $query = "SELECT COUNT(*) FROM {$this->table}";
        return (int) $this->db->getField($query);
    }
}
