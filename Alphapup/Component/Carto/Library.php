<?php
namespace Alphapup\Component\Carto;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\CommitOrderCalculator;
use Alphapup\Component\Carto\Librarian\BasicEntityLibrarian;
use Alphapup\Component\Carto\Proxy\CollectionProxy;
use Alphapup\Component\Carto\Proxy\Proxy;

class Library
{
	const
		STATE_NEW = 1,
		STATE_MANAGED = 2,
		STATE_DETACHED = 3,
		STATE_REMOVED = 4;
		
	private
		$_carto,
		$_changesets = array(),
		$_collectionLibrarians = array(),
		$_collectionsToUpdate = array(),
		$_commitOrderCalculator,
		$_entities = array(),
		$_identifiers = array(),
		$_librarians = array(),
		$_originals = array(),
		$_map = array(),
		$_states = array(),
		$_toDelete = array(),
		$_toExtraUpdate = array(),
		$_toInsert = array(),
		$_toUpdate = array(),
		$_visitedCollections = array();
	
	public function __construct(Carto $carto)
	{
		$this->_carto = $carto;
		$this->_commitOrderCalculator = new CommitOrderCalculator();
	}
	
	public function _doPersist($entity)
	{
		// create local id for entity
		$oid = $this->createId($entity);
		
		// get entity state
		$state = $this->determineEntityState($entity);
		
		switch($state) {
			case self::STATE_MANAGED:
				// It's already managed
				break;
				
			case self::STATE_NEW:
				$this->persistNew($this->_carto->mapping(get_class($entity)),$entity);
				break;
				
			case self::STATE_REMOVED:
				// was set for deletion, but now is being
				// persisted again
				unset($this->_toDelete[$oid]);
				$this->_states[$oid] = self::STATE_MANAGED;
				break;
				
			case self::STATE_DETACHED:
				break;
		}
	}
	
	public function addManaged($entity)
	{
		$oid = $this->addToLibrary($entity);
		$this->_states[$oid] = self::STATE_MANAGED;
	}
	
	public function addToLibrary($entity)
	{
		$className = get_class($entity);
		$mapping = $this->_carto->mapping($className);
		
		// create unique id
		$oid = $this->createId($entity);
		
		// create entity's id hash
		$entityIdValues = $mapping->entityIdValues($entity);
		$idHash = implode(' ',$entityIdValues);
		
		// add to entities
		$this->_entities[$oid] = $entity;

		// add to map
		$this->_map[$className][$idHash] = $oid;
		
		// add to id's
		$this->_identifiers[$oid] = $entityIdValues;
		
		// add to originals
		$this->_originals[$oid] = clone $entity;
		
		return $oid;
	}
	
	public function calculateAssociationChanges($assoc,$value)
	{
		if($value instanceof CollectionProxy && $value->isDirty()) {
			
			if($assoc['isOwningSide']) {
				$this->_collectionsToUpdate[] = $value;
			}
			$this->_visitedCollections[] = $value;
		}
		
		// Look through the entities, and in any of their associations, for transient (new)
		// entities, recursively ("Persistence by reachability")
		if($assoc['type'] & Mapping::TO_ONE) {
			if($value instanceof Proxy && !$value->__isInitialized__) {
				return; // Ignore unintialized proxy object
			}
			$value = array($value);
		}elseif($value instanceof CollectionProxy) {
			$value = $value->collection();
		}
		
		$targetMapping = $this->_carto->mapping($assoc['entity']);
		foreach($value as $entry) {
			$state = $this->determineEntityState($entry,self::STATE_NEW);
			$oid = $this->createId($entry);
			if($state == self::STATE_NEW) {
				$this->persistNew($targetMapping,$entry);
				$this->calculateChangeset($targetMapping,$entry);
			
			}elseif($state == self::STATE_REMOVED) {
				// DO EXCEPTION
				return;
			}elseif($state == self::STATE_DETACHED) {
				// DO EXCEPTION
				return;
			}
			// MANAGED associated entities are already taken into account
            // during changeset calculation anyway, since they are in the identity map.
		}
	}
	
