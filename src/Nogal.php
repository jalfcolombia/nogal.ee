<?php

/**
 * This file is part of the NogalEE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    protected $config;

    /**
     * Constructor de la clase DataSource
     *
     * @param array $config
     *            Arreglo asociativo con los parámetros de configuración
     *            necesarios.<br>driver, host, port, dbname, user, password, hash.<br><br>
     */
    public function __construct(array $config)
    {
        $this->query_params = array();
        $this->instance = null;
        if (isset($config['persistent']) === false) {
            $config['persistent'] = true;
        } else if (is_bool($config['persistent']) === false) {
            throw new \Exception('The value for the "persistent" option must be a boolean value.');
        } else {
            $config['persistent'] = false;
        }
        $this->config = $config;
    }
    
    public function debugDumpParams(): void
    {
        $this->stmt->debugDumpParams();
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
        $previous = ($exc->getPrevious() !== null) ? $exc->getPrevious() : '';
        // , $code, $previous
        throw new \Exception($exc->getMessage());
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
                return $this->config['driver'] . ':Server=' . $this->config['host'] . ',' . $this->config['port'] . ';Database=' . $this->config['dbname'];
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
            foreach ($this->query_params as $param => $data) {
                $this->stmt->bindParam($param, $data['value'], $data['type']);
            }
        }
    }

    /**
     * Método para obtener los resultados como un objeto de PHP genérico o como
     * un objeto de una clase definida.
     *
     * @param \PDOStatement $stmt
     *            Estamento que contiene la respuesta a la consulta realizada.
     * @param object $class_object
     *            Clase del objeto a usar para dar respuesta de ese tipo de objeto.
     * @return mixed La respuesta puede ser en un objeto genérico de PHP o el tipo de objeto pasado en $class_object
     * @throws \Exception
     */
    private function getResultsObject(\PDOStatement $stmt, object $class_object)
    {
        try {
            $answer = array();
            if ($class_object === null) {
                $answer = $stmt->fetchAll(\PDO::FETCH_OBJ);
            } else if (class_exists($class_object) === true) {
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
    protected function detectDataType ($value): int
    {
        switch (gettype($value)) {
            case 'string':
                return Nogal::PARAM_STR;
                break;
            case 'integer':
                return Nogal::PARAM_INT;
                break;
            case 'double':
                return Nogal::PARAM_INT;
                break;
            case 'boolean':
                return Nogal::PARAM_BOOL;
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
    protected function setQueryParam(string $param, $value, ?int $type = null): Nogal
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
    protected function getDataBaseDriver(): string
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
            if ($this->instance === null) {
                $this->instance = new \PDO($this->getDataSourceName(), $this->config['user'], $this->config['password'], array(
                    \PDO::ATTR_PERSISTENT => $this->config['persistent'],
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ));
            }
            return $this->instance;
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
        $this->instance->beginTransaction();
        return $this;
    }

    /**
     * Confirma una transacción
     *
     * @return $this
     */
    protected function commit(): Nogal
    {
        $this->instance->commit();
        return $this;
    }

    /**
     * Retrocede una transacción
     *
     * @return $this
     */
    protected function rollBack(): Nogal
    {
        $this->instance->rollBack();
        return $this;
    }

    /**
     * SELECT
     * Método usado para realizar consultas tipo SELECT
     *
     * @param string $sql
     * @param object $class_object
     *            [opcional]
     * @return array
     * @throws \PDOException
     */
    protected function query(string $sql, object $class_object = null): array
    {
        try {
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
            $this->stmt = $this->getConection()->prepare($sql);
            $this->bindParams();
            $this->stmt->execute();
            preg_match('/^(insert into )/i', $sql, $matches);
            if (count($matches) > 0) {
                return $sequence !== null ? $this->getConection()->lastInsertId($sequence) : $this->getConection()->lastInsertId();
            } else {
                return null;
            }
        } catch (\PDOException $exc) {
            $this->throwNewExceptionFromPDOException($exc);
        } finally {
            $this->deleteQueryParams();
        }
    }
    
    protected function getColumnsLastQuery(bool $how_string = false)
    {
        $fields = str_replace(' ', '', preg_replace(array('/(\sAS\s(\w+))/g', '/^select /i', '/ from (\w|\s|\W|\t|\r)+/i'), array('','',''), $this->stmt->queryString));
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
        throw new \Exception($exc->getMessage());
    }
}