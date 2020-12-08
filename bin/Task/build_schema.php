<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . 'DataBase.php';

use TaskConsole\Interfaces\ITask;

class BuildSchema implements ITask
{

    private $tab;
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
        $this->tab = (string)"    ";
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
        foreach ($schema as $tableName => $objTable) {
            $outputBase = $this->params['output'] . 'Base' . DIRECTORY_SEPARATOR;
            $this->generateTable($this->camelCase($tableName));
            $this->createDirBase($outputBase);
            $this->generateBase(
                $objTable->schema,
                $tableName,
                $objTable->columns,
                $objTable->pks,
                $objTable->fks,
                $outputBase
            );
        }
    }

    private function createDirBase(string $path): void
    {
        if (is_dir($path) === false) {
            mkdir($path);
        }
    }

    private function fileSave(string $path, string $name, string $content, bool $rewrite = false): void
    {
        $newfile = $path . $name . '.php';
        if ($rewrite === true) {
            if ($file = fopen($newfile, "w")) {
                if (fwrite($file, $content) === false) {
                    throw new Exception("Ocurrio un error en el archivo '{$name}' al intentar escribirlo");
                }
                fclose($file);
            } else {
                throw new Exception(
                    "Ocurrio un error y el archivo '{$name}' en la ubicación
                    '{$path}' no pudo ser creado"
                );
            }
        } else {
            if (file_exists($newfile) === $rewrite) {
                if ($file = fopen($newfile, "w")) {
                    if (fwrite($file, $content) === false) {
                        throw new Exception(
                            "Ocurrio un error en el archivo '{$name}'
                            al intentar escribirlo"
                        );
                    }
                    fclose($file);
                } else {
                    throw new Exception(
                        "Ocurrio un error y el archivo '{$name}'
                        en la ubicación '{$path}' no pudo ser creado"
                    );
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

    private function generateTable(string $table): void
    {
        $skeleton = (string)'';
        $require = __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'Skeleton' . DIRECTORY_SEPARATOR .
            'Table.php';
        require $require;
        $this->fileSave($this->params['output'], $table, $skeleton);
    }

    private function generateBase(
        ?string $schema,
        string $table,
        stdClass $columns,
        stdClass $pks,
        stdClass $fks,
        string $outputBase
    ): void {
        $tab = (string)"    ";
        $type =
        $save =
        $reset =
        $fields =
        $length =
        $delete =
        $detail =
        $noSave =
        $defaults =
        $noUpdate =
        $noDelete =
        $setDelete =
        $columnsTwo =
        $saveDetails =
        $whereUpdate =
        $saveExecute =
        $whereDelete =
        $saveDefaults =
        $updateDetails =
        $updateDefaults =
        $deleteDefaults =
        $namespaceDetails =
        $gettersAndSetters = (string)'';
        $tableCamelCase = $this->camelCase($table);
        $tableDetails = array();

        // Tablas detalle (fk)
        foreach ($fks as $columnParent => $fk) {
            if (isset($fk->childs) === true and count((array)$fk->childs) > 0) {
            // print_r($fks); exit();
                foreach ($fk->childs as $index => $child) {
                    if (isset($fk->childs[$index - 1]) === false or
                        (
                            isset($fk->childs[$index - 1]) === true and
                            $fk->childs[$index - 1]->table !== $child->table
                        )
                    ) {
                        $namespaceDetails .= $this->getUseNamespace($child->table);
                        $tableDetails[] = array('name' => $child->table, 'type' => $child->table);
                        $index = null;
                    } elseif (isset($fk->childs[$index - 1]) === true
                        and $fk->childs[$index - 1]->table === $child->table
                    ) {
                        $columnsTwo .= $this->getVariableDeclaration($child->table, $index);
                        $tableDetails[] = array('name' => $child->table . $index, 'type' => $child->table);
                    }

                    $detail .= $this->getDetail($child->table, $index);
                    $noSave .= $this->getNoSaveSkeleton($child->table, $index);
                    $noUpdate .= $this->getNoUpdateSkeleton($child->table, $index);
                    $noDelete .= $this->getNoDeleteSkeleton($child->table, $index);

                    $saveDetails .= $this->getSaveDetailsSkeleton(
                        $child->table,
                        $child->column,
                        $fk->column,
                        $index
                    );
                    $updateDetails .= $this->getUpdateDetailsSkeleton(
                        $child->table,
                        $child->column,
                        $fk->column,
                        $index
                    );
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

        // FIELDS and LENGTHS
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
            $typeParam = $typeParamReturn = "";
            if (in_array($column->type, $this->float)) {
                $typeParam = "INT";
                $typeParamReturn = "float";
            } elseif (in_array($column->type, $this->int)) {
                $typeParam = "INT";
                $typeParamReturn = "int";
            } elseif (in_array($column->type, $this->str)) {
                $typeParam = "STR";
                $typeParamReturn = "string";
                if (in_array($column->type, $this->datetime)) {
                    $typeParamReturn = "DateTime";
                }
            } elseif (in_array($column->type, $this->boo)) {
                $typeParam = "BOOL";
                $typeParamReturn = "bool";
            }
            $type .= PHP_EOL . <<<TYPE
    /**
     * Tipo de dato del campo "{$column->column}" para ser tratado por PDO
     */
    public const TYPE_{$columnMayus} = self::PARAM_{$typeParam};
TYPE . PHP_EOL;

            // protected columns
            $columnsTwo .= PHP_EOL . <<<PRC
    /**
     *
     * @var {$typeParamReturn}
     */
    protected \${$column->column};
PRC . PHP_EOL;

            // dontwork for all behaviors
            if (isset($column->behaviors->dontwork) === true) {
                $noSave .= "'{$column->column}', ";
                $noUpdate .= "'{$column->column}', ";
            }

            if ($column->auto_increment === true) {
                $noSave .= "'{$column->column}', ";
                $noUpdate .= "'{$column->column}', ";
            }

            if (isset($column->behaviors->update) === true and isset($column->behaviors->default) === false) {
                $noSave .= "'{$column->column}', ";
            }

            if (isset($column->behaviors->insert) === true and isset($column->behaviors->default) === false) {
                $noUpdate .= "'{$column->column}', ";
            }

            if (isset($column->behaviors->delete) === true and isset($column->behaviors->default) === false) {
                $noDelete .= "'{$column->column}',";
            }

            if (isset($column->behaviors->delete) === true) {
                $setDelete .= PHP_EOL . <<<SET_DELETE
                    self::FIELD_{$columnMayus} => (object) array(
                        'value' => \$this->get{$columnCamelCase}(),
                        'type' => self::TYPE_{$columnMayus}
                    ),
SET_DELETE;
                $whereUpdate .= PHP_EOL . <<<WHERE_UPDATE
                NQL::_AND => (object) array(
                    'condition' => self::FIELD_{$columnMayus} . ' IS NULL',
                    'raw' => true
                ),
WHERE_UPDATE;
                $whereDelete .= PHP_EOL . <<<WHERE_DELETE
                    NQL::_AND => (object) array(
                        'condition' => self::FIELD_{$columnMayus} . ' IS NULL',
                        'raw' => true
                    ),
WHERE_DELETE;
            }

            // RESET
            if ($typeParamReturn === 'DateTime') {
                $reset .= PHP_EOL . "{$tab}{$tab}\$this->{$column->column} = new {$typeParamReturn}();";
            } else {
                $reset .= PHP_EOL . "{$tab}{$tab}\$this->{$column->column} = ({$typeParamReturn}) null;";
            }

            // DEFAULTS
            if (isset($column->default) === false and isset($column->behaviors->default) === true) {
                $flag = $this->foundDefault((array)$column->behaviors);
                if ($flag === true) {
                    $defaults .= PHP_EOL . "{$tab}{$tab}\$this->{$column->column} = {$column->behaviors->default};";
                }

                // save with behaviors
                if (isset($column->behaviors->insert) === true) {
                    $noUpdate .= "'{$column->column}', ";
                    if ($typeParamReturn === "DateTime") {
                        $saveDefaults .= PHP_EOL . <<<SAVE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());
{$tab}{$tab}{$tab}}
SAVE_DEFAULTS;
                    } else {
                        $saveDefaults .= PHP_EOL . <<<SAVE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});
{$tab}{$tab}{$tab}}
SAVE_DEFAULTS;
                    }
                }

                // update with behaviors
                if (isset($column->behaviors->update) === true) {
                    $noSave .= "'{$column->column}', ";
                    if ($typeParamReturn === "DateTime") {
                        $updateDefaults .= PHP_EOL . <<<UPDATE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());
{$tab}{$tab}{$tab}}
UPDATE_DEFAULTS;
                    } else {
                        $updateDefaults .= PHP_EOL . <<<UPDATE_DEFAULTS
{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});
{$tab}{$tab}{$tab}}
UPDATE_DEFAULTS;
                    }
                }

                // delete with behaviors
                if (isset($column->behaviors->delete) === true) {
                    $noSave .= "'{$column->column}', ";
                    $noUpdate .= "'{$column->column}', ";
                    if ($typeParamReturn === "DateTime") {
                        $deleteDefaults .= PHP_EOL . <<<DELETE_DEFAULTS
{$tab}{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(date(\$this->getConfigFormatDateTime()), \$this->getConfigFormatDateTime());
{$tab}{$tab}{$tab}{$tab}}
DELETE_DEFAULTS;
                    } else {
                        $deleteDefaults .= PHP_EOL . <<<DELETE_DEFAULTS
{$tab}{$tab}{$tab}{$tab}if (is_null(\$this->{$column->column})) {
{$tab}{$tab}{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}({$column->behaviors->default});
{$tab}{$tab}{$tab}{$tab}}
DELETE_DEFAULTS;
                    }
                }
            }

            $getter = "return \$this->{$column->column};";
            $getterparam = "";
            $typeParamReturnTwo = ($typeParamReturn === "DateTime") ? "string" : $typeParamReturn;
            $setterparam = "{$typeParamReturnTwo} \${$column->column}";
            $setter = "\$this->{$column->column} = \${$column->column};";
            if (in_array($column->type, $this->datetime)) {
                $getterparam = "string \$format = null";
                $getter = "return (\$format === null) ? \$this->{$column->column} : \$this->{$column->column}->setFormat(\$format);";
                $setterparam = "string \${$column->column}, string \$format = null";
                $setter = "\$this->{$column->column} = new DateTime(\${$column->column}, \$format);";
            }
            // GETTER
            $gettersAndSetters .= PHP_EOL . PHP_EOL . <<<GETTER
    /**
     * Obtiene el valor contenido en el campo "{$column->column}"
     *
     * @return {$typeParamReturn}|null
     */
    public function get{$columnCamelCase}({$getterparam}): ?{$typeParamReturn}
    {
        {$getter}
    }
