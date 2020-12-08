<?php

$skeleton = <<<skeleton
<?php

namespace Model\Base;

use NogalEE\Table\Base;
use NogalEE\DataType\DateTime;
use NogalEE\NQL;
{$namespaceDetails}
abstract class {$tableCamelCase}Base extends Base
{
{$sequence}{$schema}
    /**
     * Nombre de la tabla "{$table}" en base de datos
     */
    public const TABLE = self::SCHEMA . '.' . '{$table}';
{$fields}{$length}{$type}{$columnsTwo}
    public function __construct(array \$config)
    {
        parent::__construct(\$config);{$detail}{$defaults}
    }{$gettersAndSetters}{$save}
{$update}

{$delete}

    /**
     * Crea un arreglo con el nombre de las columnas que se van a tener en cuena para una inserción o actualización
     *
     * @param array \$exonerate [opcional] Arreglo con los nombre de las columnas a exonerar en una inserción o actualización
     *
     * @param array \$updateColumnsToNULL
     * @return array Arreglo con los nombres de las columnas a tener encuenta en una inserción o actualización
     */
    private function createDataForSaveOrUpdate(array \$exonerate = array(), array \$updateColumnsToNULL = array()): array
    {
        \$columns = get_object_vars(\$this);
        unset(\$columns['_nql']);
        foreach (\$exonerate as \$column) {
            unset(\$columns[\$column]);
        }
        \$data = array();
        foreach (\$columns as \$column => \$value) {
            \$FIELD = 'FIELD_' . strtoupper(\$column);
            foreach (\$updateColumnsToNULL as \$col) {
                if (\$column === \$col) {
                    \$data[constant("self::\$FIELD")] = (object) array(
                        'value' => null,
                        'type' => \\PDO::PARAM_NULL
                    );
                    \$this->\$column = null;
                }
            }
            if (is_null(\$this->\$column) === false) {
                \$TYPE = 'TYPE_' . strtoupper(\$column);
                \$get = 'get' . ucfirst(\$this->camelCase(\$column));
                \$data[constant("self::\$FIELD")] = (object) array(
                    'value' => \$this->\$get(),
                    'type' => constant("self::\$TYPE")
                );
            }
        }
        return \$data;
    }

    public function reset(): self
    {{$reset}{$detail}{$defaults}
        return \$this;
    }
}

skeleton;
