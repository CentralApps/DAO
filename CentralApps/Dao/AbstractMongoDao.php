<?php
namespace CentralApps\Dao;

class AbstractMongoDao implements DAOInterface
{
	
	protected $databaseEngineReference = 'mongo';
    protected $databaseEngine;
    protected $collectionName = '';
	protected $collection;
    protected $uniqueReferenceField = '_id';
    protected $uniqueReferenceFieldType = 'MongoId';
    protected $fields = array();
	protected $reworkMongoIdsToId = true;
	
	/**
	 * Data Access Object constructor
	 * @param array $container dependency injection container - this is where we will get the database layer from
	 * @return void
	 */
	public function __construct($container)
	{
		$this->container = $container;
		$this->databaseEngine = $container[$this->databaseEngineReference];
		$this->collection = $this->databaseEngine->$collectionName;
		// In a child class you may wish to enforce some constraints on the collection, indexes etc
	}
	
	public function setReworkMongoIdsToId($set)
	{
		$this->reworkMongoIdsToId = $set;
	}
	
	/**
	 * Create from unique reference
	 * @param mixed $unique_reference a unique reference such as a primary key to get data for
	 * @param object $model Optional parameter of the model to use, if null method will have to populate
	 */
	public function createFromUniqueReference($unique_reference, ModelInterface $model=null)
	{
		$unique_value = ($this->uniqueReferenceFieldType == 'MongoId') ? new \MongoId($unique_reference) : $unique_reference;
		$query = array($this->uniqueReferenceField => $unique_value );
		$row = $this->collection->findOne($query);
		if(!is_null($row)) {
			foreach($row as $field => $value ) {
				if($this->reworkMongoIdsToId && $field == '_id') {
					$field = 'id';
				}
				$model->$field = $value;
			}	
		} else {
			throw new \OutOfBoundsException("No document found in the " . $this->collectionName . " collection with a unique reference of " . $unique_reference);
		}	
	}
	
	/**
	 * Creates a collection of models based of a named query and some db parameters
	 * @param string $named_query the reference of the query (should relate to a private method within DAO class)
	 * @param array $parameters query parameters to be passed to the named query method
	 * @param IteratorAggregate $collection Optional pre-existing collection for these models to go into
	 */
	public function createCollectionFromNamedQuery($named_query, array $paramaters=array(), \IteratorAggregate $collection=null)
	{
		throw new \LogicException("The createCollectionFromNamedQuery must be implemented if you want to use it for a specific model");
	}
	
    /**
     * Save a model in the database
     * @param object $model
     * @return void
     * @throws OutOfBoundsException
     * @throws \LogicException
     */
	public function save(ModelInterface $model)
	{
		
	}
	
    /**
     * Delete a model from the database
     * @param object $model
     * @return void
     * @throws \OutOfBoundsException
     */
	public function delete(ModelInterface $object)
	{
		
	}
	
    /**
     * Save multiple records in the database in one go
     * @param array $collection a collection of models
     * @return void
     * @throws \OutOfBoundsException
     */
	public function saveMany($collection)
	{
		
	}
    
    /**
     * Get properties for a model from database fields
     * @return array
     */
    public function getProperties()
	{
		$fields = array_keys($this->fields);
		if($this->reworkMongoIdsToId && in_array('_id', $fields) && ! in_array('id', $fields)) {
			$fields[] = 'id';
			unset($fields['_id']);
		}
		return $fields;
	}
    
    /**
     * Get the field used as unique reference in the database, typically PK
     * @return string
     */
    public function getUniqueReferenceField()
	{
		
	}
}
