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

    public function __construct()
    {
        $this->nql = '';
    }

    /**
     *
     * @param string $table
     * @return $this
     */
    public function delete(string $table): self
    {
        $this->nql = "DELETE FROM {$table} ";
        return $this;
    }

    /**
     *
     * @param string $table
     * @return $this
     */
    public function from(string $table): self
    {
        $this->nql .= "FROM {$table} ";
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
        $this->nql = "INSERT INTO {$table} ({$columns}) ";
        return $this;
    }

    /**
     *
     * @param int $limit
     * @return $this
     */
    public function limit(float $limit): self
    {
        $this->nql .= "LIMIT {$limit} ";
        return $this;
    }

    /**
     *
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->nql .= "OFFSET {$offset} ";
        return $this;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \NogalEE\Interfaces\IDriver::orderBy()
     */
    public function orderBy(string $columns): self
    {
        $this->nql .= "ORDER BY {$columns} ";
        return $this;
    }

    /**
     *
     * @param string $columns
     * @return $this
     */
    public function select(string $columns): self
    {
        $this->nql = "SELECT {$columns} ";
        return $this;
    }

    /**
     *
     * @param string $columnsAndValues
     * @param bool $raw
     * @return $this
     */
    public function set(string $columnsAndValues, bool $raw = false): self
    {
        if ($raw === true) {
            $this->nql .= "SET {$columnsAndValues} ";
        } else if ($raw === false) {
            $columnsAndValues = str_replace(' ', '', $columnsAndValues);
            $data = explode(',', $columnsAndValues);
            $set = '';
            foreach ($data as $column) {
                $set .= $column . " = :{$column}, ";
            }
            $set = substr($set, 0, - 2);
            $this->nql .= "SET {$set} ";
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
        $this->nql = "UPDATE {$table} ";
        return $this;
    }

    /**
     *
     * @param string $values
     */
    public function values(string $values): self
    {
        $this->nql .= "VALUES ({$values}) ";
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
            $this->nql .= "WHERE {$condition} ";
        } elseif ($raw === false) {
            $this->nql .= "WHERE {$condition} {$logical_operator} :{$condition} ";
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
    public function condition(string $type_condition, string $condition, bool $raw = false, string $logical_operator = '='): self
    {
        if ($raw === true) {
            $this->nql .= (($type_condition === NQL::_AND or $type_condition === NQL::_OR) ? $type_condition . ' ' : '') . $condition . " ";
        } elseif ($raw === false) {
            $this->nql .= (($type_condition === NQL::_AND or $type_condition === NQL::_OR) ? $type_condition . ' ' : '') . $condition . " {$logical_operator} :{$condition} ";
        }
        return $this;
    }

    public function rightOuterJoin(string $next_table, array $condition): self
    {}

    public function unionAll(string $sql): self
    {
        $this->nql .= "UNION ALL {$sql} ";
        return $this;
    }

    public function fullJoin(string $next_table, array $condition = []): self
    {}

    public function fullOuterJoin(string $next_table, array $condition = []): self
    {}

    public function crossJoin(string $next_table, array $condition = []): self
    {}

    public function naturalInnerJoin(string $next_table, array $condition = []): self
    {}

    public function rightJoin(string $next_table, array $condition): self
    {}

    public function naturalJoin(string $next_table, array $condition = []): self
    {}

    /**
     * 
     * {@inheritDoc}
     * @see \NogalEE\Interfaces\IDriver::join()
     */
    public function join(string $next_table, array $condition = array()): self
    {
        $this->nql .= "JOIN {$next_table} ";
        if (isset($condition['on']) === true) {
            $this->nql .= "ON {$condition['on']} ";
        } else if (isset($condition['using']) === true) {
            $this->nql .= "USING ({$condition['using']}) ";
        }
        return $this;
    }

    public function leftOuterJoin(string $next_table, array $condition): self
    {}

    public function innerJoin(string $next_table, array $condition): self
    {}

    public function naturalRightOuterJoin(string $next_table, array $condition = []): self
    {}

    public function naturalLeftOuterJoin(string $next_table, array $condition = []): self
    {}

    public function naturalRightJoin(string $next_table, array $condition = []): self
    {}

    public function union(string $sql): self
    {
        $this->nql .= "UNION {$sql} ";
        return $this;
    }

    public function naturalLeftJoin(string $next_table, array $condition = []): self
    {}

    public function leftJoin(string $next_table, array $condition): self
    {}
    
    public function groupBy(string $field_list): self
    {
        $this->nql .= "GROUP BY {$field_list} ";
        return $this;
    }

    public function having(string $condition, bool $raw = false, string $logical_operator = '='): self
    {
        if ($raw === true) {
            $this->nql .= "HAVING {$condition} ";
        } elseif ($raw === false) {
            $this->nql .= "HAVING {$condition} {$logical_operator} :{$condition} ";
        }
        return $this;
    }
    
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
        return substr($this->nql, 0, - 1);
    }

}
