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

/**
 * Clase controladora de la conexión a la base de datos
 *
 * @author Julian Lasso <jalasso69@misena.edu.co>
 * @package NogalEE
 */
class Nogal
{

    /**
     * Representa el tipo de dato NULL de SQL
     */
    public const PARAM_NULL = 0;

    /**
     * Representa el tipo de dato INTEGER de SQL
     */
    public const PARAM_INT = 1;

    /**
     * Representa el tipo de dato CHAR, VARCHAR de SQL, u otro tipo de datos de cadena
     */
    public const PARAM_STR = 2;

    /**
     * Representa el tipo de dato de objeto grande (LOB) de SQL
     */
    public const PARAM_LOB = 3;

    /**
     * Representa un tipo de dato booleano
     */
    public const PARAM_BOOL = 5;

    /**
     * Pila de parámetros a usar en una sentencia SQL
     *
     * @var array
     */
    private $query_params;

    /**
     * Instancia de la clase PDO
     *
     * @var \PDO
     */
    private $instance;

    /**
     * Representa una expresión preparada y, después de ejecutar la expresión, un conjunto de resultados asociados.
     *
     * @var \PDOStatement
     */
    private $stmt;

    private $sql;

    /**
     * Arreglo asociativo con los parámetros de configuración necesarios.<br><br>
     * <b>driver</b> Driver a usar para la conexión a la base de datos.
     * Ejemplo pgsql, mysql<br>
     * <b>host</b> Dirección IP donde se encuentra la base de datos. Ejemplo localhost<br>
     * <b>port</b> Puerto de conexión de la base de datos. Ejemplo 5432, 3306<br>
     * <b>dbname</b> Nombre de la base de datos a usar en la conexión. Ejemplo mydb<br>
     * <b>user</b> Usuario de la base de datos.<br>
     * <b>password</b> Contraseña del usuario de la base de datos.<br>
     * <b>unix_socket</b> Ruta física del socket de conexión a la base de datos: Ejemplo /tmp/mysql.sock<br>
     * <b>hash</b> Método a usar para encriptar las contraseñas en la base datos. Ejemplo md5, sha512<br><br>
     * Para más información del HASH ver http://php.net/manual/en/function.hash.php<br>
     * <b>persistent</b> [opcional] Valor boobleano para definir si una conexión es persistente o no.<br>
     * El valor por defecto es "true".
     *
     * @var array
     * @link http://php.net/manual/en/function.hash.php Más información para la configuración del HASH
     */
    private $config;

    /**
     * Constructor de la clase DataSource
     *
     * @param array $config Arreglo asociativo con los parámetros de configuración necesarios.
     *                      driver, host, port, dbname, user, password, hash, [opcional] persistent
     */
    public function __construct(array $config)
    {
        $this->query_params = array();
        //$this->instance = null;
        $GLOBALS['instanceNogalEE'] = null;
        if (isset($config['persistent']) === false) {
            $config['persistent'] = true;
        } elseif (is_bool($config['persistent']) === false) {
            throw new \Exception('The value for the "persistent" option must be a boolean value.');
        }
        $this->config = $config;
    }

    public function debugDumpParams(): void
    {
        print_r($this->sql);
        print_r($this->query_params);
    }

    protected function getConfigFormatDateTime(): string
    {
        return $this->config['format']['date_time'];
    }

    /**
     * Método para preparar una excepción del tipo PDOException
     *
     * @param \PDOException $exc
     * @throws \Exception
     */
    private function throwNewExceptionFromPDOException(\PDOException $exc): void
    {
        $code = (strlen($exc->getCode()) > 0) ? $exc->getCode() : '0';
        if ($exc->getPrevious() !== null) {
            throw new \Exception($exc->getMessage(), $code, $exc->getPrevious());
        } else {
            throw new \Exception($exc->getMessage());
        }
    }

    /**
     * Devuelve el DSN de conexión a una base de datos
     *
     * @return string
     */
    private function getDataSourceName(): string
    {
        switch ($this->config['driver']) {
            case 'pgsql':
                return $this->config['driver'] . ':host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['dbname'];
                break;
            case 'mysql':
                if (isset($this->config['unix_socket']) === true) {
                    return $this->config['driver'] . ':unix_socket=' . $this->config['unix_socket'] . ';dbname=' . $this->config['dbname'];
                } else {
                    return $this->config['driver'] . ':host=' . $this->config['host'] . ';port=' . $this->config['port'] . ';dbname=' . $this->config['dbname'];
                }
                break;
            case 'sqlsrv':
                // return $this->config['driver'] . ':Server=' . $this->config['host'] . ',' . $this->config['port'] . ';Database=' . $this->config['dbname'];
                return $this->config['driver'] . ':Server=' . $this->config['host'] . ';Database=' . $this->config['dbname'] . ';ConnectionPooling=' . ((isset($this->config['persistent']) and $this->config['persistent'] === true) ? 1 : 0);
                break;
            case 'oci':
                return $this->config['driver'] . ':dbname=//' . $this->config['host'] . ':' . $this->config['port'] . '/' . $this->config['dbname'];
                break;
        }
    }

