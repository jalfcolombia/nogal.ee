<?php
require '../vendor/autoload.php';
require '../output/Base/MaestroBase.php';
require '../output/Maestro.php';
require '../output/Base/DetalleBase.php';
require '../output/Detalle.php';

require '../output/Base/Query/MaestroBaseQuery.php';
require '../output/Query/MaestroQuery.php';

function convert($size)
{
    $unit = array(
        'b',
        'kb',
        'mb',
        'gb',
        'tb',
        'pb'
    );
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

$config = array(
    'driver' => 'sqlsrv',
    'host' => 'localhost\\SQLEXPRESS',
    'port' => 1434,
    'dbname' => 'prueba',
    'user' => 'sa',
    'password' => 'sqlx32',
    'hash' => 'sha512',
    'persistent' => true,
    'format' => array(
        'date' => 'd-m-Y',
        'time' => 'h:i:s',
        'date_time' => 'd-m-Y h:i:s'
    )
);

use MyApp\Model\Maestro;
use MyApp\Model\Detalle;
use MyApp\Model\Query\MaestroQuery;
use NogalEE\NQL;

try {
    
    /*$detalle1 = new Detalle();
    $detalle1->setArticulo('cuadernos')
        ->setCantidad(3)
        ->setValor(500)
        ->setTotal((500 * 3));
    $detalle2 = new Detalle();
    $detalle2->setArticulo('lapiceros')
        ->setCantidad(5)
        ->setValor(600)
        ->setTotal((600 * 5));
    $detalle3 = new Detalle();
    $detalle3->setArticulo('rebomba')
        ->setCantidad(2)
        ->setValor(1500)
        ->setTotal((1500 * 2));
    
    $maestro = new Maestro();
    $maestro->setCedula('12312333')
        ->setNombre('Julian Lasso Figueroa')
        ->setDireccion('Carrera 10 # 56 - 36 La Base')
        ->setTelefono('311 742 0875')
        ->setCorreo('jalasso6933@misena.edu.co')
        ->setDetalle($detalle1)
        ->setDetalle($detalle2)
        ->setDetalle($detalle3)
        ->save();
    
    echo 'maestro -> ' . $maestro->getId() . '<br>';
    echo 'detalle -> ' . $detalle1->getId() . '<br>';
    echo 'detalle -> ' . $detalle2->getId() . '<br>';
    echo 'detalle -> ' . $detalle3->getId() . '<br>';*/
    
    // echo $maestro->getId();
    
    /*$detalle = new Detalle();
    $detalle->setId(114)->setArticulo('bombillas de Kevin');
    
    $maestro = new Maestro();
    $maestro->setId(104)->setNombre('Kevin')->setCedula(312)->setDetalle($detalle)->update();*/
    
    //$maestro = new Maestro();
    //$maestro->setId(104)->delete();
    // $maestro->setId(4)->delete(false);
    
    echo '<pre>';
    // $maestro = MaestroQuery::select()->find();
    $maestro = MaestroQuery::select();
    //$maestro->findById(104);
    echo $maestro->findByCedula(123)->getOne()->getCorreo();
    //$maestro = MaestroQuery::select()->find();
    
    /* @var $item Maestro */    
    /*foreach ($maestro as $item) {
        print_r($item->getNombre() . "\n");
    }*/
    echo '</pre>';
    
} catch (Exception $exc) {
    echo $exc->getMessage();
    echo '<pre>';
    echo 'Code: ' . $exc->getCode() . '<br>';
    echo 'File: ' . $exc->getFile() . '<br>';
    echo 'Line: ' . $exc->getLine() . '<br>';
    print_r($exc->getTrace());
} finally {
    echo convert(memory_get_usage(true));
}