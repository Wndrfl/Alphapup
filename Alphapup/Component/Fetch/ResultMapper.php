<?php
namespace Alphapup\Component\Fetch;

class ResultMapper
{
	private
		$_childrenTableAliasesToParentTableAliases = array(),
		$_columnNamesToClassNames = array(),
		$_columnNamesToPropertyNames = array(),
		$_propertyNamesToColumnNames = array(),
		$_tableAliasesToEntityNames = array();
	
	public function classNameForColumn($columnName)
	{
		return (isset($this->_columnNamesToClassNames[$columnName]))
			? $this->_columnNamesToClassNames[$columnName] : null;
	}
	
	public function entityNameForTableAlias($tableAlias)
	{
		return (isset($this->_tableAliasesToEntityNames[$tableAlias]))
			? $this->_tableAliasesToEntityNames[$tableAlias] : null;
	}
	
	public function mapForeignTableAliasAsChild($childTableAlias,$parentTableAlias)
	{
		$this->_childrenTableAliasesToParentTableAliases[$childTableAlias] = $parentTableAlias;
	}
	
	public function mapTableAliasToClassName($queryAlias,$className)
	{
		$this->_tableAliasesToEntityNames[$queryAlias] = $className;
	}
	
	public function mapMeta($className,$columnName,$columnAlias)
	{
		$this->_metaColumnAliasesToColumnNames[$columnAlias] = $columnName;
		$this->_columnNamesToClassNames[$columnAlias] = $className;
	}
	
	public function mapPropertyNameToColumnName($className,$propertyName,$columnName)
	{
		$this->_propertyNamesToColumnNames[$propertyName] = $columnName;
		$this->_columnNamesToPropertyNames[$columnName] = $propertyName;
		$this->_columnNamesToClassNames[$columnName] = $className;
		return $this;
	}
	
	public function metaColumn($columnName)
	{
		return (isset($this->_metaColumnAliasesToColumnNames[$columnName])) 
			? $this->_metaColumnAliasesToColumnNames[$columnName] : false;
	}
	
	public function parentAliasForTableAlias($tableAlias)
	{
		return (isset($this->_childrenTableAliasesToParentTableAliases[$tableAlias]))
			? $this->_childrenTableAliasesToParentTableAliases[$tableAlias] : null;
	}
	
	public function propertyNameForColumn($columnName)
	{
		return (isset($this->_columnNamesToPropertyNames[$columnName]))
			? $this->_columnNamesToPropertyNames[$columnName] : null;
	}
	
	public function tableAliasForEntityName($className)
	{
		foreach($this->_tableAliasesToEntityNames as $tableAlias => $name) {
			if($name == $className) {
				return $tableAlias;
			}
		}
		return null;
	}
}