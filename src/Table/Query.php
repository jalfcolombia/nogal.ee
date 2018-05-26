<?php

/**
 * This file is part of the NogalEE package.
 *
 * (c) Julian Lasso <jalasso69@misena.edu.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NogalEE\Table;

use NogalEE\NQL;
use NogalEE\Nogal;

/**
 * Clase para usar como extención en una clase representativa de una tabla.
 * Ejemplo class Usuario extends Query
 *
 * @author Julian Lasso <jalasso69@misena.edu.co>
 */
abstract class Query extends Nogal implements \Iterator
{

    protected static $instence = null;

    protected $answer;

    /**
     *
     * @var NQL
     */
    protected $nql;

    /**
     *
     * @var string
     */
    protected $table;

    /**
     * Contiene las columnas de la tabla, ejemplo:<br>
     * id, username, password, actived
     *
     * @var string;
     */
    protected $columns;

    /**
     * array('deleted' => true)
     *
     * @var array
     */
    protected $behavior;

    /**
     *
     * @var string
     */
    protected $fieldActive;

    /**
     *
     * @var string
     */
    protected $fieldDeleted;

    /**
     *
     * @var object
     */
    protected $class;

    public function __construct(array $config, ?string $columns = null)
    {
        parent::__construct($config);
        $this->answer = array();
        $this->nql = new NQL($this->getDataBaseDriver());
        $this->nql->select(($columns !== null) ? $columns : $this->columns)->from($this->table);
    }

    public function rewind(): void
    {
        reset($this->var);
    }

    public function key(): int
    {
        return key($this->answer);
    }
    
    public function next(): ?self
    {
        return next($this->answer);
    }
    
    public function valid(): bool
    {
        return (key($this->var) !== NULL && key($this->var) !== FALSE);
    }
    
    public function current(): self
    {
        return current($this->answer);
    }

    public static function select(array $config, ?string $columns = null): self
    {
        if (self::$instence === null) {
            self::$instence = new self($config, $columns);
        }
        return self::$instence;
    }

    public function isActived(): self
    {
        $this->behaviorActived(true);
    }

    public function isInactived(): self
    {
        $this->behaviorActived(false);
    }

    public function find(): self
    {
        $this->behaviorDeleted();
        $this->answer = $this->query($this->nql, $this->class);
        return $this;
    }

    public function findOne(): self
    {
        $this->behaviorDeleted();
        $this->nql->limit(1);
        $answer = $this->query($this->nql, $this->class);
        $this->answer = (isset($answer[0]) === true) ? $answer[0] : null;
        return $this;
    }

    public function findPK(array $id): self
    {
        $where = 0;
        foreach ($id as $field => $data) {
            if ($where === 0) {
                $this->nql->where($field);
                $where ++;
            } else {
                $this->nql->whereCondition(NQL::_AND, $field);
            }
            if (is_object($data) === true) {
                $this->setQueryParam(":{$field}", $data->value, $data->type);
            } else {
                $this->setQueryParam(":{$field}", $data);
            }
        }
        $this->behaviorDeleted();
        $this->answer = $this->query($this->nql, $this->class);
        return $this;
    }

    public function update(array $set): self
    {
        $this->nql->reset();
        $fields = $this->getColumnsLastQuery();
        $where = 0;
        if (is_object($this->answer) === true) {
            $set = '';
            foreach ($set as $field => $data) {
                if (is_object($data) === true) {
                    $this->setQueryParam(":{$field}", $data->value, $data->type);
                } else {
                    $this->setQueryParam(":{$field}", $data);
                }
                $set .= "{$field}, ";
            }
            $this->nql->update($this->table)->set(substr($set, 0, -2));
            foreach ($fields as $field) {
                $fieldUpper = 'FIELD_' . strtoupper($field);
                $fieldUpperType = strtoupper($field) . '_TYPE';
                $fieldCamelCase = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                if ($where === 0) {
                    $where++;
                    $this->nql->where(ucfirst($this->table)::$fieldUpper);
                } else {
                    $this->nql->whereCondition(NQL::_AND, ucfirst($this->table)::$fieldUpper);
                }
                $this->setQueryParam(':' . self::$fieldUpper, $this->answer->$fieldCamelCase(), self::$fieldUpperType);
            }
            $this->execute($this->nql);
        } else if (is_array($this->answer) === true) {
            foreach ($this->answer as $row) {
                $set = '';
                foreach ($set as $field => $data) {
                    if (is_object($data) === true) {
                        $this->setQueryParam(":{$field}", $data->value, $data->type);
                    } else {
                        $this->setQueryParam(":{$field}", $data);
                    }
                    $set .= "{$field}, ";
                }
                $this->nql->update($this->table)->set(substr($set, 0, -2));
                foreach ($fields as $field) {
                    $fieldUpper = 'FIELD_' . strtoupper($field);
                    $fieldUpperType = strtoupper($field) . '_TYPE';
                    $fieldCamelCase = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                    if ($where === 0) {
                        $where++;
                        $this->nql->where(ucfirst($this->table)::$fieldUpper);
                    } else {
                        $this->nql->whereCondition(NQL::_AND, ucfirst($this->table)::$fieldUpper);
                    }
                    $this->setQueryParam(':' . self::$fieldUpper, $row->$fieldCamelCase(), self::$fieldUpperType);
                }
                $this->execute($this->nql);
                $this->nql->reset();
            }
        }
        return $this;
    }

