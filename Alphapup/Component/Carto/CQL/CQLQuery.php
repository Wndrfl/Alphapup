<?php
namespace Alphapup\Component\Carto\CQL;

use Alphapup\Component\Carto\Carto;
use Alphapup\Component\Carto\CQL\Lexer;
use Alphapup\Component\Carto\ResultMapping;
use Alphapup\Component\Carto\CQL\Strategizer;
use Alphapup\Component\Carto\CQL\Translator;
use Alphapup\Component\Carto\Hydrator;

class CQLQuery
{
	private
		$_carto,
		$_params=array(),
		$_parseResult,
		$_resultMapping,
		$_statement = null;
		
	public function __construct(Carto $carto,$cql,array $params=array())
	{
		$this->_carto = $carto;
		$this->_cql = $cql;
		$this->_params = $params;
		$this->_resultMapping = new ResultMapping();
		$this->_hydrator = new Hydrator($this->_carto);
	}
	
	public function cql()
	{
		return $this->_cql;
	}
	
	public function execute()
	{
		$lexer = new Lexer();
		$lexer->parseTokens($this->_cql);
		
		$strategizer = new Strategizer($this->_carto,$lexer);
		$strategy = $strategizer->createStrategy();
		
		$parser = new Parser($this->_carto,$lexer,$strategy);
		$parseResult = $parser->parse($this->_carto,$this);
		
		$translator = new Translator($this->_carto,$this,$parseResult,$strategy);
		
		$this->_statement = $parseResult->stmt()->translate($translator);

		return $this->_statement;
	}
	
	public function params()
	{
		return $this->_params;
	}
	
	public function resultMapping()
	{
		return $this->_resultMapping;
	}
	
	public function results()
	{
		$statement = $this->execute();
		$rows = $this->_carto->dexter()->query($statement,$this->_params)->results();
		
		return $this->_hydrator->hydrateAll($rows,$this->resultMapping());
	}
	
	public function statement()
	{
		$this->execute();
		return $this->_statement;
	}
}