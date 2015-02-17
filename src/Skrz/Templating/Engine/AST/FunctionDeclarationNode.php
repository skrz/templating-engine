<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {function ...} ... {/function}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class FunctionDeclarationNode extends AbstractNode
{

	/** @var string */
	private $name;

	/** @var ExpressionNode[] */
	private $defaultArguments;

	/** @var AbstractNode[] */
	private $body;

	/** @var string */
	private $fileName;

	/** @var int */
	private $row;

	/** @var int */
	private $column;

	public function __construct($name, $defaultArguments, $body, $fileName, $row, $column)
	{
		$this->name = $name;
		$this->defaultArguments = $defaultArguments;
		$this->body = $body;
		$this->fileName = $fileName;
		$this->row = $row;
		$this->column = $column;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return ExpressionNode[]
	 */
	public function getDefaultArguments()
	{
		return $this->defaultArguments;
	}

	/**
	 * @param ExpressionNode[] $defaultArguments
	 * @return self
	 */
	public function setDefaultArguments($defaultArguments)
	{
		$this->defaultArguments = $defaultArguments;
		return $this;
	}

	/**
	 * @return AbstractNode[]
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @param AbstractNode[] $body
	 * @return self
	 */
	public function setBody($body)
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	/**
	 * @param string $fileName
	 * @return self
	 */
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getRow()
	{
		return $this->row;
	}

	/**
	 * @param int $row
	 * @return self
	 */
	public function setRow($row)
	{
		$this->row = $row;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @param int $column
	 * @return self
	 */
	public function setColumn($column)
	{
		$this->column = $column;
		return $this;
	}

}
