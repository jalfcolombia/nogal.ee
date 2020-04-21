<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . 'DataBase.php';

use TaskConsole\Interfaces\ITask;

class build_schema implements ITask
{

    private $params;
    private $float = array(
        'decimal',
        'float',
        'money',
        'numeric',
        'real'
    );
    private $int = array(
        'bigint',
        'bit',
        'int',
        'integer',
        'smallint',
        'smallmoney',
        'tinyint'
    );
    private $str = array(
        'binary',
        'char',
        'date',
        'datetime2',
        'datetime',
        'datetimeoffset',
        'image',
        'nchar',
        'ntext',
        'nvarchar',
        'smalldatetime',
        'text',
        'time',
        'varbinary',
        'varchar',
        'xml'
    );
    private $boo = array(
        'bool'
    );
    private $datetime = array(
        'date',
        'datetime2',
        'datetime',
        'datetimeoffset',
        'smalldatetime',
        'time'
    );

    /**
     * @var DataBase
     */
    private $conn;

    public function __construct(array $params)
    {
        $this->params = $params;
        $config = array(
            'driver' => $params['driver'],
            'host' => $params['host'],
            'port' => $params['port'],
            'dbname' => $params['dbname'],
            'user' => $params['dbuser'],
            'password' => $params['dbpass']
        );
        $this->conn = new DataBase($config);
    }

    public function task(): void
    {
        $schema = $this->conn->getAllAttributes($this->params['dbname']);
        // print_r($schema['aprendiz']); exit();
        foreach ($schema as $table_name => $objTable) {
            $outputBase = $this->params['output'] . 'Base' . DIRECTORY_SEPARATOR;
            $this->generate_table($this->camelCase($table_name));
            $this->create_dir_base($outputBase);
            $this->generate_base($objTable->schema, $table_name, $objTable->columns, $objTable->pks, $objTable->fks, $outputBase);
        }
    }

    private function create_dir_base(string $path): void
    {
        if (is_dir($path) === false) {
            mkdir($path);
        }
    }

    private function file_save(string $path, string $name, string $content, bool $rewrite = false): void
    {
        $newfile = $path . $name . '.php';
        if ($rewrite === true) {
            if ($file = fopen($newfile, "w")) {
                if (fwrite($file, $content) === false) {
                    throw new Exception("Ocurrio un error en el archivo '{$name}' al intentar escribirlo");
                }
                fclose($file);
            } else {
                throw new Exception("Ocurrio un error y el archivo '{$name}' en la ubicación '{$path}' no pudo ser creado");
            }
        } else {
            if (file_exists($newfile) === $rewrite) {
                if ($file = fopen($newfile, "w")) {
                    if (fwrite($file, $content) === false) {
                        throw new Exception("Ocurrio un error en el archivo '{$name}' al intentar escribirlo");
                    }
                    fclose($file);
                } else {
                    throw new Exception("Ocurrio un error y el archivo '{$name}' en la ubicación '{$path}' no pudo ser creado");
                }
            }
        }
    }

    private function camelCase(string $string): string
    {
        if (isset($GLOBALS['cacheTempCamelCase'][$string]) === false) {
            $GLOBALS['cacheTempCamelCase'][$string] = str_replace(
                    ' ',
                    '',
                    ucwords(str_replace(array('_', '.'), ' ', $string))
            );
        }
        return $GLOBALS['cacheTempCamelCase'][$string];
    }

    private function generate_table(string $table): void
    {
        $skeleton = (string) '';
        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Skeleton' . DIRECTORY_SEPARATOR . 'Table.php';
        $this->file_save($this->params['output'], $table, $skeleton);
    }

