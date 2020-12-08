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

use NogalEE\Cache\Session;

/**
 * Clase para controlar la caché del sistema
 *
 * @category Cache
 * @package  NogalEE
 * @author   Julian Lasso <jalasso69@misena.edu.co>
 * @license  https://github.com/jalfcolombia/nogal.ee/blob/master/LICENSE Apache2
 * @link     https://github.com/jalfcolombia/nogal.ee
 */
class Cache
{

    /**
     * Variable para el manejo del objeto de las sesiones del sistema
     *
     * @var Session
     */
    private $session;

    /**
     * Constructor de la clase Cache
     *
     * @param string $session Instancia del objeto para manejar las sesiones
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Comprueba que un archivo existe en la caché del sistema
     *
     * @param string $file Ruta y nombre del archivo
     *
     * @return bool VERDADERO si el archivo existe, de lo contrario FALSO
     */
    public function has(string $file): bool
    {
        return $this->session->has($file);
    }

    /**
     * Establece un archivo con un contenido en caché
     *
     * @param string $file    Ruta y nombre del archivo
     * @param mixed  $content Contenido a guardar
     *
     * @return void
     */
    public function set(string $file, $content, $time): void
    {
        $this->session->set($file, $content, $time);
    }

    /**
     * Obtiene el contenido de un archivo en el caché
     *
     * @param string $file Ruta y nombre del archivo
     *
     * @return mixed Contenido del archivo
     */
    public function get(string $file)
    {
        return $this->session->get($file);
    }

    /**
     * Borra un archivo del caché
     *
     * @param string $file Ruta y nombre del archivo
     *
     * @return bool
     */
    public function delete(string $file): bool
    {
        $this->session->delete($file);
        return true;
    }
}
