<?php
namespace CentralApps\Dao;

trait SaveDispatchingTrait
{
    protected $resourceName = null;
    
    public function save(GettableContainerModelInterface $model)
    {
        // TODO: this method is a little clunky and tied heavily to the event work in CA\Base
        // this needs resolving. Also, propagateEvents is not defined in the interface above
        // Needs refactoring as soon as possible.
        
        $resource_name = (is_null($this->resourceName)) ? $model->getDaoContainerKey() : $this->resourceName;
        $container = $model->getContainer();
        $event = $container['standard_event']($model);

        try {
            $return = parent::save($model);
        } catch (\Exception $e) {
            if ($model->propagateEvents()) {

                $container['dispatcher']->dispatch($resource_name . '.saving_failed', $event);
            }

            throw $e;
        }

        if ($model->propagateEvents()) {
            $container['dispatcher']->dispatch($resource_name . '.saved', $event);
        }

        return $return;
    }
}
