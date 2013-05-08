<?php
namespace Alphapup\Component\Introspect\Introspector;

use Alphapup\Component\Introspect\Introspector\BaseIntrospector;

abstract class AnnotatedIntrospector extends BaseIntrospector
{
	protected
		$_annotations;
		
	public function __construct(\Reflector $introspector)
	{
		parent::__construct($introspector);
	}
	
	/**
	 * Looks for annotations in docblocks.
	 * Valid annotations:
	 * 	- @annotation
	 *  - @annotation (key = value, key2 = "value2")
	 * @return array An Array of annotations
	 */
	protected function _parseAnnotations($docComment)
	{	
		$foundAnnotations = array();
	
		if(empty($docComment) || $docComment == '') {
			return $foundAnnotations;
		}
		
		preg_match_all('#@([A-Za-z\_\-\\\]+)\s?(\(([A-Za-z0-9\s\{\}\:\=\,\.\"\'\<\>\/\_\-]+)\))?#',$docComment,$annotations,PREG_SET_ORDER);
		
		// loop through annotations
		foreach($annotations as $annotation) {
			
			$annotationName = $annotation[1];
			$rawParams = (isset($annotation[3])) ? $annotation[3] : false;

			// break apart variables
			if($rawParams && $rawParams != '') {
				
				preg_match_all('#([A-Za-z0-9\_\-]+)\=?([\"\'])?([A-Za-z0-9\{\}\:\_\-\s\.\,\<\>\/]+)($2)?#',$rawParams,$params,PREG_SET_ORDER);
				
				$values = array();
				foreach($params as $param) {
					
					$key = $param[1];
					$quote = isset($param[2]) ? $param[2] : false;
					$value = isset($param[3]) ? $param[3] : false;
					
					// if bool, or number
					if(!$value) {
						$value = true;
					}elseif(!$quote || $quote == '') {
						$numTest = intval($value);
						if($numTest != 0 || $value == '0') {
							$value = $numTest;
						}elseif($value == 'true' || $value == 'false') {
							$value = ($value == 'true') ? true : false;
						}
					}
					$values[$key] = $value;
				}
				
				$foundAnnotations[$annotationName] = $values;
				
			// no value for this annotation
			}else{
				$foundAnnotations[$annotationName] = true;
			}
		}
		
		return $foundAnnotations;
	}
	
	public function annotation($annotation)
	{
		$annotations = $this->annotations();
		return (isset($annotations[$annotation])) ? $annotations[$annotation] : false;
	}
	
	public function annotations()
	{
		if(empty($this->_annotations)) {
			$this->_annotations = $this->_parseAnnotations($this->docComment());
		}
		return $this->_annotations;
	}
	
	public function annotationsWithPrefix($prefix)
	{
		$prefix = rtrim($prefix,'\\');
		$annotations = $this->annotations();
		$withPrefix = array();
		$prefixLen = strlen($prefix)+1;
		foreach($annotations as $name => $meta) {
			if(strstr($name,$prefix.'\\')) {
				$withPrefix[substr($name,$prefixLen)] = $meta;
			}
		}
		return $withPrefix;
	}
	
	public function docComment()
	{
		return $this->_reflector->getDocComment();
	}
	
	public function hasAnnotation($annotation)
	{
		return ($this->annotation($annotation)) ? true : false;
	}
	
	public function hasAnnotationPrefix($prefix)
	{
		$annotations = $this->annotations();
		foreach($annotations as $name => $meta) {
			if(strstr($name,$prefix.'\\')) {
				return true;
			}
		}
		return false;
	}
}