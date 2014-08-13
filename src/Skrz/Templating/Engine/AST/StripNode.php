<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {strip} ... {/strip}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class StripNode extends AbstractNode
{

	/** @var AbstractNode[] */
	private $body;

	public function __construct($body)
	{
		$this->body = $body;
	}

	public function getBody()
	{
		return $this->body;
	}

}
