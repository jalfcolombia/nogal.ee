<?php

/**
 * Copyright 2018 Servicio Nacional de Aprendizaje - SENA
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace NogalEE\Table;

use NogalEE\Nogal;
use NogalEE\NQL;

abstract class Base extends Nogal
{

    /**
     *
     * @var NQL
     */
    protected $_nql;

    /**
     * Constructor de la clase base
     *
     * @param array $config
     * @see \NogalEE\Nogal::__construct()
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->_nql = new NQL($this->getConfigDataBaseDriver());
    }

    public function restartNql(): void
    {
        $this->_nql = new NQL($this->getConfigDataBaseDriver());
    }

    protected function deleteBase(string $table, array $id): void
    {
        try {
            $this->_nql->delete($table);
            $this->generateCondition('where', $id);
            $this->execute($this->_nql);
        } catch (\Exception $exc) {
            $this->throwNewExceptionFromException($exc);
        }
    }

    /*
     * public function select(string $table, string $select_columns, array $joins = array(), array $where = array(), ?string $group_by = null, array $having = array(), ?string $order_by = null, object $page = null, object $class_object = null): array
     * {
     * try {
     * $this->nql->select($select_columns)->from($table);
     * if (count($joins) > 0) {
     * foreach ($joins as $type => $data) {
     * $this->nql->$type($data->table, ((isset($data->condition)) ? $data->condition : array()));
     * }
     * }
     *
     * $this->generateCondition('where', $where);
     *
     * if ($group_by !== null) {
     * $this->nql->groupBy($group_by);
     * }
     *
     * $this->generateCondition('having', $having);
     *
     * if ($order_by !== null) {
     * $this->nql->orderBy($order_by);
     * }
     *
     * if ($page !== null) {
     * $this->nql->limit($page->limit)->offset($page->offset);
     * }
     *
     * echo $this->nql;
     * exit();
     * return $this->query($this->nql, $class_object);
     * } catch (\Exception $exc) {
     * $this->throwNewExceptionFromException($exc);
     * }
     * }
     */
    
    protected function saveBase(string $table, array $columns_and_values, string $sequence = null): int
    {
        try {
            $values = $columns = '';
            foreach ($columns_and_values as $column => $value) {
                $columns .= $column . ', ';
                $values .= ":{$this->camelCase($column)}, ";
                if (is_object($value) === true) {
                    $this->setQueryParam(":{$this->camelCase($column)}", $value->value, $value->type);
                } else {
                    $this->setQueryParam(":{$this->camelCase($column)}", $value, $this->detectDataType($value));
                }
            }
            $columns = substr($columns, 0, - 2);
            $values = substr($values, 0, - 2);
            $this->_nql->insert($table, $columns)->values($values);
            /*echo '<pre>';
            echo $this->_nql;
            echo '</pre>';*/
            return $this->execute($this->_nql, $sequence);
        } catch (\Exception $exc) {
            $this->throwNewExceptionFromException($exc);
        }
    }

    protected function updateBase(string $table, array $set, array $where): void
    {
        try {
            $this->_nql->update($table);
            $columns = '';
            foreach ($set as $column => $value) {
                $columns .= "{$column}, ";
                if (is_object($value) === true) { 
                    $this->setQueryParam(":{$this->camelCase($column)}", $value->value, $value->type);
                } else {
                    $this->setQueryParam(":{$this->camelCase($column)}", $value, $this->detectDataType($value));
                }
            }
            $columns = substr($columns, 0, - 2);
            $this->_nql->set($columns);
            $this->generateCondition('where', $where);
            $this->execute($this->_nql);
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
                $data->raw = (isset($data->raw) === true) ? $data->raw : false;
                if ($cicle === 0) {
                    $this->_nql->$type(((isset($data->condition) === true) ? $data->condition : $condition), $data->raw);
                    $cicle ++;
                } else {
                    $type_condition = "{$type}Condition";
                    if (is_array($data) === true) {
                        $this->addCondition($condition, $data, $type_condition);
                    } elseif (isset($data->logical_operator) === true) {
                        $this->_nql->$type_condition($condition, $data->condition, $data->raw, $data->logical_operator);
                    } else {
                        $this->_nql->$type_condition($condition, $data->condition, $data->raw);
                    }
                }
                
                if (isset($data->raw) === true and $data->raw === false) {
                    $this->setQueryParam(':' . ((isset($data->condition) === true) ? $this->camelCase($data->condition) : $condition), $data->value, ((isset($data->type) === true) ? $data->type : $this->detectDataType($data->value)));
                }
            }
        }
    }

    private function addCondition(string $condition, array $where, $type_condition): void
    {
        $this->_nql->$type_condition("PRE", "{$condition} ( ");
        foreach ($where as $condition => $data) {
            if (is_array($data) === true) {
                $this->addCondition($condition, $data, $type_condition);
            } else {
                if (isset($data->logical_operator) === true) {
                    $this->_nql->$type_condition($condition, $data->condition, $data->raw, $data->logical_operator);
                } else {
                    $this->_nql->$type_condition($condition, $data->condition, $data->raw);
                }
                if ($data->raw === false) {
                    $this->setQueryParam(':' . $data->condition, $data->value, $data->type);
                }
            }
        }
        $this->_nql->$type_condition("POS", ") ");
    }
}
