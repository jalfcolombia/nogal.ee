<?php

/**
 * Copyright 2020 Servicio Nacional de Aprendizaje - SENA
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
 *
 * PHP version 7.2
 *
 * @category Cache
 * @package  NogalEE
 * @author   Julian Lasso <jalasso69@misena.edu.co>
 * @license  https://github.com/jalfcolombia/nogal.ee/blob/master/LICENSE Apache2
 * @link     https://github.com/jalfcolombia/nogal.ee
 */

namespace NogalEE\Cache;

/**
 * Clase para manejar las sesiones del sistema bajo Redis
 *
 * @category Cache
 * @package  NogalEE
 * @author   Julian Lasso <jalasso69@misena.edu.co>
 * @license  https://github.com/jalfcolombia/nogal.ee/blob/master/LICENSE Apache2
 * @link     https://github.com/jalfcolombia/nogal.ee
 */
class Session
{
    
    /**
     * Nombre para base para la información guardad en redis
     */
    private const NOGAL_KEY = 'nogalDB';

    /**
     * Nombre base para guardar datos del sistema en sesión
     *
     * @var string
     */
    private $name;

    /**
     * Tiempo expresado en segundos para darle un tiempo de vida a las variables
     * de sesión de un usuario. Un -1 significa tiempo ilimitado
     *
     * @var int
     */
    private $time;

    /**
     * Variable para guardar el objeto de Redis
     *
     * @var \Redis
     */
    private $redis;

    /**
     * Constructor de la clase Session
     *
     * @param string $name Nombre de la sesión para usar como nombre del sistema\
     *                     en redis para guardar información.
     * @param int $db      Número de la base de datos a usar en Redis
     * @param string $host [opcional] IP o nombre de la máquina que contiene el
     *                     servidor de bases de datos NoSQL Redis.
     * @param int $port    [opcional] número del puerto de conexión al servidor
     *                     de bases de datos NoSQL Redis.
     */
    public function __construct(string $name, int $db, string $host = 'localhost', int $port = 6379)
    {
        $this->setName($name);
        $this->start($db, $host, $port);
    }

    /**
     * Da comienzo a la sesión estableciendo el tiempo de expiración en segundos
     *
     * @param int $time Tiempo en segundos
     *
     * @return \NogalEE\Cache\Session Instancia del objecto Session
     */
    public function start(int $db, string $host = 'localhost', int $port = 6379): Session
    {
        $this->redis = new \Redis();
        if ($this->redis->pconnect($host, $port) === false) {
            throw new \RuntimeException('Parece ser que el servidor Redis no está en línea');
        } else {
            $this->redis->select($db);
        }
        return $this;
    }

    /**
     * Establece el nombre de la cookie para manejar la sesión
     *
     * @param string   $name Nombre de la cookie
     *
     * @return \NogalEE\Cache\Session Instancia de la clase Session
     */
    public function setName(string $name): Session
    {
        $this->name = $name . '.' . self::NOGAL_KEY;
        return $this;
    }

    /**
     * Devuelve el nombre de la cookie que maneja la sesión en el cliente
     *
     * @return string Nombre de la cookie
     */
    public function getName(): string
    {
        return str_replace(self::NOGAL_KEY, '', $this->name);
    }

    /**
     * Establece una variable de sesión
     *
     * @param string $param  Nombre de la variable
     * @param mixed  $value  Valor de la variable
     * @param int    $time   Tiempo expresado en segundos. Un -1 significa
     *                       tiempo ilimitado.
     *
     * @return \NogalEE\Cache\Session Instancia de la clase Session
     */
    public function set(string $param, $value, int $time = -1): Session
    {
        $GLOBALS["{$this->name}.$param"] = $value;
        if ($time >= 0) {
            $this->redis->set("{$this->name}.$param", serialize($value), $time);
        } else {
            $this->redis->set("{$this->name}.$param", serialize($value));
        }
        return $this;
    }

    /**
     * Evalua la existencia de una variable de sesión
     *
     * @param string $param  Nombre de variable
     *
     * @return bool Falso si no existe de lo contrario Verdadero
     */
    public function has(string $param): bool
    {
        return (isset($GLOBALS["{$this->name}.$param"]) === true) ? true : ($this->redis->exists("{$this->name}.$param") === 1) ? true : false;
    }

    /**
     * Devuelve el valor de una variable de sesión
     *
     * @param string $param  Nombre de la variable
     *
     * @return mixed Valor de la variable de sesión o falso si no lo encuntra
     */
    public function get(string $param)
    {
        if (isset($GLOBALS["{$this->name}.$param"]) === false) {
            if ($this->has($param, true) === true) {
                $GLOBALS["{$this->name}.$param"] = unserialize($this->redis->get("{$this->name}.$param"));
            } else {
                return false;
            }
        }
        return $GLOBALS["{$this->name}.$param"];
    }

    /**
     * Borra una variable de sesión
     *
     * @param string $param  Nombre de la variable
     *
     * @return \NogalEE\Cache\Session Instancia de la clase Session
     */
    public function delete(string $param): Session
    {
        unset($GLOBALS["{$this->name}.$param"]);
        $this->redis->unlink("{$this->name}.$param");
        return $this;
    }

    /**
     * Destruye toda la información registrada de una sesión
     *
     * @return \NogalEE\Cache\Session Instancia de la clase Session
     */
    public function destroy(): Session
    {
        $data = $this->redis->keys("{$this->name}.*");
        foreach ($data as $param) {
            $this->redis->unlink($param);
        }
        return $this;
    }

}
