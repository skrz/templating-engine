<?php
namespace Skrz\Templating\Engine\AST\Walkers;

use Skrz\Templating\Engine\AST\AbstractNode;
use Skrz\Templating\Engine\AST\AssignmentNode;
use Skrz\Templating\Engine\AST\BlockNode;
use Skrz\Templating\Engine\AST\CaptureNode;
use Skrz\Templating\Engine\AST\CommentNode;
use Skrz\Templating\Engine\AST\EchoNode;
use Skrz\Templating\Engine\AST\ExpressionNode;
use Skrz\Templating\Engine\AST\ForeachNode;
use Skrz\Templating\Engine\AST\ForNode;
use Skrz\Templating\Engine\AST\FunctionNode;
use Skrz\Templating\Engine\AST\IfNode;
use Skrz\Templating\Engine\AST\IncludeNode;
use Skrz\Templating\Engine\AST\ModifierNode;
use Skrz\Templating\Engine\AST\PHPNode;
use Skrz\Templating\Engine\AST\SectionNode;
use Skrz\Templating\Engine\AST\StripNode;
use Skrz\Templating\Engine\AST\TemplateNode;
use Skrz\Templating\Engine\AST\TextNode;

/**
 * An abstract syntax tree walker
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
abstract class AbstractASTWalker
{

	public function walk(AbstractNode $node)
	{
		$walkerName = get_class($node);

		// remove namespace
		$pos = strrpos($walkerName, "\\");
		if ($pos !== false) {
			$walkerName = substr($walkerName, $pos + 1);
		}

		// remove Node end
		$walkerName = substr($walkerName, 0, -4);
		$method = "walk{$walkerName}";

		return $this->$method($node);
	}

	/**
	 * @param AbstractNode[] $nodes
	 * @return AbstractNode[]
	 */
	public function walkEach(array $nodes)
	{
		$ret = array();
		foreach ($nodes as $k => $node) {
			$ret[$k] = $this->walk($node);
		}
		return $ret;
	}

	abstract protected function walkAssignment(AssignmentNode $assignment);

	abstract protected function walkCapture(CaptureNode $capture);

	abstract protected function walkComment(CommentNode $comment);

	abstract protected function walkEcho(EchoNode $echo);

	abstract protected function walkFor(ForNode $for);

	abstract protected function walkForeach(ForeachNode $foreach);

	abstract protected function walkFunction(FunctionNode $function);

	abstract protected function walkIf(IfNode $if);

	abstract protected function walkInclude(IncludeNode $include);

	abstract protected function walkModifier(ModifierNode $modifier);

	abstract protected function walkPHP(PHPNode $php);

	abstract protected function walkSection(SectionNode $section);

	abstract protected function walkStrip(StripNode $strip);

	abstract protected function walkTemplate(TemplateNode $template);

	abstract protected function walkText(TextNode $text);

	abstract protected function walkBlock(BlockNode $block);

	abstract protected function walkExpression(ExpressionNode $expression);

}
