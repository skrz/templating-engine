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
 * AST walker that collects are variables names used in a template
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class VariableNamesWalker extends AbstractASTWalker
{

	protected function walkAssignment(AssignmentNode $assignment)
	{
		return array();
	}

	protected function walkCapture(CaptureNode $capture)
	{
		$variableNames = array();

		foreach ($capture->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		return $variableNames;
	}

	protected function walkComment(CommentNode $comment)
	{
		return array();
	}

	protected function walkEcho(EchoNode $echo)
	{
		return array();
	}

	protected function walkFor(ForNode $for)
	{
		$variableNames = array();

		foreach ($for->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		if ($for->getElseBody() !== null) {
			foreach ($for->getElseBody() as $statement) {
				foreach ($this->walk($statement) as $name => $_) {
					$variableNames[$name] = true;
				}
			}
		}

		return $variableNames;
	}

	protected function walkForeach(ForeachNode $foreach)
	{
		$variableNames = array();

		foreach ($foreach->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		if ($foreach->hasElseBody()) {
			foreach ($foreach->getElseBody() as $statement) {
				foreach ($this->walk($statement) as $name => $_) {
					$variableNames[$name] = true;
				}
			}
		}

		return $variableNames;
	}

	protected function walkFunction(FunctionNode $function)
	{
		return array();
	}

	protected function walkIf(IfNode $if)
	{
		$variableNames = array();

		foreach ($if->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		foreach ($if->getElseifs() as $elseif) {
			foreach ($elseif->getBody() as $statement) {
				foreach ($this->walk($statement) as $name => $_) {
					$variableNames[$name] = true;
				}
			}
		}

		foreach ($if->getElseBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		return $variableNames;
	}

	protected function walkInclude(IncludeNode $include)
	{
		return $this->walk($include->getTemplate());
	}

	protected function walkModifier(ModifierNode $modifier)
	{
		return array();
	}

	protected function walkPHP(PHPNode $php)
	{
		return array();
	}

	protected function walkSection(SectionNode $section)
	{
		$variableNames = array();

		foreach ($section->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		return $variableNames;
	}

	protected function walkStrip(StripNode $strip)
	{
		$variableNames = array();

		foreach ($strip->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		return $variableNames;
	}

	protected function walkTemplate(TemplateNode $template)
	{
		$variableNames = array();

		foreach ($template->getContext()->getVariableNames() as $name) {
			$variableNames[$name] = true;
		}

		foreach ($template->getStatements() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		return $variableNames;
	}

	protected function walkText(TextNode $text)
	{
		return array();
	}

	protected function walkBlock(BlockNode $block)
	{
		$variableNames = array();

		foreach ($block->getBody() as $statement) {
			foreach ($this->walk($statement) as $name => $_) {
				$variableNames[$name] = true;
			}
		}

		return $variableNames;
	}

	protected function walkExpression(ExpressionNode $expression)
	{
		return array();
	}
}
