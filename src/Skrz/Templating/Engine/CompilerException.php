<?php
namespace Skrz\Templating\Engine;

class CompilerException extends TemplateException
{

	/** @var CompilerContext */
	private $context;

	public function __construct(CompilerContext $context, $reason)
	{
		$this->context = $context;
		parent::__construct($reason);
	}

	public function getContext()
	{
		return $this->context;
	}

}