    private function generate_base(?string $schema, string $table, stdClass $columns, stdClass $pks, stdClass $fks, string $outputBase): void
    {
        $tab = (string) "    ";
        $namespace_details = $fields = $length = $type = $columns2 = $detail = $defaults = $getters_and_setters = $save = $no_save = $save_defaults = $save_details = (string) "";
        $no_update = $update_defaults = $no_delete = $delete_defaults = $update_details = $where_update = $delete = $set_delete = $where_delete = $save_execute = (string) "";
        $reset = (string) "";
        $tableCamelCase = $this->camelCase($table);
        $table_details = array();

        // Tablas detalle (fk)
        foreach ($fks as $columnParent => $fk) {
            if (isset($fk->childs) === true and count((array) $fk->childs) > 0) {
                foreach ($fk->childs as $child) {
                    $namespace_details .= "use Model\\{$this->camelCase($child->table)};" . PHP_EOL;
                    $columns2 .= PHP_EOL . <<<PRC
    /**
     *
     * @var {$this->camelCase($child->table)}
     */
    protected \${$child->table};
PRC . PHP_EOL;
                    $detail .= PHP_EOL . "{$tab}{$tab}\$this->{$child->table} = array();";
                    $no_save .= "'{$child->table}', ";
                    $no_update .= "'{$child->table}', ";
                    $no_delete .= "'{$child->table}',";
                    $table_details[] = $child->table;
                    $save_details .= PHP_EOL . <<<SAVE_DETAILS
            if (count(\$this->{$child->table}) > 0) {
                /* @var \$detalle {$this->camelCase($child->table)} */
                \$x = 0;
                foreach (\$this->{$child->table} as \${$child->table}) {
                    \$this->{$child->table}[\$x] = \${$child->table}->set{$this->camelCase($child->column)}(\$this->get{$this->camelCase($fk->column)}())->save(\$debug);
                    \$x++;
                }
            }
SAVE_DETAILS;
                    $update_details .= PHP_EOL . <<<UPDATE_DETAILS
            if (count(\$this->{$child->table}) > 0) {
                /* @var \$detalle {$this->camelCase($child->table)} */
                foreach (\$this->{$child->table} as \${$child->table}) {
                    \${$child->table}->set{$this->camelCase($child->column)}(\$this->get{$this->camelCase($fk->column)}())->update(\$debug);
                }
            }
UPDATE_DETAILS;
                }
            }
        }

        $sequence = PHP_EOL . <<<SEQ
    /**
     * Nombre de la secuencia usada en la llave primaria
     */
    public const SEQUENCE = null;
SEQ . PHP_EOL;
        $schema = PHP_EOL . <<<TBL
    /**
     * Nombre del esquema al que pertenece la tabla "{$table}"
     */
    public const SCHEMA = '{$schema}';
TBL . PHP_EOL;

        // FIELDS anD LENGTHS
        foreach ($columns as $column) {
            $columnMayus = strtoupper($column->column);
            $fields .= PHP_EOL . <<<FLD
    /**
     * Nombre del campo "{$column->column}"
     */
    public const FIELD_{$columnMayus} = self::TABLE . '.' . '{$column->column}';
FLD . PHP_EOL;
            $columnCamelCase = $this->camelCase($column->column);
            if (isset($column->length) === true) {
                $length .= PHP_EOL . <<<LNG
    /**
     * Longitud del campo "{$column->column}"
     */
    public const LENGTH_{$columnMayus} = {$column->length};
LNG . PHP_EOL;
            }

            // TYPE
            $type_param = $type_param_return = "";
            if (in_array($column->type, $this->float)) {
                $type_param = "INT";
                $type_param_return = "float";
            } elseif (in_array($column->type, $this->int)) {
                $type_param = "INT";
                $type_param_return = "int";
            } elseif (in_array($column->type, $this->str)) {
                $type_param = "STR";
                $type_param_return = "string";
                if (in_array($column->type, $this->datetime)) {
                    $type_param_return = "DateTime";
                }
            } elseif (in_array($column->type, $this->boo)) {
                $type_param = "BOOL";
                $type_param_return = "bool";
            }
            $type .= PHP_EOL . <<<TYPE
    /**
     * Tipo de dato del campo "{$column->column}" para ser tratado por PDO
     */
    public const TYPE_{$columnMayus} = self::PARAM_{$type_param};
TYPE . PHP_EOL;

            // protected columns
            $columns2 .= PHP_EOL . <<<PRC
    /**
     *
     * @var {$type_param_return}
     */
    protected \${$column->column};
PRC . PHP_EOL;


            // dontwork for all behaviors
            if (isset($column->behaviors->dontwork) === true) {
                $no_save .= "'{$column->column}', ";
                $no_update .= "'{$column->column}', ";
            }

            if ($column->auto_increment === true) {
                $no_save .= "'{$column->column}', ";
                $no_update .= "'{$column->column}', ";
            }

            if (isset($column->behaviors->update) === true and isset($column->behaviors->default) === false) {
                $no_save .= "'{$column->column}', ";
            }

            if (isset($column->behaviors->insert) === true and isset($column->behaviors->default) === false) {
                $no_update .= "'{$column->column}', ";
            }

            if (isset($column->behaviors->delete) === true and isset($column->behaviors->default) === false) {
                $no_delete .= "'{$column->column}',";
            }

            if (isset($column->behaviors->delete) === true) {
                $set_delete .= PHP_EOL . <<<SET_DELETE
                    self::FIELD_{$columnMayus} => (object) array(
                        'value' => \$this->get{$columnCamelCase}(),
                        'type' => self::TYPE_{$columnMayus}
                    ),
SET_DELETE;
                $where_update .= PHP_EOL . <<<WHERE_UPDATE
                NQL::_AND => (object) array(
                    'condition' => self::FIELD_{$columnMayus} . ' IS NULL',
                    'raw' => true
                ),
WHERE_UPDATE;
                $where_delete .= PHP_EOL . <<<WHERE_DELETE
                    NQL::_AND => (object) array(
                        'condition' => self::FIELD_{$columnMayus} . ' IS NULL',
                        'raw' => true
                    ),
WHERE_DELETE;
            }

            // RESET
            $reset .= PHP_EOL . "{$tab}{$tab}\$this->{$column->column} = null;";

            // DEFAULTS
            if (isset($column->default) === false and isset($column->behaviors->default) === true) {
                $flag = $this->found_default((array) $column->behaviors);
                if ($flag === true) {
                    $defaults .= PHP_EOL . "{$tab}{$tab}\$this->{$column->column} = {$column->behaviors->default};";
                }

                // save with behaviors
                if (isset($column->behaviors->insert) === true) {
                    $no_update .= "'{$column->column}', ";
                    if ($type_param_return === "DateTime") {
                        $save_defaults .= PHP_EOL . <<<SAVE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());
{$tab}{$tab}{$tab}}
SAVE_DEFAULTS;
                    } else {
                        $save_defaults .= PHP_EOL . <<<SAVE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});
{$tab}{$tab}{$tab}}
SAVE_DEFAULTS;
                    }
                }

                // update with behaviors
                if (isset($column->behaviors->update) === true) {
                    $no_save .= "'{$column->column}', ";
                    if ($type_param_return === "DateTime") {
                        $update_defaults .= PHP_EOL . <<<UPDATE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());
{$tab}{$tab}{$tab}}
UPDATE_DEFAULTS;
                    } else {
                        $update_defaults .= PHP_EOL . <<<UPDATE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});
{$tab}{$tab}{$tab}}
UPDATE_DEFAULTS;
                    }
                }

                // delete with behaviors
                if (isset($column->behaviors->delete) === true) {
                    $no_save .= "'{$column->column}', ";
                    $no_update .= "'{$column->column}', ";
                    if ($type_param_return === "DateTime") {
                        $delete_defaults .= PHP_EOL . <<<DELETE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());
{$tab}{$tab}{$tab}}
DELETE_DEFAULTS;
                    } else {
                        $delete_defaults .= PHP_EOL . <<<DELETE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});
{$tab}{$tab}{$tab}}
DELETE_DEFAULTS;
                    }
                }
            }

            $getter = "return \$this->{$column->column};";
            $getterparam = "";
            $type_param_return2 = ($type_param_return === "DateTime") ? "string" : $type_param_return;
            $setterparam = "{$type_param_return2} \${$column->column}";
            $setter = "\$this->{$column->column} = \${$column->column};";
            if (in_array($column->type, $this->datetime)) {
                $getterparam = "string \$format = null";
                $getter = "return (\$format === null) ? \$this->{$column->column} : \$this->{$column->column}->setFormat(\$format);";
                $setterparam = "string \${$column->column}, string \$format = null";
                $setter = "\$this->{$column->column} = new DateTime(\${$column->column}, \$format);";
            }
            // GETTER
            $getters_and_setters .= PHP_EOL . PHP_EOL . <<<GETTER
    /**
     * Obtiene el valor contenido en el campo "{$column->column}"
     *
     * @return {$type_param_return}|null
     */
    public function get{$columnCamelCase}({$getterparam}): ?{$type_param_return}
    {
        {$getter}
    }
