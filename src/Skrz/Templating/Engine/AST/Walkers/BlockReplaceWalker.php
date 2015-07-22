<?php
namespace Skrz\Templating\Engine\AST\Walkers;

use Skrz\Templating\Engine\AST\AssignmentNode;
use Skrz\Templating\Engine\AST\BlockNode;
use Skrz\Templating\Engine\AST\CaptureNode;
use Skrz\Templating\Engine\AST\CommentNode;
use Skrz\Templating\Engine\AST\EchoNode;
use Skrz\Templating\Engine\AST\ExpressionNode;
use Skrz\Templating\Engine\AST\ForeachNode;
use Skrz\Templating\Engine\AST\ForNode;
use Skrz\Templating\Engine\AST\FunctionDeclarationNode;
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
 * AST walker used by {extend ...} to replace parent's block with the child's
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class BlockReplaceWalker extends AbstractASTWalker
{

	/** @var BlockNode[] */
	private $replacingBlocks = array();

	/**
	 * @param TemplateNode $template
	 * @param BlockNode[] $replacingBlocks
	 */
	public function replaceBlocks(TemplateNode $template, array $replacingBlocks)
	{
		$this->replacingBlocks = array();
		foreach ($replacingBlocks as $block) {
			$this->replacingBlocks[$block->getName()] = $block;
		}

		return $this->walk($template);
	}

	protected function walkAssignment(AssignmentNode $assignment)
	{
		return $assignment;
	}

	protected function walkCapture(CaptureNode $capture)
	{
		return new CaptureNode(
			$capture->getName(),
			$capture->getAssign(),
			$capture->getAppend(),
			$this->walkEach($capture->getBody())
		);
	}

	protected function walkComment(CommentNode $comment)
	{
		return $comment;
	}

	protected function walkEcho(EchoNode $echo)
	{
		return $echo;
	}

	protected function walkFor(ForNode $for)
	{
		return new ForNode(
			$for->getVariableName(),
			$for->getFrom(),
			$for->getTo(),
			$for->getStep(),
			$this->walkEach($for->getBody()),
			$this->walkEach($for->getElseBody())
		);
	}

	protected function walkForeach(ForeachNode $foreach)
	{
		return new ForeachNode(
			$foreach->getExpression(),
			$foreach->getName(),
			$foreach->getKeyVariableName(),
			$foreach->getValueVariableName(),
			$this->walkEach($foreach->getBody()),
			$foreach->hasElseBody() ? $this->walkEach($foreach->getElseBody()) : null
		);
	}

	protected function walkFunction(FunctionNode $function)
	{
		return $function;
	}

	protected function walkFunctionDeclaration(FunctionDeclarationNode $functionDeclaration)
	{
		return $functionDeclaration;
	}

	protected function walkIf(IfNode $if)
	{
		return new IfNode(
			$this->walk($if->getCondition()),
			$this->walkEach($if->getBody()),
			$this->walkEach($if->getElseifs()),
			$if->hasElseBody() ? $this->walkEach($if->getElseBody()) : null
		);
	}

	protected function walkInclude(IncludeNode $include)
	{
		return $include;
	}

	protected function walkModifier(ModifierNode $modifier)
	{
		return $modifier;
	}

	protected function walkPHP(PHPNode $php)
	{
		return $php;
	}

	protected function walkSection(SectionNode $section)
	{
		return new SectionNode(
			$section->getName(),
			$section->getExpression(),
			$this->walkEach($section->getBody())
		);
	}

	protected function walkStrip(StripNode $strip)
	{
		return new StripNode(
			$this->walkEach($strip->getBody())
		);
	}

	protected function walkTemplate(TemplateNode $template)
	{
		return new TemplateNode(
			$template->getContext(),
			$this->walkEach($template->getStatements())
		);
	}

	protected function walkText(TextNode $text)
	{
		return $text;
	}

	protected function walkBlock(BlockNode $block)
	{
		if (!isset($this->replacingBlocks[$block->getName()])) {
			return new BlockNode(
				$block->getName(),
				null,
				null,
				$this->walkEach($block->getBody()),
				$block->getFileName(),
				$block->getRow(),
				$block->getColumn()
			);

		} else {
			$replacingBlock = $this->replacingBlocks[$block->getName()];
			if ($replacingBlock->getAppend()) {
				return new BlockNode(
					$block->getName(),
					null,
					null,
					array_merge($this->walkEach($replacingBlock->getBody()), $this->walkEach($block->getBody())),
					$replacingBlock->getFileName(),
					$replacingBlock->getRow(),
					$replacingBlock->getColumn()
				);

			} elseif ($replacingBlock->getPrepend()) {
				return new BlockNode(
					$block->getName(),
					null,
					null,
					array_merge($this->walkEach($block->getBody()), $this->walkEach($replacingBlock->getBody())),
					$replacingBlock->getFileName(),
					$replacingBlock->getRow(),
					$replacingBlock->getColumn()
				);

			} else {
				return new BlockNode(
					$block->getName(),
					null,
					null,
					$this->walkEach($replacingBlock->getBody()),
					$replacingBlock->getFileName(),
					$replacingBlock->getRow(),
					$replacingBlock->getColumn()
				);
			}
		}
	}

	protected function walkExpression(ExpressionNode $expression)
	{
		return $expression;
	}

}