	public function calculateChangeset($mapping,$entity)
	{
		$oid = $this->createId($entity);
		
		$actualData = array();
		foreach($mapping->propertyNames() as $propertyName) {
			$value = $mapping->entityValue($entity,$propertyName);
			if($assoc = $mapping->propertyAssociation($propertyName)
				&& ($assoc['type'] & Mapping::TO_MANY)
				&& $value !== null 
				&& !($value instanceof CollectionProxy)) {
				
				// If $value is not an ArrayCollection,
				// then make it one
				if(!$value instanceof ArrayCollection) {
					$value = new ArrayCollection($value);
				}
					
				$collection = new CollectionProxy(
					$this->_carto,
					$mapping,
					$value
				);
				
				$collection->setOwner($entity,$assoc);
				$collection->setDirty(!$collection->isEmpty());
				$mapping->setEntityValue($entity,$propertyName,$collection);
				$actualData[$propertyName] = $collection;
				
			}elseif(!$mapping->propertyIsId($propertyName)) {
				$actualData[$propertyName] = $value;
			}
		}
		
		foreach($mapping->associations() as $propertyName => $assoc) {
			$value = $mapping->entityValue($entity,$propertyName);
			$actualData[$propertyName] = $value;
		}
		
		if(!isset($this->_originals[$oid])) {
			
			// Entity is either NEW or MANAGED but not yet fully persisted (only has an oid).
			// These result in an INSERT
			$this->_originals[$oid] = $actualData;
			$changeset = array();
			foreach($actualData as $propertyName => $value) {
				if($assoc = $mapping->propertyAssociation($propertyName)) {
					if($assoc['type'] & Mapping::TO_ONE) {
						$changeset[$propertyName] = array(null,$value);
					}
				}else{
					$changeset[$propertyName] = array(null,$value);
				}
			}
			$this->_changesets[$oid] = $changeset;
		
		}else{
		
			// Entity is FULLY MANAGED
			$originalEntity = $this->_originals[$oid];
			$originalData = array();
			
			foreach($mapping->propertyNames() as $propertyName) {
				$originalData[$propertyName] = $mapping->entityValue($originalEntity,$propertyName);
			}
			
			$changeset = array();
			
			foreach($actualData as $propertyName => $actualValue) {
				if(isset($originalData[$propertyName])) {
					$originalValue = $originalData[$propertyName];
				}elseif(array_key_exists($propertyName,$originalData)) {
					$originalValue = null;
				}else{
					// skip property, it's partially omitted!
					continue;
				}
				
				if($assoc = $mapping->propertyAssociation($propertyName)) {
					if($assoc['type'] & Mapping::TO_ONE && $originalValue !== $actualValue) {
						if($assoc['isOwningSide']) {
							$changeset[$propertyName] = array($originalValue,$actualValue);
						}
					}elseif($originalValue instanceof CollectionProxy && $originalValue !== $actualValue) {
						// The collection was de-referenced, so delete it.
						if(!in_array($originalValue,$this->_collectionsToDelete,true)) {
							$this->_collectionsToDelete[] = $originalValue;
							$changeset[$propertyName] = $originalValue; // Signal changeset, to-many assocs will be ignored
						}
					}
				}elseif($originalValue !== $actualValue) {
					$changeset[$propertyName] = array($originalValue,$actualValue);
				}
			}
			
			if($changeset) {
				$this->_changesets[$oid] = $changeset;
				$this->_originals[$oid] = $entity;
				$this->_toUpdate[$oid] = $entity;
			}
		}
		
		foreach($mapping->associations() as $propertyName => $assoc) {
			$value = $mapping->entityValue($entity,$propertyName);
			if($value !== null && $value !== false) {
				$this->calculateAssociationChanges($assoc,$value);
			}
		}
	}
	