GETTER;
            // SETTER
            if (isset($column->behaviors->encrypt) === true) {
                $setter = "\$this->{$column->column} = hash('{$column->behaviors->encrypt}', \${$column->column});";
            }
            $paramTwo = ($typeParamReturn === "DateTime") ? PHP_EOL . "{$tab} * @param string \$format [opcional]" : null;
            $paramTwotype = ($typeParamReturn === "DateTime") ? "string" : $typeParamReturn;
            $gettersAndSetters .= PHP_EOL . PHP_EOL . <<<SETTER
    /**
     * Setea un valor en la columna "{$column->column}"
     *
     * @param {$paramTwotype} \${$column->column}{$paramTwo}
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
            if (count((array)$pks) > 0) {
                foreach ($pks as $pk) {
                    if ($column->column === $pk->column) {
                        if ($column->auto_increment === true) {
                            $saveExecute .= PHP_EOL . "{$tab}{$tab}{$tab}\$this->set{$columnCamelCase}(\$this->saveBase(self::TABLE, \$data, self::SEQUENCE, \$debug));";
                        } else {
                            $saveExecute .= PHP_EOL . "{$tab}{$tab}{$tab}\$this->saveBase(self::TABLE, \$data, self::SEQUENCE, \$debug);";
                            $noUpdate .= "'{$column->column}', ";
                        }

                        $whereUpdate .= PHP_EOL . <<<WHERE_UPDATE
                (object) array(
                    'condition' => self::FIELD_{$columnMayus},
                    'value' => \$this->get{$columnCamelCase}(),
                    'type' => self::TYPE_{$columnMayus},
                    'raw' => false
                ),
WHERE_UPDATE;
                        $whereDelete .= PHP_EOL . <<<WHERE_DELETE
                    (object) array(
                        'condition' => self::FIELD_{$columnMayus},
                        'value' => \$this->get{$columnCamelCase}(),
                        'type' => self::TYPE_{$columnMayus},
                        'raw' => false
                    ),
WHERE_DELETE;
                        $gettersAndSetters .= PHP_EOL . PHP_EOL . <<<HAS
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

        if (count($tableDetails) > 0) {
            foreach ($tableDetails as $tableDetail) {
                $tableTypeDetailCamelCase = $this->camelCase($tableDetail['type']);
                $tableNameDetailCamelCase = $this->camelCase($tableDetail['name']);
                $tableNameDetail = $tableDetail['name'];
                $tableTypeDetail = $tableDetail['type'];
                $gettersAndSetters .= PHP_EOL . PHP_EOL . <<<GETTER_AND_SETTER
    /**
     * Obtiene un arreglo de objetos en relación a la tabla "{$tableTypeDetail}"
     *
     * @return {$tableTypeDetailCamelCase}[]
     */
    public function get{$tableNameDetailCamelCase}(): array
    {
        return \$this->{$tableNameDetail};
    }

    /**
     * Setea un objeto en relación a la tabla "{$tableTypeDetail}"
     *
     * @param {$tableTypeDetailCamelCase} {$tableNameDetail}
     *
     * @return \self
     */
    public function set{$tableNameDetailCamelCase}({$tableTypeDetailCamelCase} \${$tableNameDetail}): self
    {
        \$this->{$tableNameDetail}[] = \${$tableNameDetail};
        return \$this;
    }
