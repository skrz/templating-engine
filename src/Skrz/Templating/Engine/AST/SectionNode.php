<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {section ...} ... {/section}
 *
 * @deprecated
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class SectionNode extends AbstractNode
{

	/** @var string */
	private $name;

	/** @var PHPNode */
	private $expression;

	/** @var AbstractNode[] */
	private $body;

	public function __construct($name, $expression, $body)
	{
		$this->name = $name;
		$this->expression = $expression;
		$this->body = $body;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getExpression()
	{
		return $this->expression;
	}

	public function getBody()
	{
		return $this->body;
	}

}
