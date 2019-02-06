<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . 'DataBase.php';

use TaskConsole\Interfaces\ITask;

class build_schema implements ITask {

    private $params;
    private $int = array(
        'bigint',
        'bit',
        'decimal',
        'float',
        'int',
        'integer',
        'money',
        'numeric',
        'smallint',
        'smallmoney',
        'real',
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

    public function __construct(array $params) {
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

    public function task(): void {
        $schema = $this->conn->getAllAttributes($this->params['dbname']);
        // print_r($schema); exit();
        foreach ($schema as $table_name => $objTable) {
            $outputBase = $this->params['output'] . 'Base' . DIRECTORY_SEPARATOR;
            $this->generate_table($this->camelCase($table_name));
            $this->create_dir_base($outputBase);
            $this->generate_base($objTable->schema, $table_name, $objTable->columns, $objTable->pks, $objTable->fks, $outputBase);
        }
    }

    private function create_dir_base(string $path): void {
        if (is_dir($path) === false) {
            mkdir($path);
        }
    }

    private function file_save(string $path, string $name, string $content, bool $rewrite = false): void {
        $newfile = $path . $name . '.php';
        // if (file_exists($newfile) === $rewrite) {
        if ($file = fopen($newfile, "w")) {
            if (fwrite($file, $content) === false) {
                throw new Exception("Ocurrio un error en el archivo '{$name}' al intentar escribirlo");
            }
            fclose($file);
        } else {
            throw new Exception("Ocurrio un error y el archivo '{$name}' en la ubicación '{$path}' no pudo ser creado");
        }
        // }
    }

    private function camelCase(string $string): string {
        if (isset($GLOBALS['cacheTempCamelCase'][$string]) === false) {
            $GLOBALS['cacheTempCamelCase'][$string] = str_replace(
                    ' ',
                    '',
                    ucwords(str_replace(array('_', '.'), ' ', $string))
            );
        }
        return $GLOBALS['cacheTempCamelCase'][$string];
    }

    private function generate_table(string $table): void {
        $skeleton = (string) '';
        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Skeleton' . DIRECTORY_SEPARATOR . 'Table.php';
        $this->file_save($this->params['output'], $table, $skeleton);
    }

    private function generate_base(?string $schema, string $table, stdClass $columns, stdClass $pks, stdClass $fks, string $outputBase): void {
        $tab = (string) "    ";
        $namespace_details = $fields = $length = $type = $columns2 = $detail = $defaults = $getters_and_setters = $save = $no_save = $save_defaults = $save_details = (string) "";
        $no_update = $update_defaults = (string) "";
        $tableCamelCase = $this->camelCase($table);
        $table_details = array();

        // Tablas detalle (fk)
        foreach ($fks as $columnParent => $fk) {
            if (isset($fk->childs) === true and count((array) $fk->childs) > 0) {
                foreach ($fk->childs as $child) {
                    $namespace_details .= "use Model\\{$this->camelCase($child->table)};" . PHP_EOL;
                    $columns2 .= "{$tab}protected \${$child->table};" . PHP_EOL;
                    $detail .= "{$tab}\$this->{$child->table} = array();" . PHP_EOL;
                    $no_save .= "'{$child->table}',";
                    $table_details[] = $child->table;
                    $save_details .= PHP_EOL . <<<SAVE_DETAILS
            if (count(\$this->{{$child->table}}) > 0) {
                /* @var \$detalle {$this->camelCase($child->table)} */
                \$x = 0;
                foreach (\$this->{$child->table} as \${$child->table}) {
                    \$this->{$child->table}[\$x] = \${$child->table}->set{$this->camelCase($child->column)}(\$this->get{$this->camelCase($fk->column)}())->save();
                    \$x++;
                }
            }
SAVE_DETAILS;
                }
            }
        }

        $sequence = "{$tab}public const SEQUENCE = null;" . PHP_EOL;
        $schema = "{$tab}public const SCHEMA = '{$schema}';" . PHP_EOL;

        // FIELDS anD LENGTHS
        foreach ($columns as $column) {
            $columnMayus = strtoupper($column->column);
            $fields .= "{$tab}public const FIELD_{$columnMayus} = self::TABLE . '.' . '{$column->column}';" . PHP_EOL;
            $columnCamelCase = $this->camelCase($column->column);
            if (isset($column->length) === true) {
                $length .= "{$tab}public const LENGTH_{$columnMayus} = {$column->length};" . PHP_EOL;
            }

            // TYPE
            $type_param = $type_param_return = "";
            if (in_array($column->type, $this->int)) {
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
            $type .= "{$tab}public const TYPE_{$columnMayus} = self::PARAM_{$type_param};" . PHP_EOL;

            // protected columns
            $columns2 .= "{$tab}protected \${$column->column};" . PHP_EOL;


            // dontwork for all behaviors
            if (isset($column->behaviors->dontwork) === true) {
                $no_save .= "'{$column->column}',";
                $no_update .= "'{$column->column}',";
            }

            if ($column->auto_increment === true) {
                $no_save .= "'{$column->column}',";
                $no_update .= "'{$column->column}',";
            }

            // DEFAULTS
            if (isset($column->default) === false and isset($column->behaviors->default) === true) {
                $flag = $this->found_default((array) $column->behaviors);
                if ($flag === true) {
                    $defaults .= "{$tab}{$tab}\$this->{$column->column} = {$column->behaviors->default};" . PHP_EOL;
                }

                // save with behaviors
                if (isset($column->behaviors->insert) === true) {
                    $no_save .= "'{$column->column}',";
                    if ($type_param_return === "DateTime") {
                        $save_defaults .= PHP_EOL . "\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());";
                    } else {
                        $save_defaults .= PHP_EOL . "{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});";
                    }
                }

                // update with behaviors
                if (isset($column->behaviors->update) === true) {
                    $no_update .= "'{$column->column}',";
                    if ($type_param_return === "DateTime") {
                        $update_defaults .= PHP_EOL . "\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());";
                    } else {
                        $update_defaults .= PHP_EOL . "{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});";
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
                $setterparam = "?string \${$column->column}, string \$format = null";
                $setter = "\$this->{$column->column} = new DateTime(\${$column->column}, \$format);";
            }
            // GETTER
            $getters_and_setters .= PHP_EOL . <<<GETTER
    public function get{$columnCamelCase}({$getterparam}): ?{$type_param_return}
    {
        {$getter}
    }
GETTER;
            // SETTER
            if (isset($column->behaviors->encrypt) === true) {
                $setter = "\$this->{$column->column} = hash('{$column->behaviors->encrypt}', \${$column->column});";
            }
            $getters_and_setters .= PHP_EOL . <<<SETTER
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
                        $getters_and_setters .= PHP_EOL . <<<HAS
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
                $getters_and_setters .= PHP_EOL . <<<GETTER_AND_SETTER
    public function get{$tableDetailCamelCase}(): array
    {
        return \$this->{$table_detail};
    }

    public function set{$tableDetailCamelCase}({$tableDetailCamelCase} \${$table_detail}): self
    {
        \$this->{$table_detail}[] = \${$table_detail};
        return \$this;
    }
GETTER_AND_SETTER;
            }
        }

        $no_save = trim($no_save, ",");
        $save = PHP_EOL . <<<SAVE
    public function save(): self
    {
        try {
            \$this->beginTransaction();{$save_defaults}
            \$data = \$this->createDataForSaveOrUpdate(array({$no_save}));
            \$this->setId(\$this->saveBase(self::TABLE, \$data, self::SEQUENCE));{$save_details}
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
SAVE;

        $no_update = trim($no_update, ",");
        $update = PHP_EOL . <<<UPDATE
    public function update(): self
    {
        try {
            \$this->beginTransaction();{$update_defaults}
            \$set = \$this->createDataForSaveOrUpdate(array({$no_update}));
            \$where = array(
                (object) array(
                    'condition' => self::FIELD_ID,
                    'value' => \$this->getId(),
                    'type' => self::ID_TYPE,
                    'raw' => false
                ),
                NQL::_AND => (object) array(
                    'condition' => self::FIELD_DELETED_AT . ' IS NULL',
                    'raw' => true
                )
            );
            parent::updateBase(self::TABLE, \$set, \$where);
            if (is_array(\$this->detalle) and count(\$this->detalle)) {
                /* @var \$detalle Detalle */
                foreach (\$this->detalle as \$detalle) {
                    \$detalle->setMaestroId(\$this->getId())->update();
                }
            }
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
UPDATE;
        $skeleton = (string) '';
        require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Skeleton' . DIRECTORY_SEPARATOR . 'TableBase.php';
        $this->file_save($outputBase, $tableCamelCase, $skeleton, true);
    }

    private function found_default(array $behaviors): bool {
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
