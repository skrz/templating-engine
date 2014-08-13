<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {$var -->|trim<--}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class ModifierNode extends AbstractNode
{

	/** @var string */
	private $name;

	/** @var PHPNode[] */
	private $arguments = array();

	/** @var string */
	private $fileName;

	/** @var int */
	private $row;

	/** @var int */
	private $column;

	public function __construct($name, array $arguments, $fileName, $row, $column)
	{
		$this->name = $name;
		$this->arguments = $arguments;
		$this->fileName = $fileName;
		$this->row = $row;
		$this->column = $column;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getArguments()
	{
		return $this->arguments;
	}

	public function hasArgument($i)
	{
		return isset($this->arguments[$i]);
	}

	public function getArgument($i)
	{
		return $this->arguments[$i];
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function getRow()
	{
		return $this->row;
	}

	public function getColumn()
	{
		return $this->column;
	}

}