	public function calculateChangesets()
	{
		// Do INSERTed entities first
		foreach($this->_toInsert as $entity) {
			$mapping = $this->_carto->mapping(get_class($entity));
			$this->calculateChangeset($mapping,$entity);
		}
		
		// Compute changes for MANAGED entities
		foreach($this->_map as $className => $oids) {
			$mapping = $this->_carto->mapping($className);
			
			if($mapping->isReadOnly()) {
				continue;
			}
			
			foreach($oids as $oid) {
				$entity = $this->_entities[$oid];
				
				// Ignore uninitialized proxies
				if($entity instanceof Proxy && !$entity->__isInitialized__) {
					continue;
				}
				
				// Only process entities that are not scheduled for insertion
				$oid = $this->createId($entity);
				if(!isset($this->_toInsert[$oid]) && isset($this->_states[$oid])) {
					$this->calculateChangeset($mapping,$entity);
				}
			}
		}
	}
	
	public function classNames()
	{
		return array_keys($this->_map);
	}
	
	public function clearChangesets()
	{
		$this->_changesets = 
		$this->_toUpdate = 
		array();
	}
	
	public function collectionLibrarian(array $assoc)
	{
		$type = $assoc['type'];
		if(!isset($this->_collectionLibrarians[$type])) {
			if($type == Mapping::ONE_TO_MANY) {
				$librarian = new OneToManyLibrarian($this->_carto);
			}elseif($type == Mapping::MANY_TO_MANY) {
				$librarian = new ManyToManyLibrarian($this->_carto);
			}
			$this->_collectionLibrarians[$type] = $librarian;
		}
		return $this->_collectionLibrarians[$type];
	}
	
	public function commit($className=null)
	{
		if(!is_null($className)) {
			$this->clearChangesets();
		}
		$this->calculateChangesets();
		
		$commitOrder = $this->commitOrder();
		
		$this->_carto->dexter()->beginTransaction();
		try {
			// insert
			if($this->_toInsert) {
				foreach($commitOrder as $className) {
					$this->commitInserts($className);
				}
			}
		
			// update
			if($this->_toUpdate) {
				foreach($commitOrder as $className) {
					$this->commitUpdates($className);
				}
			}
		
			// extra updates requested by librarian
			if($this->_toExtraUpdate) {
				$this->commitExtraUpdates();
			}
			
			foreach($this->_collectionsToUpdate as $collection) {
				$this->commitCollectionUpdates($collection);
			}
			
			// deletions come last and are reversed
			if($this->_toDelete) {
				for($count = count($commitOrder), $i = $count-1 ; $i >=0; --$i) {
					$this->commitDeletions($commitOrder[$i]);
				}
			}
		
			$this->_carto->dexter()->commit();
			
		}catch(\Exception $e) {
			$this->_carto->dexter()->rollback();
			throw $e;
		}
		
		// Clean up
		$this->_toInsert = 
		$this->_toUpdate =
		$this->_toExtraUpdate =
		$this->_changeSets = 
		$this->_toDelete = 
		array();
	}
	
	public function commitCollectionUpdates(CollectionProxy $collection)
	{
		$librarian = $this->collectionLibrarian($collection->association());
		
		if(!$assoc['isOwningSide']) {
			return;
		}
		
		$librarian->executeCollectionInserts($collection);
	}
	
	public function commitDeletions($mapping)
	{
		$className = $mapping->className();
		$librarian = $this->librarian($className);
		
		foreach($this->_toDelete as $oid => $entity) {
			if(get_class($entity) == $className || $entity instanceof Proxy && get_parent_class($entity) == $className) {
				$librarian->executeDelete($entity);
				unset(
					$this->_toDelete[$oid],
					$this->_identifiers[$oid],
					$this->_originals[$oid],
					$this->_states[$oid]
				);
				
				// Entity with this $oid after deletion treated as NEW, even if the $oid
                // is obtained by a new entity because the old one went out of scope.
                // $this->_states[$oid] = self::STATE_NEW;

				$ids = $mapping->ids();
				if($mapping->idGenerationIsAuto()) {
					$mapping->setEntityValue($entity,$ids[0],null);
				}
			}
		}
	}
	
	public function commitExtraUpdates()
	{
		foreach($this->_toExtraUpdate as $oid => $update) {
			list($entity,$changeset) = $update;
			$this->_changesets[$oid] = $changeset;
			$librarian = $this->librarian(get_class($entity));
			$librarian->update($entity);
		}
	}

