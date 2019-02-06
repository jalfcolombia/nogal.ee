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

namespace NogalEE;

use NogalEE\Interfaces\IDriver;

/**
 * Nogal Query Language (NQL) proporciona los métodos necesarios<br>
 * para crear consultas SQL propias para ser ejecutadas con Nogal.<br>
 * Se trabaja constantemente para cubrir el mayo porcentaje posible<br>
 * de las consultas SQL en los diferentes motordes de Bases de Datos.
 *
 * @author Julian Lasso <jalasso69@misena.edu.co>
 */
class NQL implements IDriver
{

    /**
     * Valor para condicional AND de SQL
     */
    public const _AND = 'AND';

    /**
     * Valor para condicional OR de SQL
     */
    public const _OR = 'OR';

    /**
     * Valor para orden ascendente
     */
    public const ASC = 'ASC';

    /**
     * Valor para orden descendente
     */
    public const DESC = 'DESC';

    public const JOIN = 'join';

    public const JOIN_INNER = 'innerJoin';

    public const JOIN_NATURAL = 'naturalJoin';

    public const JOIN_NATURAL_INNER = 'naturalInnerJoin';

    public const JOIN_NATURAL_LEFT = 'naturalLeftJoin';

    public const JOIN_NATURAL_LEFT_OUTER = 'naturalLeftOuterJoin';

    public const JOIN_NATURAL_RIGHT = 'naturalRightJoin';

    public const JOIN_NATURAL_RIGHT_OUTER = 'naturalRightOuterJoin';

    public const JOIN_LEFT = 'leftJoin';

    public const JOIN_LEFT_OUTER = 'leftOuterJoin';

    public const JOIN_RIGHT = 'rightJoin';

    public const JOIN_RIGHT_OUTER = 'rightOuterJoin';

    public const JOIN_CROSS = 'crossJoin';

    public const JOIN_FULL = 'fullJoin';

    public const JOIN_FULL_OUTER = 'fullOuterJoin';

    /**
     * Controlador de PDO a usar, Ejemplo: pgsql, mysql
     *
     * @var string
     */
    private $driver;

    /**
     * Variable contenedora del objeto referente a la clase del motor de
     * bases de datos para NQL.
     *
     * @var object
     */
    private $driver_class;

    /**
     * Variable que destinada a guardar una instancia de la clase NQL
     *
     * @var NQL
     */
    private static $instance = null;

    /**
     * Constructor de la clase NQL
     *
     * @param string $driver
     *            Controlador de base de datos a usar
     */
    public function __construct(string $driver)
    {
        $this->driver = $driver;
        $class = 'NogalEE\\Driver\\' . $driver;
        $this->driver_class = new $class();
    }
    
    public function reset(): self
    {
        return $this->driver_class->reset();
    }

    public static function create(string $driver): NQL
    {
        if (self::$instance === null) {
            self::$instance = new NQL($driver);
        }
        return self::$instance;
    }

    /**
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     *
     * @param string $driver
     * @return NQL
     */
    public function setDriver(string $driver): NQL
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Agrega una condición AND u OR según la necesidad
     *
     * @param string $typeCondition
     *            Condicionales NQL::_AND u NQL::_OR
     * @param string $condition
     *            Condición a utilizar. Ejemplo id = 33
     * @param bool $raw
     *            true: "id = 69" o false: "id" = "id = :id" esta última
     *            opción permite enlazar (bind) con la clase PDOStatement
     * @return $this
     */
    /*public function condition(string $typeCondition, string $condition, bool $raw = false, string $logical_operator = '='): NQL
    {
        $this->driver_class->condition($typeCondition, $condition, $raw, $logical_operator);
        return $this;
    }*/

    /**
     * Inicializa un SQL con la palabra clave DELETE
     *
     * @param string $table
     *            Tabla de donde se borrará la información
     * @return $this
     */
    public function delete(string $table): NQL
    {
        $this->driver_class->delete($table);
        return $this;
    }

    /**
     * Complementa el uso de las palabra clave SELECT
     *
     * @param string $table
     *            Tabla en la cual se realizará la consulta
     * @return $this
     */
    public function from(string $table): NQL
    {
        $this->driver_class->from($table);
        return $this;
    }

    /**
     * Inicializa un SQL con la palabra clave INSERT INTO
     *
     * @param string $table
     * @param string $columns
     * @return $this
     */
    public function insert(string $table, string $columns): NQL
    {
        $this->driver_class->insert($table, $columns);
        return $this;
    }

    /**
     * Agrega a la consulta SQL la clausula LIMIT
     *
     * @param float $limit
     * @return $this
     */
    public function limit(float $limit): NQL
    {
        $this->driver_class->limit($limit);
        return $this;
    }

