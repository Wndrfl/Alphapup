<?php
namespace Alphapup\Component\Carto;

use Alphapup\Component\Carto\Mapping;

class CommitOrderCalculator
{	
	const 
		NOT_VISITED = 1,
		IN_PROGRESS = 2,
		VISITED = 3;

	private 
		$_classes = array(),
		$_relatedClasses = array(),
		$_sorted = array();
	
	private function _visitNode($node)
	{
		$this->_nodeStates[$node->className()] = self::IN_PROGRESS;
		
		// look for dependencies
		if(isset($this->_relatedClasses[$node->className()])) {
			foreach($this->_relatedClasses[$node->className()] as $relatedNode) {
				if($this->_nodeStates[$relatedNode->className()] == self::NOT_VISITED) {
					$this->_visitNode($relatedNode);
				}
			}
		}
		
		$this->_nodeStates[$node->className()] = self::VISITED;
		$this->_sorted[] = $node;
	}
	
	public function addClass(Mapping $mapping)
	{
		$this->_classes[$mapping->className()] = $mapping;
	}
	
	public function addDependency(Mapping $fromClass,Mapping $toClass)
	{
		$this->_relatedClasses[$fromClass->className()][] = $toClass;
	}
	
	public function clear()
	{
		$this->_classes = 
		$this->_relatedClasses = array();
	}
	
	/*
	* DEPTH FIRST TOPOLOGICAL SORT:
	* L ← Empty list that will contain the sorted nodes
	* S ← Set of all nodes with no outgoing edges
	* for each node n in S do
	*     visit(n) 
	* function visit(node n)
	*     if n has not been visited yet then
	*         mark n as visited
	*         for each node m with an edge from m to n do
	*            visit(m)
	*        add n to L
	*/
	public function commitOrder()
	{
		// Check whether we need to do anything.
		// 0 nodes or 1 node is easy
		$nodeCount = count($this->_classes);
		if($nodeCount == 0) {
			return array();
		}elseif($nodeCount == 1) {
			return array_values($this->_classes);
		}
		
		// Mark each node as NOT VISITED
		foreach($this->_classes as $node) {
			$this->_nodeStates[$node->className()] = self::NOT_VISITED;
		}
		
		// VISIT each node
		foreach($this->_classes as $node) {
			if($this->_nodeStates[$node->className()] == self::NOT_VISITED) {
				$this->_visitNode($node);
			}
		}
		
		// $this->_sorted is now in reverse order
		$sorted = array_reverse($this->_sorted);
		//$sorted = $this->_sorted;
		
		
		foreach($sorted as $class) {
			//echo "<br />1".$class->entityName();
		}
		//echo '<br />';
		
		$this->_sorted = $this->_nodeStates = array();
		
		return $sorted;
	}
	
	public function hasClass($className)
	{
		return (isset($this->_classes[$className])) ? true : false;
	}
}