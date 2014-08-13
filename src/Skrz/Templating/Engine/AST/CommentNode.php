<?php
namespace Skrz\Templating\Engine\AST;

/**
 * {* ... *}
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class CommentNode extends AbstractNode
{

	/** @var string */
	private $comment;

	public function __construct($comment)
	{
		$this->comment = $comment;
	}

	public function getComment()
	{
		return $this->comment;
	}

}
