<?php

/*$data = array(1, 1., NULL, 'foo', '2', true, 0, 1, 'true', 'false', '2.3');
foreach ($data as $value) {
    echo $value . ': '. gettype($value), "<br>";
}exit();*/

/*$n = '0';
if (is_bool($n) === true) {
    echo 'es booleano';
} else if (is_numeric($n) === true and is_string($n) === true) {
    echo 'es string';
} else if(is_string($n) === true) {
    echo 'es string';
} else if (is_numeric($n) === true) {
    echo 'es un número';
} exit();*/

require '../vendor/autoload.php';

use NogalEE\Table\Base;
use NogalEE\Nogal;
use NogalEE\NQL;

$base = new Base(array(
    'driver' => 'pgsql',
    'host' => 'localhost',
    'port' => 5432,
    'dbname' => 'pruebas',
    'user' => 'postgres',
    'password' => 'sqlx32',
    'hash' => 'md5'
));

/*$data = array(
    NQL::_AND => array(
        'id' => (object) array('value' => 33, 'type' => Nogal::PARAM_INT),
        'actived' => (object) array('value' => true, 'type' => Nogal::PARAM_BOOL),
        'deleted_at' => (object) array('value' => '05-01-2020 12:12:12', 'type' => Nogal::PARAM_STR)
    )
);

$data = array(
    'id' => (object) array('value' => 33, 'type' => Nogal::PARAM_INT)
);

$base->delete('usuario', $data);
exit();*/

/*
$table = 'usuario';
$select_columns = 'id, nombre, password';
$base->select($table, $select_columns);
*/

/*
$table = 'usuario';
$select_columns = 'id, nombre, password';
$joins = array(
    NQL::JOIN => (object) array(
        'table' => 'rol',
        //'condition' => array('on' => 'rojo'),
        'condition' => array('on' => 'table_parent.field1 = table_child_field2'),
        //'condition' => array('using' => 'table_child.field1'),
        //'condition' => array('using' => 'table_child.field1, table_child.field2'),
    )
);
$base->select($table, $select_columns, $joins);
*/

/*
$table = 'usuario';
$select_columns = 'id, nombre, password';
$joins = array(
    NQL::JOIN => (object) array(
        'table' => 'rol',
        //'condition' => array('on' => 'rojo'),
        'condition' => array('on' => 'table_parent.field1 = table_child_field2'),
        //'condition' => array('using' => 'table_child.field1'),
        //'condition' => array('using' => 'table_child.field1, table_child.field2'),
    )
);
$where = array(
    (object) array(
        'condition' => 'edad = 55',
        'raw' => true
    ),
    NQL::_AND => (object) array(
        'condition' => 'edad',
        'value' => 33,
        'type' => Nogal::PARAM_INT,
        'raw' => false
    ),
    NQL::_OR => (object) array(
        'condition' => 'edad = 25',
        'raw' => true
    )
);
$base->select($table, $select_columns, $joins, $where);
*/