    /**
     * Agrega a la consulta SQL la clausula OFFSET (Clusula complementaria de LIMIT)
     *
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): NQL
    {
        $this->driver_class->offset($offset);
        return $this;
    }

    /**
     * Finaliza una consulta SQL con la clausula ORDER BY
     *
     * @param string $columns
     * @return $this
     */
    public function orderBy(string $columns): NQL
    {
        $this->driver_class->orderBy($columns);
        return $this;
    }

    /**
     * Inicializa un SQL con la palabra clave SELECT
     *
     * @param string $columns
     * @return $this
     */
    public function select(string $columns): NQL
    {
        $this->driver_class->select($columns);
        return $this;
    }

    /**
     * Complementa una sentencia SQL inicializada con la palabra clave UPDATE
     *
     * @param mixed $columns
     * @param bool $raw
     * @return $this
     */
    public function set($columns, bool $raw = false): NQL
    {
        $this->driver_class->set($columns, $raw);
        return $this;
    }

    /**
     * Inicializa un SQL con la palabra clave UPDATE
     *
     * @param string $table
     * @return $this
     */
    public function update(string $table): NQL
    {
        $this->driver_class->update($table);
        return $this;
    }

    /**
     * Complementa una sentencia SQL inicializada con la palabra clave INSERT INTO
     *
     * @param string $values
     * @return $this
     */
    public function values(string $values): NQL
    {
        $this->driver_class->values($values);
        return $this;
    }

    /**
     * Complementa una sentencia SQL con la clausula WHERE
     *
     * @param string $condition
     * @param bool $raw
     * @return $this
     */
    public function where(string $condition, bool $raw = false, string $logical_operator = '='): NQL
    {
        $this->driver_class->where($condition, $raw, $logical_operator);
        return $this;
    }

    public function whereCondition(string $type_condition, string $condition, bool $raw = false, string $logical_operator = '='): NQL
    {
        $this->driver_class->whereCondition($type_condition, $condition, $raw, $logical_operator);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function join(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->join($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula INNER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function innerJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->innerJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula NATURAL JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function naturalJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->naturalJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula NATURAL INNER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function naturalInnerJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->naturalInnerJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula NATURAL LEFT JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function naturalLeftJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->naturalLeftJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula NATURAL LEFT OUTER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function naturalLeftOuterJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->naturalLeftOuterJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula NATURAL RIGHT JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function naturalRightJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->naturalRightJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula NATURAL RIGHT OUTER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function naturalRightOuterJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->naturalRightOuterJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula LEFT JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function leftJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->leftJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula LEFT OUTER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function leftOuterJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->leftOuterJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula RIGHT JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function rightJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->rightJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula RIGHT OUTER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function rightOuterJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->rightOuterJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula CROSS JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')
     * @return \NogalEE\NQL
     */
    public function crossJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->crossJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula FULL JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function fullJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->fullJoin($next_table, $condition);
        return $this;
    }

    /**
     * Completa una sentencia SQL con la clausula FULL OUTER JOIN
     *
     * @param string $next_table
     *            Nombre de la tabla a enlazar
     * @param array $condition
     *            [opcional] array('on' => 'table_parent.field1 = table_child_field2')<br>
     *            array('using' => 'table_child.field2')
     * @return \NogalEE\NQL
     */
    public function fullOuterJoin(string $next_table, array $condition = array()): NQL
    {
        $this->driver_class->fullOuterJoin($next_table, $condition);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::unionAll()
     */
    public function unionAll(string $sql): NQL
    {
        $this->driver_class->unionAll($sql);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::union()
     */
    public function union(string $sql): NQL
    {
        $this->driver_class->union($sql);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::groupBy()
     */
    public function groupBy(string $field_list): NQL
    {
        $this->driver_class->groupBy($field_list);
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     * @see \NogalEE\Interfaces\IDriver::having()
     */
    public function having(string $condition, bool $raw = false): NQL
    {
        $this->driver_class->having($condition, $raw);
        return $this;
    }

    public function havingCondition(string $type_condition, string $condition, bool $raw = false, string $logical_operator = '=')
    {
        $this->driver_class->havingCondition($type_condition, $condition, $raw, $logical_operator);
        return $this;
    }

    /**
     * Agrega texto crudo a la sentencia NQL
     *
     * @param string $text
     * @return NQL
     */
    public function addTextRaw(string $text): NQL
    {
        $this->driver_class->addTextRaw($text);
        return $this;
    }

    public function isQueryInConstruction(): bool
    {
        return $this->driver_class->isQueryInConstruction();
    }

    /**
     * Retorna la consulta creada con NQL
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->driver_class->__toString();
    }
}
