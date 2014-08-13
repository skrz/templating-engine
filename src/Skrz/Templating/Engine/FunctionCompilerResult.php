<?php
namespace Skrz\Templating\Engine;

/**
 * Transfer object for result of function compilation
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class FunctionCompilerResult
{

	/** @var string */
	private $statement;

	public function __construct($statement)
	{
		$this->statement = $statement;
	}

	public function getStatement()
	{
		return $this->statement;
	}

}
