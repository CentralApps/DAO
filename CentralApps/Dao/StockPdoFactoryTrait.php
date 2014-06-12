<?php
namespace CentralApps\Dao;

trait StockPdoFactoryTrait
{
    protected $modelClass = null;
    protected $notFoundExceptionClass = '\\Exception';
    protected $activeTrait = '\\CentralApps\\Base\\Traits\\Active';
    protected $deletableTrait = '\\CentralApps\\Base\\Traits\\Deletable';
    
    public function createNew()
    {
        $class = $this->modelClass;
        return new $class($this->container);
    }

    public function createExisting($id)
    {
        $class = $this->modelClass;
        $object = new $class($this->container, $id);

        if ($object->existsInDatabase()) {
            return $object;
        }

        throw new $this->notFoundExceptionClass();
    }

    public function getFromId($id)
    {
        $table = $this->tableName;

        $sql = "SELECT
                    *
                FROM
                    $table
                WHERE
                    id = :id";

        $sql = $this->accountForTraits($sql, $existing_conditions = true);

        $statement = $this->container['pdo_mysql']->prepare($sql);
        $statement->bindParam(':id', $id, \PDO::PARAM_INT);

        return $this->buildFromPdoStatement($statement);
    }

    public function getAll($including_in_active = false)
    {
        $table = $this->tableName;

        $sql = "SELECT
                    *
                FROM
                    $table";

        $sql = $this->accountForTraits($sql, $existing_conditions = false, $including_in_active);

        $statement = $this->container['pdo_mysql']->prepare($sql);

        return $this->buildFromPdoStatement($statement);
    }

    protected function accountForTraits($sql, $existing_conditions = false, $including_in_active = false, $prefix = '')
    {
        $conditions = array();

        if (($including_in_active == false) && $this->hasActiveTrait()) {
            $conditions[] = " {$prefix}active = 1 ";
        }

        if ($this->hasDeletableTrait()) {
            $conditions[] = " {$prefix}deleted = 0 ";
        }

        if (count($conditions) > 0 ) {
            if (!$existing_conditions) {
                $sql .= " WHERE ";
            } else {
                $sql .= " AND ";
            }

            $sql .= implode(" AND ", $conditions);
        }

        return $sql;
    }

    protected function hasActiveTrait()
    {
        return (in_array($this->activeTrait, class_uses($this->modelClass)));
    }

    protected function hasDeletableTrait()
    {
        return (in_array($this->deletableTrait, class_uses($this->modelClass)));
    }

    protected function buildFromPdoStatementLogic(\PdoStatement $statement, callable $callback = null)
    {
        $collection = parent::buildFromPdoStatementLogic($statement, $callback);

        if ($statement instanceof \CentralApps\Pagination\PdoStatement) {
            $collection->setPagination($statement->getPaginationWithTotalCount());
        }

        return $collection;
    }
}
