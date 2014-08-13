<?php
namespace Skrz\Templating\Engine\AST;

/**
 * Variable assignment {$var = ...}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class AssignmentNode extends AbstractNode
{

	/** @var string */
	private $variableName;

	/** @var string */
	private $path;

	/** @var ExpressionNode */
	private $expression;

	public function __construct($variableName, $path, ExpressionNode $expression)
	{
		$this->variableName = $variableName;
		$this->path = $path;
		$this->expression = $expression;
	}

	public function getVariableName()
	{
		return $this->variableName;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	public function getExpression()
	{
		return $this->expression;
	}

}
