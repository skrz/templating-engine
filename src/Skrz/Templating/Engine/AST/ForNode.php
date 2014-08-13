<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {for ...} ... {/for}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class ForNode extends AbstractNode
{

	/** @var string variable name to iterate over */
	private $variableName;

	/** @var PHPNode */
	private $from;

	/** @var PHPNode */
	private $to;

	/** @var PHPNode */
	private $step;

	/** @var AbstractNode[] */
	private $body;

	/** @var AbstractNode[] */
	private $elseBody;

	public function __construct($variableName, $from, $to, $step, $body, $elseBody)
	{
		$this->variableName = $variableName;
		$this->from = $from;
		$this->to = $to;
		$this->step = $step;
		$this->body = $body;
		$this->elseBody = $elseBody;
	}

	public function getVariableName()
	{
		return $this->variableName;
	}

	public function getFrom()
	{
		return $this->from;
	}

	public function getTo()
	{
		return $this->to;
	}

	public function getStep()
	{
		return $this->step;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getElseBody()
	{
		return $this->elseBody;
	}

}
