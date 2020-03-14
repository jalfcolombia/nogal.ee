<?php

use NogalEE\Nogal;

class DataBase extends Nogal {

    private $schema;

    public function getAllAttributes(string $db): array {
        $sql = <<<SQL
  SELECT a.[TABLE_SCHEMA] AS "schema"
    ,a.[TABLE_NAME] AS "table"
    ,a.[COLUMN_NAME] AS "column"
    ,b.[is_identity] AS "identity"
    ,a.[COLUMN_DEFAULT] AS "default"
    ,b.[is_nullable] AS "nullable"
    ,a.[DATA_TYPE] AS "type"
    ,a.[CHARACTER_MAXIMUM_LENGTH] AS "length"
  FROM [{$db}].[INFORMATION_SCHEMA].[COLUMNS] AS a, (
      SELECT TOP (900000) b.[name] AS "schema", a.[name] AS "table", c.[name] AS "column",
	  c.[max_length], c.[is_nullable], c.[is_identity]
	  FROM [{$db}].[sys].[tables] AS a
	  JOIN [{$db}].[sys].[schemas] AS b ON a.schema_id = b.schema_id
	  JOIN [{$db}].[sys].[columns] AS c ON a.object_id = c.object_id
	  ORDER BY "schema" ASC, "table" ASC, c.[column_id] ASC
  ) AS b
  WHERE (a.[TABLE_SCHEMA] = b."schema"
  AND a.[TABLE_NAME] = b."table"
  AND a.[COLUMN_NAME] = b."column")
  ORDER BY a.[TABLE_SCHEMA] ASC, a.[TABLE_NAME] ASC, a.[ORDINAL_POSITION] ASC
SQL;
        try {
            $this->beginTransaction();
            $this->schema = array();
            $init = $this->query($sql);
            foreach ($init as $key => $item) {
                if (isset($this->schema[$item->table]) === false) {
                    $this->schema[$item->table] = new stdClass();
                }
                if (isset($this->schema[$item->table]->columns) === false) {
                    $this->schema[$item->table]->columns = new stdClass();
                }
                if (isset($this->schema[$item->table]->pks) === false) {
                    $this->schema[$item->table]
                            ->pks = $this->searchPrimaryKeys($item->table, $item->schema);
                }
                if (isset($this->schema[$item->table]->fks) === false) {
                    $this->schema[$item->table]
                            ->fks = $this->searchForeignKeys($item->table, $item->schema);
                } else {
                    $this->schema[$item->table]->fks = (object) array_merge(
                                    (array) $this->schema[$item->table]->fks,
                                    (array) $this->searchForeignKeys($item->table, $item->schema)
                    );
                }
                if (is_null($item->schema) === false) {
                    $this->schema[$item->table]->schema = $item->schema;
                }

                $this->schema[$item->table]->columns->{$item->column} = new stdClass();
                if (is_null($item->column) === false) {
                    $this->schema[$item->table]->columns->{$item->column}->column = $item->column;
                }
                if (is_null($item->type) === false) {
                    $this->schema[$item->table]->columns->{$item->column}->type = $item->type;
                }
                $this->schema[$item->table]->columns->{$item->column}->auto_increment = (bool) $item->identity;
                $this->schema[$item->table]->columns->{$item->column}->null = (bool) $item->nullable;
                if (is_null($item->length) === false) {
                    $this->schema[$item->table]->columns->{$item->column}->length = $item->length;
                }
                if (is_null($item->default) === false) {
                    $default = null;
                    if (preg_match('/^\(\((\d+|-\d+)(.\d+)?\)\)/', $item->default)) {
                        $default = (float) substr($item->default, 2, -2);
                    } elseif (preg_match('/^\(\'.+\'\)/', $item->default)) {
                        $default = (string) substr($item->default, 1, -1);
                    }
                    if (is_null($default) === false) {
                        $this->schema[$item->table]->columns->{$item->column}->default = $default;
                    }
                }

                $behaviors = $this->searchProperties($item->schema, $item->table, $item->column);
                if (count((array) $behaviors) > 0) {
                    $this->schema[$item->table]->columns->{$item->column}->behaviors = $behaviors;
                }
            }
            return $this->schema;
        } catch (\Exception | \RuntimeException $exc) {
            $this->rollBack();
            $this->throwNewExceptionFromException($exc);
        }
    }

