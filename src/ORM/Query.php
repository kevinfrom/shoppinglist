<?php

namespace App\ORM;

use App\ORM\Connection\Connection;
use App\ORM\Entity\EntityInterface;
use App\ORM\Model\ModelInterface;

class Query
{

    /**
     * @var array $fields
     */
    private array $fields = [];

    /**
     * @var array $conditions
     */
    private array $conditions = [];

    /**
     * @var array $from
     */
    private array $from = [];

    /**
     * @var int $limits
     */
    private int $limit = 0;

    /**
     * @var ModelInterface $model
     */
    private ModelInterface $model;

    /**
     * Query Constructor
     *
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString(): string
    {
        $where = $this->conditions === [] ? '' : ' WHERE ' . implode(' AND ', $this->conditions);

        if ($this->fields) {
            $result = 'SELECT ' . implode(', ', $this->fields);
        } else {
            $result = 'SELECT * ';
        }

        $result .= 'FROM ' . implode(', ', $this->from);
        $result .= $where;

        if ($this->limit) {
            $result .= ' LIMIT ' . $this->limit;
        }

        return $result . ';';
    }

    /**
     * Add select clause
     *
     * @param string ...$select
     *
     * @return $this
     */
    public function select(string ...$select): Query
    {
        foreach (func_get_args() as $select) {
            $this->fields[$select] = $select;
        }

        return $this;
    }

    /**
     * Add where clause
     *
     * @param string ...$where
     *
     * @return $this
     */
    public function where(string ...$where): Query
    {
        $this->conditions = array_merge($this->conditions, func_get_args());

        return $this;
    }

    /**
     * Add from clause
     *
     * @param string      $table
     * @param string|null $alias
     *
     * @return $this
     */
    public function from(string $table, ?string $alias = null): Query
    {
        $from              = $alias ? "$table as $alias" : $table;
        $this->from[$from] = $from;

        return $this;
    }

    /**
     * Set limit
     *
     * @param int $limit
     *
     * @return Query
     */
    public function limit(int $limit): Query
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Return first result
     *
     * @return EntityInterface|null
     */
    public function first()
    {
        $this->limit(1);
        $result = Connection::getInstance()->query($this->__toString(), true);

        if ($result) {
            $result = $this->model->newEntity($result);
        }

        return $result;
    }

    /**
     * @return EntityInterface[]
     */
    public function all()
    {
        $result = Connection::getInstance()->query($this->__toString());

        return array_map(function ($data) {
            return $this->model->newEntity($data);
        }, $result);
    }
}