    /**
     * Asigna los parámetros establecidos en la variable $db_params
     */
    private function bindParams(): void
    {
        if (count($this->query_params) > 0) {
            // var_dump($this->query_params); exit();
            foreach ($this->query_params as $param => $data) {
                $this->stmt->bindParam($param, $data['value'], $data['type']);
            }
        }
    }

    /**
     * Método para obtener los resultados como un objeto de PHP genérico o como
     * un objeto de una clase definida.
     *
     * @param \PDOStatement $stmt Estamento que contiene la respuesta a la consulta realizada.
     * @param object $class_object Clase del objeto a usar para dar respuesta de ese tipo de objeto.
     * @return mixed La respuesta puede ser en un objeto genérico de PHP o el tipo de objeto pasado en $class_object
     * @throws \Exception
     */
    private function getResultsObject(\PDOStatement $stmt, ?object $class_object)
    {
        try {
            $answer = array();
            if ($class_object === null) {
                $answer = $stmt->fetchAll(\PDO::FETCH_OBJ);
            } elseif (is_object($class_object) === true or class_exists($class_object) === true) {
                $tmp = $stmt->fetchAll();
                $i = 0;
                $class_object = new $class_object($this->config);
                foreach ($tmp as $row) {
                    $answer[$i] = clone $class_object;
                    foreach ($row as $column => $value) {
                        $column = 'set' . str_replace("_", "", ucwords($column, "_"));
                        $answer[$i]->$column($value);
                    }
                    $i ++;
                }
            } else {
                throw new \PDOException('The object "' . $class_object . '" is not a valid object');
            }
            return $answer;
        } catch (\PDOException $exc) {
            throw new \PDOException($exc->getMessage(), $exc->getCode(), $exc->getPrevious());
        }
    }

    /**
     * Devuelve el tipo de dato pertinente según el tipo de valor
     *
     * @param mixed $value
     * @return int
     */
    protected function detectDataType($value): int
    {
        switch (gettype($value)) {
            case 'string':
                return self::PARAM_STR;
                break;
            case 'integer':
                return self::PARAM_INT;
                break;
            case 'double':
                return self::PARAM_INT;
                break;
            case 'boolean':
                return self::PARAM_BOOL;
                break;
        }
    }

    /**
     * Borra el parámetro indicado
     *
     * @param string $param
     *            Parámetro a borrar del set de parámetros a trabajar en una consulta
     * @return $this
     */
    protected function deleteQueryParam(string $param): Nogal
    {
        if (isset($this->query_params[$param]) === true) {
            unset($this->query_params[$param]);
        }
        return $this;
    }

    /**
     * Borra la pila de parámetros usados en una consulta
     *
     * @return $this
     */
    protected function deleteQueryParams(): Nogal
    {
        $this->query_params = array();
        return $this;
    }

    /**
     * Define un parámetro con su valor y el tipo de parámetro para transferido
     * a la consulta SQL
     *
     * @param string $param
     *            Nombre del parámetro
     * @param mixed $value
     *            Valor del parámetro
     * @param int $type
     *            Tipo de parámetro. Ejemplo Nogal::PARAM_STR
     * @return $this
     */
    protected function setQueryParam(string $param, $value, int $type = null): Nogal
    {
        $this->query_params[$param]['value'] = $value;
        if ($type === null) {
            $this->query_params[$param]['type'] = $this->detectDataType($value);
        } else {
            $this->query_params[$param]['type'] = $type;
        }
        return $this;
    }

    /**
     * Comprueba la existencia de un parámetro definido
     *
     * @param string $param
     *            nombre del parámetro
     * @return bool
     */
    protected function hasQueryParam(string $param): bool
    {
        return isset($this->query_params[$param]);
    }

    /**
     * Retorna el nombre del controlador de la base de datos ya establecido
     *
     * @return string
     */
    protected function getConfigDataBaseDriver(): string
    {
        return $this->config['driver'];
    }

    /**
     * Devueve la instancia de conexión de la base de datos.
     *
     * @return \PDO
     * @throws \Exception
     */
    protected function getConection(): \PDO
    {
        try {
            // echo '<pre>';
            if ($GLOBALS['instanceNogalEE'] === null) {
                $options = array(
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                );
                if ($this->config['driver'] !== 'sqlsrv') {
                    $options[\PDO::ATTR_PERSISTENT] = $this->config['persistent'];
                }
                $GLOBALS['instanceNogalEE'] = new \PDO($this->getDataSourceName(), $this->config['user'], $this->config['password'], $options);
                // print_r($GLOBALS['instanceNogalEE']);
            }
            return $GLOBALS['instanceNogalEE'];
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        }
    }