GETTER_AND_SETTER;
            }
        }

        $noSave = trim($noSave, ", ");
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
            \$this->beginTransaction();{$saveDefaults}
            \$data = \$this->createDataForSaveOrUpdate(array({$noSave}));{$saveExecute}{$saveDetails}
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
SAVE;

        $noUpdate = trim($noUpdate, ", ");
        $whereUpdate = trim($whereUpdate, ",");
        $whereDelete = trim($whereDelete, ",");
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
            \$this->beginTransaction();{$updateDefaults}
            \$set = \$this->createDataForSaveOrUpdate(array({$noUpdate}), \$updateColumnsToNULL);
            \$where = array({$whereUpdate}
            );
            parent::updateBase(self::TABLE, \$set, \$where, \$debug);{$updateDetails}
            \$this->commit();
            return \$this;
        } catch (\Exception \$exc) {
            \$this->rollBack();
            \$this->throwNewExceptionFromException(\$exc);
        }
    }
UPDATE;

        $setDelete = trim($setDelete, ",");
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
            if (\$logical === true) {{$deleteDefaults}
                \$set = array({$setDelete}
                );
                \$where = array({$whereDelete}
                );
                parent::updateBase(self::TABLE, \$set, \$where, \$debug);
            } else {
                \$where = array({$whereDelete}
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
        $skeleton = (string)'';
        $require = __DIR__ . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'Skeleton' . DIRECTORY_SEPARATOR .
            'TableBase.php';
        require $require;
        $this->fileSave($outputBase, $tableCamelCase . "Base", $skeleton, true);
    }

    private function foundDefault(array $behaviors): bool
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

    private function getUseNamespace(string $table): string
    {
        return "use Model\\{$this->camelCase($table)};" . PHP_EOL;
    }

    private function getVariableDeclaration(string $table, $suffix = null): string
    {
        $tableName = ($suffix === null) ? $table : $table . $suffix;
        return PHP_EOL . <<<PRC
        /**
         *
         * @var {$this->camelCase($table)}[]
         */
        protected \${$tableName};
PRC . PHP_EOL;
    }

    private function getDetail(string $table, $sufix = null): string
    {
        $table = ($sufix === null) ? $table : $table . $sufix;
        return PHP_EOL . "{$this->tab}{$this->tab}\$this->{$table} = array();";
    }

    private function getNoSaveSkeleton(string $table, $sufix = null): string
    {
        $table = ($sufix === null) ? $table : $table . $sufix;
        return "'{$table}', ";
    }

    private function getNoUpdateSkeleton(string $table, $sufix = null): string
    {
        $table = ($sufix === null) ? $table : $table . $sufix;
        return "'{$table}', ";
    }

    private function getNoDeleteSkeleton(string $table, $sufix = null): string
    {
        $table = ($sufix === null) ? $table : $table . $sufix;
        return "'{$table}',";
    }

    private function getSaveDetailsSkeleton(string $table, string $column, string $fkColumn, $sufix = null): string
    {
        $table = ($sufix === null) ? $table : $table . $sufix;
        return PHP_EOL . <<<SAVE_DETAILS
            if (count(\$this->{$table}) > 0) {
                \$x = 0;
                foreach (\$this->{$table} as \${$table}) {
                    \$this->{$table}[\$x] = \${$table}->set{$this->camelCase($column)}(\$this->get{$this->camelCase($fkColumn)}())
                        ->save(\$debug);
                    \$x++;
                }
            }
SAVE_DETAILS;
    }

    private function getUpdateDetailsSkeleton(string $table, string $column, string $fkColumn, $sufix = null): string
    {
        $table = ($sufix === null) ? $table : $table . $sufix;
        return PHP_EOL . <<<UPDATE_DETAILS
            if (count(\$this->{$table}) > 0) {
                foreach (\$this->{$table} as \${$table}) {
                    \${$table}->set{$this->camelCase($column)}(\$this->get{$this->camelCase($fkColumn)}())
                        ->update(array(), \$debug);
                }
            }
UPDATE_DETAILS;
    }
}
