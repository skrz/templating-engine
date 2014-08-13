<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {include ...}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class IncludeNode extends AbstractNode
{

	const SCOPE_PARENT = "parent";

	/** @var string */
	private $file;

	/** @var string */
	private $assign;

	/** @var string */
	private $scope;

	/** @var array of string => PHPNode */
	private $locals;

	/** @var TemplateNode */
	private $template;

	public function __construct($file, $assign, $scope, array $locals, TemplateNode $template)
	{
		$this->file = $file;
		$this->assign = $assign;
		$this->scope = $scope;
		$this->locals = $locals;
		$this->template = $template;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function getAssign()
	{
		return $this->assign;
	}

	public function hasAssign()
	{
		return $this->assign !== null;
	}

	public function getScope()
	{
		return $this->scope;
	}

	public function getLocals()
	{
		return $this->locals;
	}

	public function getTemplate()
	{
		return $this->template;
	}

}
