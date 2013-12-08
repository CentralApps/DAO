<?php
namespace CentralApps\Dao;

abstract class AbstractPdoFactory extends AbstractFactory
{
    protected function buildFromPdoStatement(\PdoStatement $statement)
    {
        $collection = $this->getCollection();
        $statement->execute();
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $model = new $this->modelClass($this->container);
            if (!($model instanceof ModelInterface)) {
                throw new \LogicException("The abstract factory only supports models which implement ModelInterface and have a hydrate method");
            }
            $model->hydrate($row);
            $collection->add($model);
        }

        return $collection;
    }
}
