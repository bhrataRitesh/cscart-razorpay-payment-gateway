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
 * Fluent Query Builder for constructing and executing complex SQL queries with joins, grouping, and filtering
 */
class QueryBuilder
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $alias = '';

    /**
     * @var array
     */
    protected $fields = ['*'];

    /**
     * @var bool
     */
    protected $distinct = false;

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array
     */
    protected $wheres = [];

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $havings = [];

    /**
     * @var array
     */
    protected $orders = [];

    /**
     * @var int|null
     */
    protected $limit = null;

    /**
     * @var int|null
     */
    protected $offset = null;

    /**
     * QueryBuilder constructor.
     *
     * @param Connection|null $db
     * @param string          $table
     * @param string          $alias
     */
    public function __construct(Connection $db = null, $table = '', $alias = '')
    {
        $this->db = $db ?: Tygh::$app['db'];

        if (!empty($table)) {
            $this->from($table, $alias);
        }
    }

    /**
     * Create a new query builder instance for a table
     *
     * @param string          $table
     * @param string          $alias
     * @param Connection|null $db
     * @return static
     */
    public static function table($table, $alias = '', Connection $db = null)
    {
        return new static($db, $table, $alias);
    }

    /**
     * Set SELECT fields
     *
     * @param array|string $fields
     * @return $this
     */
    public function select($fields = ['*'])
    {
        $this->fields = is_array($fields) ? $fields : [$fields];
        return $this;
    }

    /**
     * Add SELECT fields
     *
     * @param array|string $fields
     * @return $this
     */
    public function addSelect($fields)
    {
        $fields = is_array($fields) ? $fields : [$fields];
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     * Set DISTINCT
     *
     * @param bool $distinct
     * @return $this
     */
    public function distinct($distinct = true)
    {
        $this->distinct = (bool) $distinct;
        return $this;
    }

    /**
     * Set the FROM table and optional alias
     *
     * @param string $table
     * @param string $alias
     * @return $this
     */
    public function from($table, $alias = '')
    {
        $this->table = (string) $table;
        $this->alias = (string) $alias;
        return $this;
    }

    /**
     * Add a JOIN clause
     *
     * @param array|string $table Table name, or ['table' => 'alias'], or 'table AS alias'
     * @param string       $first
     * @param string       $operator
     * @param string|null  $second
     * @param string       $type
     * @param string       $alias
     * @return $this
     */
    public function join($table, $first, $operator = '=', $second = null, $type = 'INNER', $alias = '')
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        // Parse alias from array or string syntax
        if (is_array($table)) {
            $real_table = key($table);
            $alias = current($table);
            $table = $real_table;
        } elseif (empty($alias) && preg_match('/^(\S+)\s+(?:AS\s+)?(\S+)$/i', trim($table), $matches)) {
            $table = $matches[1];
            $alias = $matches[2];
        }

        $this->joins[] = [
            'type'     => strtoupper($type),
            'table'    => $table,
            'alias'    => $alias,
            'first'    => $first,
            'operator' => $operator,
            'second'   => $second,
        ];

        return $this;
    }

    /**
     * Add a LEFT JOIN clause
     *
     * @param array|string $table
     * @param string       $first
     * @param string       $operator
     * @param string|null  $second
     * @param string       $alias
     * @return $this
     */
    public function leftJoin($table, $first, $operator = '=', $second = null, $alias = '')
    {
        return $this->join($table, $first, $operator, $second, 'LEFT', $alias);
    }

    /**
     * Add a RIGHT JOIN clause
     *
     * @param array|string $table
     * @param string       $first
     * @param string       $operator
     * @param string|null  $second
     * @param string       $alias
     * @return $this
     */
    public function rightJoin($table, $first, $operator = '=', $second = null, $alias = '')
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT', $alias);
    }

    /**
     * Add an INNER JOIN clause
     *
     * @param array|string $table
     * @param string       $first
     * @param string       $operator
     * @param string|null  $second
     * @param string       $alias
     * @return $this
     */
    public function innerJoin($table, $first, $operator = '=', $second = null, $alias = '')
    {
        return $this->join($table, $first, $operator, $second, 'INNER', $alias);
    }

    /**
     * Add a WHERE condition
     *
     * @param array|string $column
     * @param mixed        $operator
     * @param mixed        $value
     * @param string       $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                $this->where($key, '=', $val, $boolean);
            }
            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type'     => 'basic',
            'boolean'  => strtoupper($boolean),
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
        ];

        return $this;
    }

    /**
     * Add an OR WHERE condition
     *
     * @param array|string $column
     * @param mixed        $operator
     * @param mixed        $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a WHERE IN condition
     *
     * @param string $column
     * @param array  $values
     * @param string $boolean
     * @return $this
     */
    public function whereIn($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type'    => 'in',
            'boolean' => strtoupper($boolean),
            'column'  => $column,
            'values'  => $values,
            'not'     => false,
        ];

        return $this;
    }

    /**
     * Add an OR WHERE IN condition
     *
     * @param string $column
     * @param array  $values
     * @return $this
     */
    public function orWhereIn($column, array $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Add a WHERE NOT IN condition
     *
     * @param string $column
     * @param array  $values
     * @param string $boolean
     * @return $this
     */
    public function whereNotIn($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type'    => 'in',
            'boolean' => strtoupper($boolean),
            'column'  => $column,
            'values'  => $values,
            'not'     => true,
        ];

        return $this;
    }

    /**
     * Add a WHERE IS NULL condition
     *
     * @param string $column
     * @param string $boolean
     * @return $this
     */
    public function whereNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type'    => 'null',
            'boolean' => strtoupper($boolean),
            'column'  => $column,
            'not'     => false,
        ];

        return $this;
    }

    /**
     * Add a WHERE IS NOT NULL condition
     *
     * @param string $column
     * @param string $boolean
     * @return $this
     */
    public function whereNotNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type'    => 'null',
            'boolean' => strtoupper($boolean),
            'column'  => $column,
            'not'     => true,
        ];

        return $this;
    }

    /**
     * Add a WHERE BETWEEN condition
     *
     * @param string $column
     * @param mixed  $min
     * @param mixed  $max
     * @param string $boolean
     * @return $this
     */
    public function whereBetween($column, $min, $max, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type'    => 'between',
            'boolean' => strtoupper($boolean),
            'column'  => $column,
            'min'     => $min,
            'max'     => $max,
        ];

        return $this;
    }

    /**
     * Add raw WHERE SQL clause with bindings
     *
     * @param string $sql
     * @param array  $bindings
     * @param string $boolean
     * @return $this
     */
    public function whereRaw($sql, array $bindings = [], $boolean = 'AND')
    {
        $this->wheres[] = [
            'type'     => 'raw',
            'boolean'  => strtoupper($boolean),
            'sql'      => $sql,
            'bindings' => $bindings,
        ];

        return $this;
    }

    /**
     * Set GROUP BY columns
     *
     * @param array|string ...$columns
     * @return $this
     */
    public function groupBy(...$columns)
    {
        foreach ($columns as $column) {
            if (is_array($column)) {
                $this->groups = array_merge($this->groups, $column);
            } else {
                $this->groups[] = $column;
            }
        }

        return $this;
    }

    /**
     * Add a HAVING condition
     *
     * @param string $column
     * @param mixed  $operator
     * @param mixed  $value
     * @param string $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'boolean'  => strtoupper($boolean),
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
        ];

        return $this;
    }

    /**
     * Add an ORDER BY clause
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [
            'column'    => $column,
            'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
        ];

        return $this;
    }

    /**
     * Order by newest created record
     *
     * @param string $column
     * @return $this
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'DESC');
    }

    /**
     * Order by oldest created record
     *
     * @param string $column
     * @return $this
     */
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'ASC');
    }

    /**
     * Set query LIMIT and optional OFFSET
     *
     * @param int      $limit
     * @param int|null $offset
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = (int) $limit;

        if ($offset !== null) {
            $this->offset = (int) $offset;
        }

        return $this;
    }

    /**
     * Set query OFFSET
     *
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * Compile clauses into parameterized SQL and binding arguments
     *
     * @return array [string $sql, array $bindings]
     */
    public function compile()
    {
        $bindings = [];

        // 1. SELECT clause
        $sql = 'SELECT ';
        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }
        $sql .= implode(', ', $this->fields);

        // 2. FROM clause
        $sql .= ' FROM ' . $this->table;
        if (!empty($this->alias)) {
            $sql .= ' AS ' . $this->alias;
        }

        // 3. JOIN clauses
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= ' ' . $join['type'] . ' JOIN ' . $join['table'];
                if (!empty($join['alias'])) {
                    $sql .= ' AS ' . $join['alias'];
                }
                $sql .= ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
            }
        }

        // 4. WHERE clauses
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            $first = true;

            foreach ($this->wheres as $where) {
                $prefix = $first ? '' : ' ' . $where['boolean'] . ' ';
                $first = false;

                switch ($where['type']) {
                    case 'basic':
                        $ph = $this->getPlaceholderForValue($where['value']);
                        $sql .= $prefix . $where['column'] . ' ' . $where['operator'] . ' ' . $ph;
                        $bindings[] = $where['value'];
                        break;

                    case 'in':
                        $not = $where['not'] ? 'NOT IN' : 'IN';
                        $sql .= $prefix . $where['column'] . ' ' . $not . ' (?a)';
                        $bindings[] = $where['values'];
                        break;

                    case 'null':
                        $null_clause = $where['not'] ? 'IS NOT NULL' : 'IS NULL';
                        $sql .= $prefix . $where['column'] . ' ' . $null_clause;
                        break;

                    case 'between':
                        $ph_min = $this->getPlaceholderForValue($where['min']);
                        $ph_max = $this->getPlaceholderForValue($where['max']);
                        $sql .= $prefix . $where['column'] . ' BETWEEN ' . $ph_min . ' AND ' . $ph_max;
                        $bindings[] = $where['min'];
                        $bindings[] = $where['max'];
                        break;

                    case 'raw':
                        $sql .= $prefix . '(' . $where['sql'] . ')';
                        if (!empty($where['bindings'])) {
                            $bindings = array_merge($bindings, $where['bindings']);
                        }
                        break;
                }
            }
        }

        // 5. GROUP BY clause
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        // 6. HAVING clauses
        if (!empty($this->havings)) {
            $sql .= ' HAVING ';
            $first = true;

            foreach ($this->havings as $having) {
                $prefix = $first ? '' : ' ' . $having['boolean'] . ' ';
                $first = false;
                $ph = $this->getPlaceholderForValue($having['value']);
                $sql .= $prefix . $having['column'] . ' ' . $having['operator'] . ' ' . $ph;
                $bindings[] = $having['value'];
            }
        }

        // 7. ORDER BY clause
        if (!empty($this->orders)) {
            $order_parts = [];
            foreach ($this->orders as $order) {
                $order_parts[] = $order['column'] . ' ' . $order['direction'];
            }
            $sql .= ' ORDER BY ' . implode(', ', $order_parts);
        }

        // 8. LIMIT and OFFSET clauses
        if ($this->limit !== null) {
            if ($this->offset !== null) {
                $sql .= ' LIMIT ?i, ?i';
                $bindings[] = $this->offset;
                $bindings[] = $this->limit;
            } else {
                $sql .= ' LIMIT ?i';
                $bindings[] = $this->limit;
            }
        }

        return [$sql, $bindings];
    }

    /**
     * Determine suitable CS-Cart placeholder based on PHP variable type
     *
     * @param mixed $value
     * @return string '?i', '?d', or '?s'
     */
    protected function getPlaceholderForValue($value)
    {
        if (is_int($value)) {
            return '?i';
        }

        if (is_float($value)) {
            return '?d';
        }

        return '?s';
    }

    /**
     * Return compiled and escaped SQL query string
     *
     * @return string
     */
    public function toSql()
    {
        list($sql, $bindings) = $this->compile();
        return $this->db->quote($sql, ...$bindings);
    }

    /**
     * Execute query and return all matching records
     *
     * @return array
     */
    public function get()
    {
        list($sql, $bindings) = $this->compile();
        return $this->db->getArray($sql, ...$bindings);
    }

    /**
     * Execute query and return the first matching record
     *
     * @return array
     */
    public function first()
    {
        $this->limit(1);
        list($sql, $bindings) = $this->compile();
        $row = $this->db->getRow($sql, ...$bindings);
        return !empty($row) ? $row : [];
    }

    /**
     * Execute query and return a single field value
     *
     * @param string $column
     * @return mixed
     */
    public function value($column)
    {
        $this->select([$column])->limit(1);
        list($sql, $bindings) = $this->compile();
        return $this->db->getField($sql, ...$bindings);
    }

    /**
     * Execute query and return an array of values for a given column
     *
     * @param string $column
     * @return array
     */
    public function pluck($column)
    {
        $this->select([$column]);
        list($sql, $bindings) = $this->compile();
        return $this->db->getColumn($sql, ...$bindings);
    }

    /**
     * Execute COUNT query
     *
     * @param string $column
     * @return int
     */
    public function count($column = '*')
    {
        $this->select(["COUNT({$column})"]);
        list($sql, $bindings) = $this->compile();
        return (int) $this->db->getField($sql, ...$bindings);
    }

    /**
     * Check if any matching records exist
     *
     * @return bool
     */
    public function exists()
    {
        return !empty($this->first());
    }
}