GETTER;
            // SETTER
            if (isset($column->behaviors->encrypt) === true) {
                $setter = "\$this->{$column->column} = hash('{$column->behaviors->encrypt}', \${$column->column});";
            }
            $param2 = ($type_param_return === "DateTime") ? PHP_EOL . "{$tab} * @param string \$format [opcional]" : null;
            $param2type = ($type_param_return === "DateTime") ? "string" : $type_param_return;
            $getters_and_setters .= PHP_EOL . PHP_EOL . <<<SETTER
    /**
     * Setea un valor en la columna "{$column->column}"
     *
     * @param {$param2type} \${$column->column}{$param2}
     *
     * @return \self
     */
    public function set{$columnCamelCase}({$setterparam}): self
    {
        {$setter}
        return \$this;
    }
SETTER;
            // HAS
            if (count((array) $pks) > 0) {
                foreach ($pks as $pk) {
                    if ($column->column === $pk->column) {

                        if ($column->auto_increment === true) {
                            $save_execute .= PHP_EOL . "{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(\$this->saveBase(self::TABLE, \$data, self::SEQUENCE, \$debug));";
                        } else {
                            $save_execute .= PHP_EOL . "{$tab}{$tab}{$tab}\$this->saveBase(self::TABLE, \$data, self::SEQUENCE, \$debug);";
                            $no_update .= "'{$column->column}', ";
                        }

                        $where_update .= PHP_EOL . <<<WHERE_UPDATE
                (object) array(
                    'condition' => self::FIELD_{$columnMayus},
                    'value' => \$this->get{$columnCamelCase}(),
                    'type' => self::TYPE_{$columnMayus},
                    'raw' => false
                ),
WHERE_UPDATE;
                        $where_delete .= PHP_EOL . <<<WHERE_DELETE
                    (object) array(
                        'condition' => self::FIELD_{$columnMayus},
                        'value' => \$this->get{$columnCamelCase}(),
                        'type' => self::TYPE_{$columnMayus},
                        'raw' => false
                    ),
WHERE_DELETE;
                        $getters_and_setters .= PHP_EOL . PHP_EOL . <<<HAS
    /**
     * Comprueba la existencia de un valor en la columna "{$pk->column}"
     *
     * @return bool TRUE si existe, de lo contrario FALSE
     */
    public function has{$columnCamelCase}(): bool
    {
        if (is_null(\$this->{$pk->column}) === true) {
            return false;
        }
        return true;
    }
HAS;
                    }
                }
            }
        }

        if (count($table_details) > 0) {
            foreach ($table_details as $table_detail) {
                $tableDetailCamelCase = $this->camelCase($table_detail);
                $getters_and_setters .= PHP_EOL . PHP_EOL . <<<GETTER_AND_SETTER
    /**
     * Obtiene un arreglo de objetos en relación a la tabla "{$table_detail}"
     *
     * @return array
     */
    public function get{$tableDetailCamelCase}(): array
    {
        return \$this->{$table_detail};
    }

    /**
     * Setea un objeto en relación a la tabla "{$table_detail}"
     *
     * @param {$tableDetailCamelCase} {$table_detail}
     *
     * @return \self
     */
    public function set{$tableDetailCamelCase}({$tableDetailCamelCase} \${$table_detail}): self
    {
        \$this->{$table_detail}[] = \${$table_detail};
        return \$this;
    }
GETTER_AND_SETTER;
            }
        }

        $no_save = trim($no_save, ", ");
        $save = PHP_EOL . PHP_EOL . <<<SAVE
    /**
     * Guarda un registro en la tabla "{$table}"
     *
     * @param bool \$debug
     * @return \self
     *
     * @throws \Exception
     */
    public function save(bool \$debug = false): self
    {
        try {
            \$this->beginTransaction();{$save_defaults}
            \$data = \$this->createDataForSaveOrUpdate(array({$no_save}));{$save_execute}{$save_details}
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
SAVE;

        $no_update = trim($no_update, ", ");
        $where_update = trim($where_update, ",");
        $where_delete = trim($where_delete, ",");
        $update = PHP_EOL . <<<UPDATE
    /**
     * Actualiza un registro en la tabla "{$table}"
     *
     * @param array \$updateColumnsToNULL
     * @param bool \$debug
     * @return \self
     *
     * @throws \Exception
     */
    public function update(array \$updateColumnsToNULL = array(), bool \$debug = false): self
    {
        try {
            \$this->beginTransaction();{$update_defaults}
            \$set = \$this->createDataForSaveOrUpdate(array({$no_update}), \$updateColumnsToNULL);
            \$where = array({$where_update}
            );
            parent::updateBase(self::TABLE, \$set, \$where, \$debug);{$update_details}
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
UPDATE;

        $set_delete = trim($set_delete, ",");
        $delete = <<<DELETE
    /**
     * Borra un registro en la tabla "{$table}"
     *
     * @param bool \$logical TRUE para borrado lógico y FALSE para borrado físico
     * @param bool \$deep [en BETA aún no funciona correctamente]
     * @param bool \$debug
     *
     * @return \self
     *
     * @throws \Exception
     */
    public function delete(bool \$logical = true, bool \$deep = true, bool \$debug = false): self
    {
        try {
            \$this->beginTransaction();
            if (\$logical === true) {{$delete_defaults}
                \$set = array({$set_delete}
                );
                \$where = array({$where_delete}
                );
                parent::updateBase(self::TABLE, \$set, \$where, \$debug);
            } else {
                \$where = array({$where_delete}
                );
                parent::deleteBase(self::TABLE, \$where, \$debug);
            }
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
DELETE;
        $skeleton = (string) '';
        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Skeleton' . DIRECTORY_SEPARATOR . 'TableBase.php';
        $this->file_save($outputBase, $tableCamelCase . "Base", $skeleton, true);
    }

    private function found_default(array $behaviors): bool
    {
        $answer = true;
        foreach ($behaviors as $key => $value) {
            if ($key === 'insert') {
                return false;
            } elseif ($key === 'update') {
                return false;
            } elseif ($key === 'delete') {
                return false;
            } elseif ($key === 'dontwork') {
                return false;
            } elseif ($key === 'noupdate') {
                return false;
            }
        }
        return $answer;
    }

}
