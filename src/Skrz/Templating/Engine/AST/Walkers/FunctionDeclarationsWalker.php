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
 * AST walker to collect {function ...} declarations
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class FunctionDeclarationsWalker extends AbstractASTWalker
{

	/** @var FunctionDeclarationNode[] */
	private $declarations = [];

	/** @var boolean  whether function declaration is allowed in this place */
	private $allowed = true;

	/** @var string */
	private $nodeType = null;

	/**
	 * @return FunctionDeclarationNode[]
	 */
	public function getDeclarations()
	{
		return $this->declarations;
	}

	private function walkEachDisallow($body, $nodeType)
	{
		$this->allowed = false;
		$savedNodeType = $this->nodeType;
		$this->nodeType = $nodeType;
		$this->walkEach((array)$body);
		$this->nodeType = $savedNodeType;
		$this->allowed = true;
	}

	protected function walkAssignment(AssignmentNode $assignment)
	{
	}

	protected function walkCapture(CaptureNode $capture)
	{
		$this->walkEachDisallow($capture->getBody(), "capture");
	}

	protected function walkComment(CommentNode $comment)
	{
	}

	protected function walkEcho(EchoNode $echo)
	{
	}

	protected function walkFor(ForNode $for)
	{
		$this->walkEachDisallow($for->getBody(), "for");
		$this->walkEachDisallow($for->getElseBody(), "forelse");
	}

	protected function walkForeach(ForeachNode $foreach)
	{
		$this->walkEachDisallow($foreach->getBody(), "foreach");
		$this->walkEachDisallow($foreach->getElseBody(), "foreachelse");
	}

	protected function walkFunction(FunctionNode $function)
	{
	}

	protected function walkFunctionDeclaration(FunctionDeclarationNode $functionDeclaration)
	{
		$this->declarations[$functionDeclaration->getName()] = $functionDeclaration;
		$this->walkEachDisallow($functionDeclaration->getBody(), "function");
	}

	protected function walkIf(IfNode $if)
	{
		$this->walkEachDisallow($if->getBody(), "if");
		foreach ((array)$if->getElseifs() as $elseif) {
			/** @var IfNode $elseif */
			$this->walkEachDisallow($elseif->getBody(), "elseif");
		}
		$this->walkEachDisallow($if->getElseBody(), "else");
	}

	protected function walkInclude(IncludeNode $include)
	{
		$this->walk($include->getTemplate());
	}

	protected function walkModifier(ModifierNode $modifier)
	{
	}

	protected function walkPHP(PHPNode $php)
	{
	}

	protected function walkSection(SectionNode $section)
	{
		$this->walkEachDisallow($section->getBody(), "section");
	}

	protected function walkStrip(StripNode $strip)
	{
		$this->walkEachDisallow($strip->getBody(), "strip");
	}

	protected function walkTemplate(TemplateNode $template)
	{
		$this->walkEach($template->getStatements());
	}

	protected function walkText(TextNode $text)
	{
	}

	protected function walkBlock(BlockNode $block)
	{
		$this->walkEach($block->getBody());
	}

	protected function walkExpression(ExpressionNode $expression)
	{
	}

}
