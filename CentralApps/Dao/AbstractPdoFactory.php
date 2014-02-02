<?php
namespace CentralApps\Dao;

abstract class AbstractPdoFactory extends AbstractFactory
{
    protected function buildFromPdoStatement(\PdoStatement $statement)
    {
        return $this->buildFromPdoStatementLogic($statement, null);
    }

    protected function buildFromPdoStatementWithCallback(\PdoStatement $statement, callable $callback)
    {
        return $this->buildFromPdoStatementLogic($statement, $callback);
    }

    protected function buildFromPdoStatementLogic(\PdoStatement $statement, callable $callback = null)
    {
        $collection = $this->getCollection();
        $statement->execute();

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $model = new $this->modelClass($this->container);

            if (!($model instanceof ModelInterface)) {
                throw new \LogicException("The abstract factory only supports models which implement ModelInterface and have a hydrate method");
            }

            $model->setExistsInDatabase(true);

            if (!is_null($callback)) {
                $model = call_user_func_array($callback, array($model, &$row));
            }

            $model->hydrate($row);
            $collection->add($model);
        }

        return $collection;
    }
}