	public function commitInserts($mapping)
	{
		$className = $mapping->className();
		$librarian = $this->librarian($className);
		
		$inserts = array();
		foreach($this->_toInsert as $oid => $entity) {
			if(get_class($entity) == $className) {
				$librarian->addInsert($entity);
				unset($this->_toInsert[$oid]);
			}
		}
		
		$insertIds = $librarian->executeInserts();
		
		if($insertIds) {
			foreach($insertIds as $id => $entity) {
				$oid = spl_object_hash($entity);
				$idProperties = $mapping->ids();
				$idProperty = $idProperties[0];
				$mapping->setEntityValue($entity,$idProperty,$id);
				$this->addManaged($entity);
			}
		}
	}
	
	public function commitOrder()
	{
		$changes = array_merge(
			$this->_toInsert,
			$this->_toUpdate
		);
		
		$calc = $this->_commitOrderCalculator;
		
		// See if there are any new classes in the changeset, that are not in the
        // commit order graph yet (dont have a node).
		$newNodes = array();
		foreach($changes as $oid => $entity) {
			$className = get_class($entity);
			if(!$calc->hasClass($className)) {
				$class = $this->_carto->mapping($className);
				$calc->addClass($class);
				$newNodes[] = $class;
			}
		}
		
		// calculate dependencies for new nodes
		while($mapping = array_pop($newNodes)) {
			foreach($mapping->associations() as $assoc) {
				if($assoc['isOwningSide'] && $assoc['type'] & Mapping::TO_ONE) {
					
					// add class to calculator
					$targetMapping = $this->_carto->mapping($assoc['entity']);
					if(!$calc->hasClass($targetMapping->className())) {
						$calc->addClass($targetMapping);
						$newNodes[] = $targetMapping;
					}
					
					// add dependency to calculator
					$calc->addDependency($targetMapping,$mapping);
					
					// If the targetMapping has mapped subclasses,
					// these share the same dependency
					// See: Symfony 2 unitofwork.php line 877
				}
			}
		}
		
		return $calc->commitOrder();
	}
	
	public function commitUpdates($mapping)
	{
		$className = $mapping->className();
		$librarian = $this->librarian($className);
		
		foreach($this->_toUpdate as $oid => $entity) {
			if(get_class($entity) == $className || ($entity instanceof Proxy && get_parent_class($entity) == $className)) {
				if(isset($this->_changesets[$oid])) {
					$librarian->executeUpdate($entity);
				}
				
				unset($this->_toUpdate[$oid]);
			}
		}
	}
	
	public function createId($entity)
	{
		return spl_object_hash($entity);
	}
	
	public function determineEntityState($entity,$assume=null)
	{
		
		$oid = $this->createId($entity);
		if(!isset($this->_states[$oid])) {
			
			if($assume === null) {
				$className = get_class($entity);
				$mapping = $this->_carto->mapping($className);
		
				// Entity's state first depends on whether or not it
				// has an id(s) - which it would mean it's been persisted
				// before.
				$ids = $mapping->entityIdValues($entity);
		
				// If no set id(s), then it's new
				if(!$ids) {
					return self::STATE_NEW;
		
				// If it has id(s) set, then it's
				}else{
			
					// If this entity is already managed by library
					if($this->entityByEntityIds($ids)) {
						return self::STATE_MANAGED;
				
					// Entity is new to library, but we don't know
					// if it's new to the database.
					}else{
				
						$librarian = $this->librarian($className);
				
						// Exists in the db, but not here
						if($librarian->exists($entity)) {
							return self::STATE_DETACHED;
					
						// Does not exist in the Db
						}else{
							return self::STATE_NEW;
						}
				
					}
				}
			}else{
				return $assume;
			}
		}else{
			return $this->_states[$oid];
		}
	}
	
	public function entitiesByClassName($className)
	{	
		$entities = array();
		foreach($this->oidsByClassName($className) as $oid) {
			$entities[] = $this->_entities[$oid];
		}
		return $entities;
	}
	
