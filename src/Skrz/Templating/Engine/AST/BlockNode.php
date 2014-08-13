<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {block ...} ... {/block}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class BlockNode extends AbstractNode
{

	/** @var string */
	private $name;

	/** @var boolean */
	private $append;

	/** @var boolean */
	private $prepend;

	/** @var AbstractNode[] */
	private $body;

	/** @var string */
	private $fileName;

	/** @var int */
	private $row;

	/** @var int */
	private $column;

	public function __construct($name, $append, $prepend, array $body, $fileName, $row, $column)
	{
		$this->name = $name;
		$this->append = $append;
		$this->prepend = $prepend;
		$this->body = $body;
		$this->fileName = $fileName;
		$this->row = $row;
		$this->column = $column;
	}

	/**
	 * @return boolean
	 */
	public function getAppend()
	{
		return $this->append;
	}

	/**
	 * @return \Skrz\Templating\Engine\AST\AbstractNode[]
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @return int
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return boolean
	 */
	public function getPrepend()
	{
		return $this->prepend;
	}

	/**
	 * @return int
	 */
	public function getRow()
	{
		return $this->row;
	}

}
