<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {capture ...} ... {/capture}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class CaptureNode extends AbstractNode
{

	/** @var string */
	private $name = "default";

	/** @var string */
	private $assign;

	/** @var string */
	private $append;

	/** @var AbstractNode[] */
	private $body;

	public function __construct($name, $assign, $append, $body)
	{
		$this->name = $name;
		$this->assign = $assign;
		$this->append = $append;
		$this->body = $body;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getAssign()
	{
		return $this->assign;
	}

	public function getAppend()
	{
		return $this->append;
	}

	public function getBody()
	{
		return $this->body;
	}

}
