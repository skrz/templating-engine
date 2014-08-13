<?php
namespace Skrz\Templating\Engine\AST;

/**
 * { -->function_call($arguments, ...)<--- }
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class FunctionNode extends AbstractNode
{

	/** @var string */
	private $name;

	/** @var ExpressionNode[] */
	private $arguments;

	/** @var string */
	private $fileName;

	/** @var int */
	private $row;

	/** @var int */
	private $column;

	public function __construct($name, $arguments, $fileName, $row, $column)
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

	public function getArgument($name)
	{
		if (isset($this->arguments[$name])) {
			return $this->arguments[$name];
		}

		return null;
	}

	public function hasArgument($name)
	{
		return isset($this->arguments[$name]);
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
