<?php

namespace NogalEE;

use TaskConsole\Handler;

$require = __DIR__ . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'Vendor' . DIRECTORY_SEPARATOR .
    'autoload.php';
// $require = __DIR__ . DIRECTORY_SEPARATOR .
// '..' . DIRECTORY_SEPARATOR .
// 'vendor' . DIRECTORY_SEPARATOR .
// 'autoload.php';
require $require;

class Taskes extends Handler
{
    public function __construct($title, $version, $task_path)
    {
        $this->registerCommands();
        parent::__construct($title, $version, $task_path);
    }

    private function registerCommands(): void
    {
        $this->registerCommand(
            'build-schema',
            'Construye el esquema de la base de datos de la conexión indicada.',
            'bs'
        );
        $this->registerArgument(
            'build-schema',
            'driver',
            'Nombre del drive a usar.',
            'dr'
        );
        $this->registerArgument(
            'build-schema',
            'host',
            'IP o nombre del host a conectar. Ej: localhost',
            'hs'
        );
        $this->registerArgument(
            'build-schema',
            'port',
            'Puerto de conexión',
            'pr'
        );
        $this->registerArgument(
            'build-schema',
            'dbuser',
            'Usuario de la base de datos',
            'u'
        );
        $this->registerArgument(
            'build-schema',
            'dbpass',
            'Password del usuario de la base de datos',
            'p'
        );
        $this->registerArgument(
            'build-schema',
            'dbname',
            'Nombre de la base de datos',
            'db'
        );
        $this->registerArgument(
            'build-schema',
            'output',
            'Indica donde se guardará el esquema producido',
            'op'
        );
        $this->registerArgument(
            'build-schema',
            'format',
            '[opcional] Formato de salida del esquema. Por defecto php',
            'f'
        );
    }
}