    private function searchPrimaryKeys(string $table, string $schema): object {
        $sql = "EXEC sp_pkeys @table_name = '{$table}', @table_owner = '{$schema}'";
        try {
            $this->beginTransaction();
            $init = $this->query($sql);
            $rsp = new stdClass();
            foreach ($init as $key => $item) {
                $rsp->{$item->COLUMN_NAME} = new stdClass();
                $rsp->{$item->COLUMN_NAME}->column = $item->COLUMN_NAME;
            }
            return $rsp;
        } catch (\Exception | \RuntimeException $exc) {
            $this->rollBack();
            $this->throwNewExceptionFromException($exc);
        }
    }

    private function searchForeignKeys(string $table, string $schema): object {
        $flag = false;
        $sql = "EXEC sp_fkeys @pktable_name = '{$table}', @pktable_owner = '{$schema}'";
        try {
            $this->beginTransaction();
            $init = $this->query($sql);
            $rsp = new stdClass();
            if (count($init) > 0) {
                foreach ($init as $key => $item) {
                    if (isset($rsp->{$item->PKCOLUMN_NAME}) === false) {
                        $rsp->{$item->PKCOLUMN_NAME} = new stdClass();
                    }
                    if (isset($rsp->{$item->PKCOLUMN_NAME}->column) === false) {
                        $rsp->{$item->PKCOLUMN_NAME}->column = $item->PKCOLUMN_NAME;
                    }
                    $rsp->{$item->PKCOLUMN_NAME}->childs[$key] = new stdClass();
                    $rsp->{$item->PKCOLUMN_NAME}->childs[$key]->schema = $item->FKTABLE_OWNER;
                    $rsp->{$item->PKCOLUMN_NAME}->childs[$key]->table = $item->FKTABLE_NAME;
                    $rsp->{$item->PKCOLUMN_NAME}->childs[$key]->column = $item->FKCOLUMN_NAME;

                    if (isset($this->schema->{$item->FKTABLE_NAME}->fks) === false) {
                        if (isset($this->schema[$item->FKTABLE_NAME]) === false) {
                            $this->schema[$item->FKTABLE_NAME] = new stdClass();
                        }
                        if (isset($this->schema[$item->FKTABLE_NAME]->fks) === false) {
                            $this->schema[$item->FKTABLE_NAME]->fks = new stdClass();
                        }
                        $this->schema[$item->FKTABLE_NAME]
                                ->fks->{$item->FKCOLUMN_NAME} = new stdClass();
                        $this->schema[$item->FKTABLE_NAME]
                                ->fks->{$item->FKCOLUMN_NAME}->parent = new stdClass();
                        $this->schema[$item->FKTABLE_NAME]
                                ->fks->{$item->FKCOLUMN_NAME}->parent->schema = $item->PKTABLE_OWNER;
                        $this->schema[$item->FKTABLE_NAME]
                                ->fks->{$item->FKCOLUMN_NAME}->parent->table = $item->PKTABLE_NAME;
                        $this->schema[$item->FKTABLE_NAME]
                                ->fks->{$item->FKCOLUMN_NAME}->parent->column = $item->PKCOLUMN_NAME;
                        $this->schema[$item->FKTABLE_NAME]
                                ->fks->{$item->FKCOLUMN_NAME}->column = $item->FKCOLUMN_NAME;
                        $flag = true;
                    }
                }
            }
            return $rsp;
        } catch (\Exception | \RuntimeException $exc) {
            $this->rollBack();
            $this->throwNewExceptionFromException($exc);
        }
    }

    private function searchProperties(string $schema, string $table, string $column): stdClass {
        $sql = <<<SQL
SELECT value as comment
FROM sys.extended_properties
WHERE major_id = OBJECT_ID('{$schema}.{$table}')
AND minor_id = COLUMNPROPERTY(major_id, '{$column}', 'ColumnId')
SQL;
        try {
            $this->beginTransaction();
            $tmp = array();
            $behavior = new stdClass();
            $init = $this->query($sql);
            if (count($init) > 0) {
                if (preg_match_all('/(@dontwork)|(@insert)|(@update)|(@noupdate)|(@delete)|(@default)|(@encrypt)/', $init[0]->comment) > 0) {
                    $tmp = explode('@', $init[0]->comment);
                    $tmp = array_splice($tmp, 1);
                    foreach ($tmp as $item) {
                        if ($hola = preg_match_all('/^(default:)/', $item) > 0) {
                            $behavior->default = substr($item, 8);
                        } elseif (preg_match_all('/^(encrypt:)/', $item) > 0) {
                            $behavior->encrypt = substr($item, 8);
                        } else {
                            $behavior->{trim($item)} = true;
                        }
                    }
                }
            }
            return $behavior;
        } catch (\Exception | \RuntimeException $exc) {
            $this->rollBack();
            $this->throwNewExceptionFromException($exc);
        }
    }

}