/*
$table = 'usuario';
$select_columns = 'id, nombre, password';
$joins = array(
    NQL::JOIN => (object) array(
        'table' => 'rol',
        //'condition' => array('on' => 'rojo'),
        'condition' => array('on' => 'table_parent.field1 = table_child_field2'),
        //'condition' => array('using' => 'table_child.field1'),
        //'condition' => array('using' => 'table_child.field1, table_child.field2'),
    )
);
$where = array(
    (object) array(
        'condition' => 'edad = 55',
        'raw' => true
    ),
    NQL::_AND => array(
        (object) array(
            'condition' => 'edad',
            'value' => 33,
            'type' => Nogal::PARAM_INT,
            'raw' => false
        ),
        NQL::_OR => (object) array(
            'condition' => 'edad = 25',
            'raw' => true
        ),
        NQL::_OR => array(
            (object) array(
                'condition' => 'edad',
                'value' => 33,
                'type' => Nogal::PARAM_INT,
                'raw' => false
            ),
            NQL::_OR => (object) array(
                'condition' => 'edad = 25',
                'raw' => true
            )
        ),
    ),
    NQL::_OR => array(
        (object) array(
            'condition' => 'edad = 25',
            'raw' => true
        ),
        NQL::_AND => (object) array(
            'condition' => 'edad',
            'value' => 33,
            'type' => Nogal::PARAM_INT,
            'raw' => false,
            'logical_operator' => '>='
        )
    )
);
$group_by = 'campo1, campo2';

$having = array(
    (object) array(
        'condition' => 'edad = 55',
        'raw' => true
    ),
    NQL::_AND => array(
        (object) array(
            'condition' => 'edad',
            'value' => 33,
            'type' => Nogal::PARAM_INT,
            'raw' => false
        ),
        NQL::_OR => (object) array(
            'condition' => 'edad = 25',
            'raw' => true
        ),
        NQL::_OR => array(
            (object) array(
                'condition' => 'edad',
                'value' => 33,
                'type' => Nogal::PARAM_INT,
                'raw' => false
            ),
            NQL::_OR => (object) array(
                'condition' => 'edad = 25',
                'raw' => true
            )
        ),
    ),
    NQL::_OR => array(
        (object) array(
            'condition' => 'edad = 25',
            'raw' => true
        ),
        NQL::_AND => (object) array(
            'condition' => 'edad',
            'value' => 33,
            'type' => Nogal::PARAM_INT,
            'raw' => false,
            'logical_operator' => '>='
        )
    )
);

$order_by = 'table.field1 ASC';

$page = (object) array(
    'limit' => 2,
    'offset' => 5
);

echo '<div style="width: 800px; background-color: #c3c3c3; padding: 5px; word-wrap: break-word;">';
echo '<pre>';
$base->select($table, $select_columns, $joins, $where, $group_by, $having, $order_by, $page);
echo '</pre>';
echo '</div>';
*/

/*$table = 'usuario';
$columns_and_values = array(
    'id' => (object) array(
        'value' => 1,
        'type' => Nogal::PARAM_INT
    ),
    'usuario' => (object) array(
        'value' => 'jalf',
        'type' => Nogal::PARAM_STR
    ),
    'password' => '123123123123123123123'
);

echo '<div style="width: 800px; background-color: #c3c3c3; padding: 5px; word-wrap: break-word;">';
echo '<pre>';
$base->save($table, $columns_and_values);
echo '</pre>';
echo '</div>';*/



$table = 'usuario';
$id = array(
    'nombre' => 'julian',
    'apellido' => (object) array(
        'value' => 'lasso',
        'type' => Nogal::PARAM_STR
    )
);

$where = array(
    (object) array(
        'condition' => 'edad = 55',
        'raw' => true
    ),
    NQL::_AND => array(
        (object) array(
            'condition' => 'edad',
            'value' => 33,
            'type' => Nogal::PARAM_INT,
            'raw' => false
        ),
        NQL::_OR => (object) array(
            'condition' => 'edad = 25',
            'raw' => true
        ),
        NQL::_OR => array(
            (object) array(
                'condition' => 'edad',
                'value' => 33,
                'type' => Nogal::PARAM_INT,
                'raw' => false
            ),
            NQL::_OR => (object) array(
                'condition' => 'edad = 25',
                'raw' => true
            )
        ),
    ),
    NQL::_OR => array(
        (object) array(
            'condition' => 'edad = 25',
            'raw' => true
        ),
        NQL::_AND => (object) array(
            'condition' => 'edad',
            'value' => 33,
            'type' => Nogal::PARAM_INT,
            'raw' => false,
            'logical_operator' => '>='
        )
    )
);

echo '<div style="width: 800px; background-color: #c3c3c3; padding: 5px; word-wrap: break-word;">';
echo '<pre>';
$base->update($table, $id, $where);
echo '</pre>';
echo '</div>';











