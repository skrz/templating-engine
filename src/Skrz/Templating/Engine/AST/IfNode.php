<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {if ...} ... {/if}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class IfNode extends AbstractNode
{

	/** @var AbstractNode */
	private $condition;

	/** @var AbstractNode[] */
	private $body;

	/** @var IfNode[] */
	private $elseifs = array();

	/** @var AbstractNode[] */
	private $elseBody;

	public function __construct($condition, $body, $elseifs = array(), $elseBody = null)
	{
		$this->condition = $condition;
		$this->body = $body;
		$this->elseifs = $elseifs;
		$this->elseBody = $elseBody;
	}

	public function getCondition()
	{
		return $this->condition;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getElseifs()
	{
		return $this->elseifs;
	}

	public function hasElseBody()
	{
		return $this->elseBody !== null;
	}

	public function getElseBody()
	{
		return $this->elseBody;
	}

}
