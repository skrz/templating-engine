<?php
namespace Skrz\Templating\Engine\AST;

/**
 * Free text
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class TextNode extends AbstractNode
{

	/** @var string */
	private $text;

	public function __construct($text)
	{
		$this->text = $text;
	}

	public function getText()
	{
		return $this->text;
	}

}
