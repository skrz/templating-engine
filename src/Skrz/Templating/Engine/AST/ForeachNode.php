<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {foreach ...} ... {/foreach}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class ForeachNode extends AbstractNode
{

	/** @var PHPNode */
	private $expression;

	/** @var string */
	private $name;

	/** @var string */
	private $keyVariableName;

	/** @var string */
	private $valueVariableName;

	/** @var AbstractNode[] */
	private $body;

	/** @var AbstractNode[] */
	private $elseBody;

	public function __construct($expression, $name, $keyVariableName, $valueVariableName, $body, $elseBody)
	{
		$this->expression = $expression;
		$this->name = $name;
		$this->keyVariableName = $keyVariableName;
		$this->valueVariableName = $valueVariableName;
		$this->body = $body;
		$this->elseBody = $elseBody;
	}

	public function getExpression()
	{
		return $this->expression;
	}

	public function getName()
	{
		return $this->name;
	}

	public function hasName()
	{
		return $this->name !== null;
	}

	public function getKeyVariableName()
	{
		return $this->keyVariableName;
	}

	public function getValueVariableName()
	{
		return $this->valueVariableName;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getElseBody()
	{
		return $this->elseBody;
	}

	public function hasElseBody()
	{
		return $this->elseBody !== null;
	}

}
