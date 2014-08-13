<?php
namespace Skrz\Templating\Engine;

/**
 * Transfer object for compilation result of modifier
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class ModifierCompilerResult
{

	/** @var string */
	private $expression;

	/** @var string[] */
	private $statement;

	public function __construct($expression, $statement = null)
	{
		$this->expression = $expression;
		$this->statement = $statement;
	}

	public function getExpression()
	{
		return $this->expression;
	}

	public function getStatement()
	{
		return $this->statement;
	}

}
