<?php
namespace Skrz\Templating\Engine\AST;

/**
 * An expression, e.g. { -->$var|trim<-- }
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class ExpressionNode extends AbstractNode
{

	/** @var PHPNode */
	private $expression;

	/** @var ModifierNode[] */
	private $modifiers;

	public function __construct($expression, array $modifiers = array())
	{
		$this->expression = $expression;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return \Skrz\Templating\Engine\AST\PHPNode
	 */
	public function getExpression()
	{
		return $this->expression;
	}

	/**
	 * @return \Skrz\Templating\Engine\AST\ModifierNode[]
	 */
	public function getModifiers()
	{
		return $this->modifiers;
	}

}
