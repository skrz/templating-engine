<?php
namespace Skrz\Templating\Engine\AST;

/**
 * Template top level node
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class TemplateNode extends AbstractNode
{

	/** @var \Skrz\Templating\Engine\ParserContext */
	private $context;

	/** @var array */
	private $statements;

	public function __construct($context, $statements)
	{
		$this->context = $context;
		$this->statements = $statements;
	}

	public function getContext()
	{
		return $this->context;
	}

	public function getStatements()
	{
		return $this->statements;
	}

}
