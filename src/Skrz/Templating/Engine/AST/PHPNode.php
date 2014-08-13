<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {php} ... {/php}, or anything other that is compiled into PHP code
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class PHPNode extends AbstractNode
{

	private $code;

	public function __construct($code)
	{
		$this->code = $code;
	}

	public function getCode()
	{
		return $this->code;
	}

}
