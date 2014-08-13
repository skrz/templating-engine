<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {$var}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class EchoNode extends AbstractNode
{

	/** @var ExpressionNode */
	private $expression;

	/** @var boolean */
	private $noFilter;

	public function __construct($expression, $noFilter)
	{
		$this->expression = $expression;
		$this->noFilter = $noFilter;
	}

	public function getExpression()
	{
		return $this->expression;
	}

	/**
	 * @return boolean
	 */
	public function getNoFilter()
	{
		return $this->noFilter;
	}

}