	public function entityByEntityIds(array $ids=array())
	{
		$idHash = implode(' ',$ids);
		if(isset($this->_identifiers[$idHash])) {
			return $this->_entities[$this->_identifiers[$idHash]];
		}
		return false;
	}
	
	public function entityChangeset($entity)
	{
		$oid = $this->createId($entity);
		if(isset($this->_changesets[$oid])) {
			return $this->_changesets[$oid];
		}
		return array();
	}
	
	public function entityIdentifier($entity)
	{
		$oid = $this->createId($entity);
		return (isset($this->_identifiers[$oid])) ? $this->_identifiers[$oid] : array();
	}
	
	public function isScheduledForInsert($entity)
	{
		$oid = $this->createId($entity);
		if(isset($this->_toInsert[$oid])) {
			return true;
		}
		return false;
	}
	
	public function isScheduledForUpdate($entity)
	{
		$oid = $this->createId($entity);
		if(isset($this->_toUpdate[$oid])) {
			return true;
		}
		return false;
	}
	
	public function librarian($className)
	{
		$className = $this->_carto->className($className);
		if(isset($this->_librarians[$className])) {
			return $this->_librarians[$className];
		}
		$librarian = new BasicEntityLibrarian($this->_carto,$this->_carto->mapping($className));
		$this->_librarians[$className] = $librarian;
		return $this->_librarians[$className];
	}
	
	public function loadCollection(CollectionProxy $collection)
	{
		$assoc = $collection->association();
		switch($assoc['type']) {
			case Mapping::ONE_TO_MANY:
				$collection = $this->librarian($assoc['entity'])->loadOneToManyCollection(
					$assoc,
					$collection->owner(),
					$collection
				);
				break;
				
			case Mapping::MANY_TO_MANY:
				$collection = $this->librarian($assoc['entity'])->loadManyToManyCollection(
					$assoc,
					$collection->owner(),
					$collection
				);
				break;
		}
		
		return $collection;
	}
	
	public function oidsByClassName($className)
	{
		return (isset($this->_map[$className])) ? $this->_map[$className] : array();
	}
	
	public function persist($entity)
	{
		$this->_doPersist($entity);
	}
	
	public function persistNew($mapping,$entity)
	{
		$oid = $this->createId($entity);
		$this->_states[$oid] = self::STATE_MANAGED;
		$this->scheduleForInsert($entity);
	}
	
	public function remove($entity)
	{
		$oid = $this->createId($entity);
		$state = $this->determineEntityState($entity);
		
		switch($state) {
			case self::STATE_REMOVED:
				// already removed
			case self::STATE_NEW:
				// can ONLY be new if this is the first time (right now)
				// that the library has seen this entity.
				// BECAUSE EVEN IF IT'S SCHEDULED FOR INSERTION, IT HAS
				// BEEN MARKED AS MANAGED
				break;
			case self::STATE_MANAGED:
				$this->scheduleForDeletion($entity);
				break;
			case self::STATE_DETACHED:
				break;
		}
	}
	
	public function scheduleForDeletion($entity)
	{
		$oid = $this->createId($entity);
		if(isset($this->_toInsert[$oid])) {
			// Has never been in db, so we just need to
			// stop insertion
			unset($this->_toInsert[$oid]);
			return;
		}
		$this->_toDelete[$oid] = $entity;
	}
	
	public function scheduleForExtraUpdate($entity,array $changeset)
	{
		$oid = $this->createId($entity);
		if(isset($this->_toExtraUpdate[$oid])) {
			list($ignored,$changeset2) = $this->_toExtraUpdate[$oid];
			$this->_toExtraUpdate[$oid] = array($entity,$changeset+$changeset2);
		}else{
			$this->_toExtraUpdate[$oid] = array($entity,$changeset);
		}
	}
	
	public function scheduleForInsert($entity)
	{
		$oid = $this->createId($entity);
		$this->_toInsert[$oid] = $entity;
	}
	
	public function tryGetById($id,$className)
	{
		$idHash = implode(' ',(array) $id);
		if(isset($this->_map[$className][$idHash])) {
			$oid = $this->_map[$className][$idHash];
			return $this->_entities[$oid];
		}
		return false;
	}
}