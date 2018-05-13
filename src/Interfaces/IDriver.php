<?php

/**
 * This file is part of the NogalEE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NogalEE\Interfaces;

/**
 *
 * @author Julian Lasso <jalasso69@misena.edu.co>
 */
interface IDriver
{

    /**
     * 
     * @param string $columns
     * @return self
     */
    public function select(string $columns);

    /**
     * 
     * @param string $table
     * @param string $columns
     * @return self
     */
    public function insert(string $table, string $columns);

    /**
     * 
     * @param string $table
     * @return self
     */
    public function update(string $table);

    /**
     * 
     * @param string $table
     * @return self
     */
    public function delete(string $table);

    /**
     * 
     * @param string $table
     * @return self
     */
    public function from(string $table);
    
    /**
     * 
     * @param string $next_table
     * @param array $condition
     * @return self
     */
    public function join(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition
     * @return self
     */
    public function innerJoin(string $next_table, array $condition);
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function naturalJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function naturalInnerJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function naturalLeftJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function naturalLeftOuterJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function naturalRightJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function naturalRightOuterJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition
     * @return self
     */
    public function leftJoin(string $next_table, array $condition);
    
    /**
     * 
     * @param string $next_table
     * @param array $condition
     * @return self
     */
    public function leftOuterJoin(string $next_table, array $condition);
    
    /**
     * 
     * @param string $next_table
     * @param array $condition
     * @return self
     */
    public function rightJoin(string $next_table, array $condition);
    
    /**
     * 
     * @param string $next_table
     * @param array $condition
     * @return self
     */
    public function rightOuterJoin(string $next_table, array $condition);
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function crossJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function fullJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $next_table
     * @param array $condition [opcional]
     * @return self
     */
    public function fullOuterJoin(string $next_table, array $condition = array());
    
    /**
     * 
     * @param string $sql
     * @return self
     */
    public function union(string $sql);
    
    /**
     * 
     * @param string $sql
     * @return self
     */
    public function unionAll(string $sql);

    /**
     *
     * @param string $condition
     * @param bool $raw
     */
    public function where(string $condition, bool $raw = false, string $logical_operator = '=');

    /**
     *
     * @param string $type_condition
     * @param string $condition
     * @param bool $raw
     */
    public function condition(string $type_condition, string $condition, bool $raw = false, string $logical_operator = '=');

    /**
     *
     * @param float $limit
     */
    public function limit(float $limit);

    /**
     *
     * @param int $offset
     */
    public function offset(int $offset);

    /**
     *
     * @param string $columns
     */
    public function orderBy(string $columns);

    /**
     *
     * @param string $values
     */
    public function values(string $values);

    /**
     *
     * @param string $columns_and_values
     * @param bool $raw
     */
    public function set(string $columns_and_values, bool $raw = false);
    
    /**
     * 
     * @param string $field_list
     */
    public function groupBy(string $field_list);
    
    /**
     * 
     * @param string $condition
     * @param bool $raw
     */
    public function having(string $condition, bool $raw = false);
    
    /**
     * 
     * @param string $text
     */
    public function addTextRaw(string $text);

    /**
     * 
     * @return string
     */
    public function __toString();
}