    /**
     * Inicializa una transacción
     *
     * @return $this
     */
    protected function beginTransaction(): Nogal
    {
        if (isset($GLOBALS['beginTransactionNogalEE']) === false) {
            $GLOBALS['beginTransactionNogalEE'] = 1;
            $this->getConection()->beginTransaction();
        } else {
            $GLOBALS['beginTransactionNogalEE'] ++;
        }
        return $this;
    }

    /**
     * Confirma una transacción
     *
     * @return $this
     */
    protected function commit(): Nogal
    {
        if (isset($GLOBALS['beginTransactionNogalEE']) === true) {
            $GLOBALS['beginTransactionNogalEE'] --;
            if ($GLOBALS['beginTransactionNogalEE'] === 0) {
                $this->getConection()->commit();
                unset($GLOBALS['beginTransactionNogalEE']);
            }
        }
        return $this;
    }

    /**
     * Retrocede una transacción
     *
     * @return $this
     */
    protected function rollBack(): Nogal
    {
        if (isset($GLOBALS['beginTransactionNogalEE']) === true) {
            $GLOBALS['beginTransactionNogalEE'] --;
            if ($GLOBALS['beginTransactionNogalEE'] === 0) {
                unset($GLOBALS['beginTransactionNogalEE']);
                $this->getConection()->rollBack();
            }
        }
        return $this;
    }

    /**
     * SELECT
     * Método usado para realizar consultas tipo SELECT
     *
     * @param string $sql
     * @param object $class_object [opcional]
     * @return array
     * @throws \PDOException
     */
    protected function query(string $sql, object $class_object = null): array
    {
        try {
            $this->sql = $sql;
            $this->stmt = $this->getConection()->prepare($sql);
            $this->bindParams();
            $this->stmt->execute();
            return $this->getResultsObject($this->stmt, $class_object);
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        } finally {
            $this->deleteQueryParams();
        }
    }

    /**
     * INSERT, UPDATE, DELETE
     * Método para realizar consultas tipo INSERT, UPDATE y DELETE a la base datos.
     * Las consultas tipo INSERT devuelven el ID con el que fue insertado.
     * Las consultas tipo UPDATE y DELETE devuelven un cero (0).
     *
     * @param string $sql
     *            Consulta SQL
     * @param string|null $sequence
     *            [opcional] Nombre de la secuenca en PostgreSQL
     * @return int|null ID con que quedó registrado, si no es un insert entonces devuelve cero (0)
     * @throws \PDOException
     */
    protected function execute(string $sql, ?string $sequence = null): ?int
    {
        try {
            // echo '<pre>';
            // echo $sql . '<br>';
            $this->sql = $sql;
            $this->stmt = $this->getConection()->prepare($sql);
            $this->bindParams();
            $answer = $this->stmt->execute();
            preg_match('/^(insert into )/i', $sql, $matches);
            if (count($matches) > 0) {
                return $sequence !== null ? $this->getConection()->lastInsertId($sequence) : $this->getConection()->lastInsertId();
            } else {
                return $answer;
            }
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        } finally {
            $this->deleteQueryParams();
        }
    }

    protected function getColumnsLastQuery(bool $how_string = false)
    {
        $fields = str_replace(' ', '', preg_replace(array(
            '/(\sAS\s(\w+))/g',
            '/^select /i',
            '/ from (\w|\s|\W|\t|\r)+/i'
        ), array(
            '',
            '',
            ''
        ), $this->stmt->queryString));
        if ($how_string === true) {
            return $fields;
        }
        return explode(',', $fields);
    }

    /**
     * Método para preparar una excepción del tipo Exception
     *
     * @param \Exception $exc
     * @throws \Exception
     */
    protected function throwNewExceptionFromException(\Exception $exc): void
    {
        $code = (strlen($exc->getCode()) > 0) ? $exc->getCode() : '0';
        $previous = ($exc->getPrevious() !== null) ? $exc->getPrevious() : null;
        // , $code, $previous
        throw new \Exception($exc->getMessage(), $code, $previous);
    }

    protected function camelCase(string $string): string
    {
        if (isset($GLOBALS['cacheTempCamelCase'][$string]) === false) {
            $GLOBALS['cacheTempCamelCase'][$string] = str_replace(' ', '', ucwords(str_replace(array(
                '_',
                '.'
            ), ' ', $string)));
        }
        return $GLOBALS['cacheTempCamelCase'][$string];
    }
}
