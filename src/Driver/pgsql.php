<?php

/**
 * This file is part of the NogalEE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NogalEE\Driver;

use NogalEE\Interfaces\IDriver;
use NogalEE\NQL;

/**
 *
 * @author Julian Lasso <jalasso69@misena.edu.co>
 */
class pgsql implements IDriver
{

    /**
     *
     * @var string
     */
    private $nql;

    private $nqlSelect;

    private $nqlInsert;

    private $nqlUpdate;

    private $nqlDelete;

    private $nqlFrom;

    private $nqlLimit;

    private $nqlOffset;

    private $nqlOrderBy;

    private $nqlSet;

    private $nqlValues;

    private $nqlWhere;

    private $nqlWhereConditions;

    private $nqlJoins;

    private $nqlUnions;

    private $nqlHaving;

    private $nqlHavingConditions;

    private $nqlGroupBy;

    public function __construct()
    {
        $this->reset();
    }
    
    public function reset(): self
    {
        $this->nql = '';
        $this->nqlDelete = '';
        $this->nqlFrom = '';
        $this->nqlHaving = '';
        $this->nqlHavingConditions = array();
        $this->nqlInsert = '';
        $this->nqlJoins = array();
        $this->nqlLimit = '';
        $this->nqlOffset = '';
        $this->nqlOrderBy = '';
        $this->nqlSelect = '';
        $this->nqlSet = '';
        $this->nqlUnions = array();
        $this->nqlUpdate = '';
        $this->nqlValues = '';
        $this->nqlWhere = '';
        $this->nqlWhereConditions = array();
        $this->nqlGroupBy = '';
        return $this;
    }

    /**
     *
     * @param string $table
     * @return $this
     */
    public function delete(string $table): self
    {
        // $this->nql = "DELETE FROM {$table} ";
        $this->nqlDelete = "DELETE FROM {$table} ";
        return $this;
    }

    /**
     *
     * @param string $table
     * @return $this
     */
    public function from(string $table): self
    {
        // $this->nql .= "FROM {$table} ";
        $this->nqlFrom = "FROM {$table} ";
        return $this;
    }

    /**
     *
     * @param string $table
     * @param string $columns
     * @return $this
     */
    public function insert(string $table, string $columns): self
    {
        // $this->nql = "INSERT INTO {$table} ({$columns}) ";
        $this->nqlInsert = "INSERT INTO {$table} ({$columns}) ";
        return $this;
    }

    /**
     *
     * @param int $limit
     * @return $this
     */
    public function limit(float $limit): self
    {
        // $this->nql .= "LIMIT {$limit} ";
        $this->nqlLimit = "LIMIT {$limit} ";
        return $this;
    }

    /**
     *
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->nqlOffset = "OFFSET {$offset} ";
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::orderBy()
     */
    public function orderBy(string $columns): self
    {
        $this->nqlOrderBy = "ORDER BY {$columns} ";
        return $this;
    }

    /**
     *
     * @param string $columns
     * @return $this
     */
    public function select(string $columns): self
    {
        $this->nqlSelect = "SELECT {$columns} ";
        return $this;
    }

    /**
     *
     * @param mixed $columns
     * @param bool $raw
     * @return $this
     */
    public function set($columns, bool $raw = false): self
    {
        if ($raw === true) {
            $this->nqlSet = "SET {$columns} ";
        } else if ($raw === false) {
            if (is_string($columns) === true) {
                $columns = explode(',', str_replace(' ', '', $columns));
            }
            $set = '';
            foreach ($columns as $column) {
                $set .= $column . " = :{$column}, ";
            }
            $set = substr($set, 0, - 2);
            $this->nqlSet = "SET {$set} ";
        }
        return $this;
    }

    /**
     *
     * @param string $table
     * @return $this
     */
    public function update(string $table): self
    {
        $this->nqlUpdate = "UPDATE {$table} ";
        return $this;
    }

    /**
     *
     * @param string $values
     */
    public function values(string $values): self
    {
        $this->nqlValues = "VALUES ({$values}) ";
        return $this;
    }

    /**
     *
     * @param string $condition
     * @param bool $raw
     * @return $this
     */
    public function where(string $condition, bool $raw = false, string $logical_operator = '='): self
    {
        if ($raw === true) {
            $this->nqlWhere = "WHERE {$condition} ";
        } elseif ($raw === false) {
            $this->nqlWhere = "WHERE {$condition} {$logical_operator} :{$condition} ";
        }
        return $this;
    }

