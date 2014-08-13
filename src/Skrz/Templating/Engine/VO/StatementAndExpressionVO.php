<?php
namespace Skrz\Templating\Engine\VO;

/**
 * Used by compiler to transfer expression compilation results that need additional statements
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class StatementAndExpressionVO
{

	/** @var string */
	private $statement;

	/** @var string */
	private $expression;

	public function __construct($statement, $expression)
	{
		$this->statement = $statement;
		$this->expression = $expression;
	}

	/**
	 * @return string
	 */
	public function getExpression()
	{
		return $this->expression;
	}

	/**
	 * @return string
	 */
	public function getStatement()
	{
		return $this->statement;
	}

}
