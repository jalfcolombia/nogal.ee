<?php

/**
 * This file is part of the NogalEE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NogalEE\Table;

use NogalEE\Nogal;
use NogalEE\NQL;

class Base extends Nogal
{

    private const CREATED_AT = 'created_at';

    private const UPDATED_AT = 'updated_at';

    private const DELETED_AT = 'deleted_at';

    /**
     *
     * @var NQL
     */
    protected $nql;

    /**
     * Constructor de la clase base
     *
     * @param array $config
     * @see \NogalEE\Nogal::__construct()
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->nql = new NQL($this->getDataBaseDriver());
    }

    public function restartNql(): void
    {
        $this->nql = new NQL($this->getDataBaseDriver());
    }

    public function delete(string $table, array $id): void
    {
        try {
            $this->nql->delete($table);
            $where = 0;
            foreach ($id as $logical_operator => $data) {
                if ($logical_operator === NQL::_AND or $logical_operator === NQL::_OR) {
                    foreach ($data as $column => $value) {
                        if ($where > 0) {
                            $this->nql->condition($logical_operator, $column);
                        } else {
                            $this->nql->where($column);
                            $where ++;
                        }
                    }
                } else {
                    $column = $logical_operator;
                    $value = $data;
                    $this->nql->where($column);
                }
                
                if (is_object($value) === true) {
                    $this->setQueryParam(':' . $column, $value->value, $value->type);
                } else {
                    $this->setQueryParam(':' . $column, $value, $this->detectDataType($value));
                }
            }
            echo $this->nql; exit();
            $this->execute($this->nql);
        } catch (\Exception $exc) {
            $this->throwNewExceptionFromException($exc);
        }
    }

    public function select(string $table, string $select_columns, array $joins = array(), array $where = array(), ?string $group_by = null, array $having = array(), ?string $order_by = null, object $page = null, object $class_object = null): array
    {
        try {
            $this->nql->select($select_columns)->from($table);
            if (count($joins) > 0) {
                foreach ($joins as $type => $data) {
                    $this->nql->$type($data->table, ((isset($data->condition)) ? $data->condition : array()));
                }
            }

            $this->generateCondition('where', $where);
            
            if ($group_by !== null) {
                $this->nql->groupBy($group_by);
            }
            
            $this->generateCondition('having', $having);
            
            if ($order_by !== null) {
                $this->nql->orderBy($order_by);
            }
            
            if ($page !== null) {
                $this->nql->limit($page->limit)->offset($page->offset);
            }
            
            echo $this->nql; exit();
            return $this->query($this->nql, $class_object);
        } catch (\Exception $exc) {
            $this->throwNewExceptionFromException($exc);
        }
    }

    public function save(string $table, array $columns_and_values, ?string $sequence = null): int
    {
        try {
            $values = $columns = '';
            foreach ($columns_and_values as $column => $value) {
                $columns .= $column . ', ';
                $values .= ":{$column}, ";
                if (is_object($value) === true) {
                    $this->setQueryParam(":{$column}", $value->value, $value->type);
                } else {
                    $this->setQueryParam(":{$column}", $value, $this->detectDataType($value));
                }
            }
            $columns = substr($columns, 0, - 2);
            $values = substr($values, 0, - 2);
            $this->nql->insert($table, $columns)->values($values);
            echo $this->nql;exit();
            return $this->execute($sql, $sequence);
        } catch (\Exception $exc) {
            $this->throwNewExceptionFromException($exc);
        }
    }

    public function update(string $table, array $set, array $where): bool
    {
        try {
            $this->nql->update($table);
            $columns = '';
            foreach ($set as $column => $value) {
                $columns .= "{$column}, ";
                if (is_object($value) === true) {
                    $this->setQueryParam(":{$column}", $value->value, $value->type);
                } else {
                    $this->setQueryParam(":{$column}", $value, $this->detectDataType($value));
                }
            }
            $columns = substr($columns, 0, - 2);
            $this->nql->set($columns);
            
            $this->generateCondition('where', $where);
            
            echo $this->nql;exit();
            $this->execute($this->nql);
            return true;
        } catch (\Exception $exc) {
            $this->throwNewExceptionFromException($exc);
        }
    }
    
    private function generateCondition(string $type, array $data): void
    {
        if (count($data) > 0) {
            $cicle = 0;
            // $condition - NQL::_AND NQL::_OR
            foreach ($data as $condition => $data) {
                if ($cicle === 0) {
                    $this->nql->$type($data->condition, $data->raw);
                    $cicle ++;
                } else {
                    if (is_array($data) === true) {
                        $this->addCondition($condition, $data);
                    } else {
                        if (isset($data->logical_operator) === true) {
                            $this->nql->condition($condition, $data->condition, $data->raw, $data->logical_operator);
                        } else {
                            $this->nql->condition($condition, $data->condition, $data->raw);
                        }
                    }
                }
                
                if (isset($data->raw) === true and $data->raw === false) {
                    $this->setQueryParam(':' . $data->condition, $data->value, $data->type);
                }
            }
        }
    }
    
    private function addCondition(string $condition, array $where): void
    {
        $this->nql->addTextRaw($condition . " ( ");
        foreach ($where as $condition => $data) {
            if (is_array($data) === true) {
                $this->addCondition($condition, $data);
            } else {
                if (isset($data->logical_operator) === true) {
                    $this->nql->condition($condition, $data->condition, $data->raw, $data->logical_operator);
                } else {
                    $this->nql->condition($condition, $data->condition, $data->raw);
                }
                if ($data->raw === false) {
                    $this->setQueryParam(':' . $data->condition, $data->value, $data->type);
                }
            }
        }
        $this->nql->addTextRaw(") ");
    }
}