    /**
     *
     * @param string $type_condition
     * @param string $condition
     * @param bool $raw
     * @return $this
     */
    public function whereCondition(string $type_condition, string $condition, bool $raw = false, string $logical_operator = '='): self
    {
        if ($type_condition === 'PRE' or $type_condition === 'POS') {
            $this->nqlWhereConditions[] = $condition;
        } else if ($raw === true) {
            $this->nqlWhereConditions[] = (($type_condition === NQL::_AND or $type_condition === NQL::_OR) ? $type_condition . ' ' : '') . $condition . " ";
        } elseif ($raw === false) {
            $this->nqlWhereConditions[] = (($type_condition === NQL::_AND or $type_condition === NQL::_OR) ? $type_condition . ' ' : '') . $condition . " {$logical_operator} :{$condition} ";
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::join()
     */
    public function join(string $next_table, array $condition = array(), ?string $type_join = null): self
    {
        $type_join = ($type_join === null) ? null : $type_join . " ";
        $nql = "{$type_join}JOIN {$next_table} ";
        if (isset($condition['on']) === true) {
            $nql .= "ON {$condition['on']} ";
        } else if (isset($condition['using']) === true) {
            $nql .= "USING ({$condition['using']}) ";
        }
        $this->nqlJoins[] = $nql;
        return $this;
    }

    public function rightOuterJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'RIGHT OUTER');
    }

    public function fullJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'FULL');
    }

    public function fullOuterJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'FULL OUTER');
    }

    public function crossJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'CROSS');
    }

    public function naturalInnerJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'NATURAL INNER');
    }

    public function rightJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'RIGHT');
    }

    public function naturalJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'NATURAL');
    }

    public function leftOuterJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'LEFT OUTER');
    }

    public function innerJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'INNER');
    }

    public function naturalRightOuterJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'NATURAL RIGHT OUTER');
    }

    public function naturalLeftOuterJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'NATURAL LEFT OUTER');
    }

    public function naturalRightJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'NATURAL RIGHT');
    }

    public function naturalLeftJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'NATURAL LEFT');
    }

    public function leftJoin(string $next_table, array $condition = array()): self
    {
        return $this->join($next_table, $condition, 'LEFT');
    }

    public function groupBy(string $field_list): self
    {
        $this->nqlGroupBy = "GROUP BY {$field_list} ";
        return $this;
    }

    public function union(string $sql): self
    {
        $this->nqlUnions[] = "UNION {$sql} ";
        return $this;
    }

    public function unionAll(string $sql): self
    {
        $this->nqlUnions[] = "UNION ALL {$sql} ";
        return $this;
    }

    public function having(string $condition, bool $raw = false, string $logical_operator = '='): self
    {
        if ($raw === true) {
            $this->nqlHaving = "HAVING {$condition} ";
        } elseif ($raw === false) {
            $this->nqlHaving = "HAVING {$condition} {$logical_operator} :{$condition} ";
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::havingCondition()
     */
    public function havingCondition(string $type_condition, string $condition, bool $raw = false, string $logical_operator = '='): self
    {
        if ($type_condition === 'PRE' or $type_condition === 'POS') {
            $this->nqlHavingConditions[] = $condition;
        } else if ($raw === true) {
            $this->nqlHavingConditions[] = (($type_condition === NQL::_AND or $type_condition === NQL::_OR) ? $type_condition . ' ' : '') . $condition . " ";
        } else if ($raw === false) {
            $this->nqlHavingConditions[] = (($type_condition === NQL::_AND or $type_condition === NQL::_OR) ? $type_condition . ' ' : '') . $condition . " {$logical_operator} :{$condition} ";
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::addTextRaw()
     */
    public function addTextRaw(string $text): self
    {
        $this->nql .= $text;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $nql = '';
        if ($this->nqlSelect !== '') {
            $nql = $this->nqlSelect . $this->nqlFrom;
            foreach ($this->nqlJoins as $join) {
                $nql .= $join;
            }
            $nql .= $this->nqlWhere;
            foreach ($this->nqlWhereConditions as $condition) {
                $nql .= $condition;
            }
            $nql .= $this->nqlGroupBy . $this->nqlHaving;
            foreach ($this->nqlHavingConditions as $condition) {
                $nql .= $condition;
            }
            foreach ($this->nqlUnions as $union) {
                $nql .= $union;
            }
            $nql .= $this->nqlOrderBy . $this->nqlLimit . $this->nqlOffset . $this->nqlGroupBy;
        } else if ($this->nqlInsert !== '') {
            $nql = $this->nqlInsert . $this->nqlValues;
        } else if ($this->nqlUpdate !== '') {
            $nql = $this->nqlUpdate . $this->nqlSet . $this->nqlWhere;
            foreach ($this->nqlWhereConditions as $condition) {
                $nql .= $condition;
            }
        } else if ($this->nqlDelete !== '') {
            $nql = $this->nqlDelete . $this->nqlWhere;
            foreach ($this->nqlWhereConditions as $condition) {
                $nql .= $condition;
            }
        }
        return substr($nql, 0, - 1);
    }
}