    public function count(): int
    {
        return count($this->answer);
    }

    public function limit(int $limit): self
    {
        return $this;
    }

    public function offset(int $offset): self
    {
        return $this;
    }

    public function paginate(int $page, int $items): self
    {
        return $this;
    }

    protected function delete(): self
    {
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $this->nql->reset();
        $fields = $this->getColumnsLastQuery();
        $where = 0;
        if (is_object($this->answer) === true) {
            $set = '';
            foreach ($set as $field => $data) {
                if (is_object($data) === true) {
                    $this->setQueryParam(":{$field}", $data->value, $data->type);
                } else {
                    $this->setQueryParam(":{$field}", $data);
                }
                $set .= "{$field}, ";
            }
            $this->nql->update($this->table)->set(substr($set, 0, -2));
            foreach ($fields as $field) {
                $fieldUpper = 'FIELD_' . strtoupper($field);
                $fieldUpperType = strtoupper($field) . '_TYPE';
                $fieldCamelCase = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                if ($where === 0) {
                    $where++;
                    $this->nql->where(ucfirst($this->table)::$fieldUpper);
                } else {
                    $this->nql->whereCondition(NQL::_AND, ucfirst($this->table)::$fieldUpper);
                }
                $this->setQueryParam(':' . self::$fieldUpper, $this->answer->$fieldCamelCase(), self::$fieldUpperType);
            }
            $this->execute($this->nql);
        } else if (is_array($this->answer) === true) {
            foreach ($this->answer as $row) {
                $set = '';
                foreach ($set as $field => $data) {
                    if (is_object($data) === true) {
                        $this->setQueryParam(":{$field}", $data->value, $data->type);
                    } else {
                        $this->setQueryParam(":{$field}", $data);
                    }
                    $set .= "{$field}, ";
                }
                $this->nql->update($this->table)->set(substr($set, 0, -2));
                foreach ($fields as $field) {
                    $fieldUpper = 'FIELD_' . strtoupper($field);
                    $fieldUpperType = strtoupper($field) . '_TYPE';
                    $fieldCamelCase = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                    if ($where === 0) {
                        $where++;
                        $this->nql->where(ucfirst($this->table)::$fieldUpper);
                    } else {
                        $this->nql->whereCondition(NQL::_AND, ucfirst($this->table)::$fieldUpper);
                    }
                    $this->setQueryParam(':' . self::$fieldUpper, $row->$fieldCamelCase(), self::$fieldUpperType);
                }
                $this->execute($this->nql);
                $this->nql->reset();
            }
        }
        return $this;
    }

    protected function orderBy($condition, $type_order = NQL::ASC): self
    {}

    protected function groupBy($condition): self
    {}

    /**
     *
     * @param string $field
     *            Nombre del campo por cual buscar
     * @param string|\stdClass $value
     *            Los valores a pasar son value y type
     * @param string $logical_operator
     *            [opcional] Operador lógico, por defecto igual (=)
     * @return array Retorna un arreglo de objectos
     */
    protected function findBy(string $field, $value, string $logical_operator = '='): self
    {
        $this->nql->where($field, false, $logical_operator);
        $this->behaviorDeleted();
        if (is_object($value) === true) {
            $this->setQueryParam(":{$field}", $value->value, $value->type);
        } else {
            $this->setQueryParam(":{$field}", $value);
        }
        $this->answer = $this->query($this->nql, $this->class);
        return $this;
    }

    /**
     *
     * @param string $field
     *            Nombre del campo por cual buscar
     * @param string|\stdClass $value
     *            Los valores a pasar son value y type
     * @param string $logical_operator
     *            [opcional] Operador lógico, por defecto igual (=)
     * @return array Retorna un arreglo de objectos
     */
    protected function findOneBy(string $field, $value, string $logical_operator = '='): ?self
    {
        $this->nql->where($field, false, $logical_operator);
        $this->behaviorDeleted();
        if (is_object($value) === true) {
            $this->setQueryParam(":{$field}", $value->value, $value->type);
        } else {
            $this->setQueryParam(":{$field}", $value);
        }
        $this->nql->limit(1);
        $answer = $this->query($this->nql, $this->class);
        $this->answer = (isset($answer[0]) === true) ? $answer[0] : null;
        return $this;
    }

    protected function filterBy(): self
    {}
    
    protected function join(string $table, array $condition = array(), string $type = NQL::JOIN): self
    {
        return $this;
    }

    private function behaviorActived(bool $value): void
    {
        if (preg_match("/(\swhere\s)/gim", (string) $this->nql) === false) {
            // no encontró el where
            $this->nql->where("{$this->fieldActive} = {$value}", true);
        } else {
            // encontró el where
            $this->nql->condition(NQL::_AND, "{$this->fieldActive} = {$value}", true);
        }
    }

    private function behaviorDeleted(): void
    {
        // IMPORTANTE
        // preguntar si existe el deleted_at
        // de no existir y estár habilitado, entonces buscar para ponerlo
        if (preg_match("/(\swhere\s)/gim", (string) $this->nql) === false and isset($this->behavior['deleted']) === true and $this->behavior['deleted'] === true) {
            // no encontró el where
            $this->nql->where("{$this->fieldDeleted} IS NULL ", true);
        } else if (isset($this->behavior['deleted']) === true and $this->behavior['deleted'] === true) {
            // encontró el where
            $this->nql->condition(NQL::_AND, "{$this->fieldDeleted} IS NULL ", true);
        }
    }
    
}