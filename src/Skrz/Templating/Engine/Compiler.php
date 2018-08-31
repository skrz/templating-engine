<?php
namespace Skrz\Templating\Engine;

use Skrz\Templating\Engine\AST\AbstractNode;
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
use Skrz\Templating\Engine\AST\Walkers\AbstractASTWalker;
use Skrz\Templating\Engine\AST\Walkers\FunctionDeclarationsWalker;
use Skrz\Templating\Engine\AST\Walkers\VariableNamesWalker;
use Skrz\Templating\Engine\VO\StatementAndExpressionVO;

/**
 * Compiles parsed template into an PHP file
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class Compiler extends AbstractASTWalker
{

	const GET_TEMPLATE_VARS_REGEX = "/\\\$template->getTemplateVars\\(([^)]+)\\)/";

	const ASSIGN_REGEX = "/\\\$template->assign\\(([^,]+),([^;]+)/";

	/** @var CompilerContext */
	private $context;

	private $counter = 0;

	public function __construct(CompilerContext $context)
	{
		$this->context = $context;
	}

	/**
	 * @return CompilerContext
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @return string
	 * @throws CompilerException
	 */
	public function compile()
	{
		$namespace = null;
		$className = trim($this->context->getFullyQualifiedClassName(), "\\");
		$pos = strrpos($className, "\\");
		if ($pos !== false) {
			$namespace = substr($className, 0, $pos);
			$className = substr($className, $pos + 1);
		}

		// prelude
		$ret = "";
		$ret .= "<?php\n";
		if ($namespace) {
			$ret .= "namespace $namespace;\n";
			$ret .= "\n";
		}
		foreach ($this->context->getUses() as $alias => $fqn) {
			$ret .= "use $fqn as $alias;\n";
		}
		$ret .= "\n";
		$ret .= "class $className implements \\Skrz\\Templating\\Engine\\TemplateInterface {\n";
		$ret .= "\n";

		$ret .= "public function __invoke(array \$____) {\n";
		$ret .= "\t\$this->render(\$____);\n";
		$ret .= "}\n";
		$ret .= "\n";

		$ret .= "public function fetch(array \$____) {\n";
		$ret .= "\tob_start();\n";
		$ret .= "\t\$this->render(\$____);\n";
		$ret .= "\treturn ob_get_clean();\n";
		$ret .= "}\n";
		$ret .= "\n";

		$ret .= "public function fetchFunction(\$functionName, array \$____) {\n";
		$ret .= "\tob_start();\n";
		$ret .= "\t\$this->{'render' . \$functionName}(\$____);\n";
		$ret .= "\treturn ob_get_clean();\n";
		$ret .= "}\n";
		$ret .= "\n";

		$ret .= "public function renderFunction(\$functionName, array \$____) {\n";
		$ret .= "\t\$this->{'render' . \$functionName}(\$____);\n";
		$ret .= "}\n";
		$ret .= "\n";

		// find function declarations
		$functionDeclarationFinder = new FunctionDeclarationsWalker();
		$functionDeclarationFinder->walk($this->context->getTemplate());

		// function declarations have to be registered first and compiled into render functions,
		// otherwise recursive calls would not be possible
		$ret .= "private \$functions = array(";
		foreach ($functionDeclarationFinder->getDeclarations() as $declaration) {
			$ret .= var_export($declaration->getName(), true) . "=>true,";
			$this->context->addFunction($declaration->getName(), function (Compiler $compiler, FunctionNode $node) use ($declaration) {
				$statement = "";
				$args = "array(";
				foreach ($node->getArguments() as $name => $expression) {
					$statementAndExpression = $compiler->walkExpression($expression);
					$statement .= $statementAndExpression->getStatement();
					$args .= var_export($name, true) . '=>' . $statementAndExpression->getExpression() . ",";
				}
				$args .= ")";
				return new FunctionCompilerResult($statement . "\$this->" . $this->getRenderName($declaration->getName()) . "(" . $args . " + \$____);");
			});
		}
		$ret .= ");\n\n";

		$ret .= "public function hasFunction(\$functionName) {\n";
		$ret .= "\treturn isset(\$this->functions[\$functionName]);\n";
		$ret .= "}\n\n";

		foreach ($functionDeclarationFinder->getDeclarations() as $declaration) {
			$ret .= $this->compileRender($declaration->getName(), $declaration->getBody(), $declaration->getDefaultArguments());
		}

		// compile main render
		$ret .= $this->compileRender("", array($this->context->getTemplate()));

		// end
		$ret .= "\n";
		$ret .= "}\n";

		return $ret;
	}

	private function getRenderName($name)
	{
		return "render" . ucfirst($name);
	}

	/**
	 * @param string $name
	 * @param AbstractNode[] $body
	 * @param ExpressionNode[] $defaults
	 * @return string
	 * @throws CompilerException
	 */
	private function compileRender($name, $body, $defaults = array())
	{
		$ret = "public function " . $this->getRenderName($name) . "(array \$____) {\n";
		$ret .= "\$____ = \$____;\n"; // FIXME: why?

		if ($this->context->getAppPath() && $this->context->getOutputFileName()) {
			$appPath = realpath($this->context->getAppPath());

			$templateFileName = $this->context->getParserContext()->getFileName();
			if (strncmp($templateFileName, $appPath, strlen($appPath)) !== 0) {
				throw new CompilerException(
					$this->context,
					"Template '{$templateFileName}' is outside of app path '{$appPath}'."
				);
			}

			$outputFileName = $this->context->getOutputFileName();
			if (strncmp($outputFileName, $appPath, strlen($appPath)) !== 0) {
				throw new CompilerException(
					$this->context,
					"Output file name '{$outputFileName}' is outside of app path '{$appPath}'."
				);
			}

			$templateFileName = explode("/", substr($templateFileName, strlen($appPath) + 1));
			$outputFileName = explode("/", substr($outputFileName, strlen($appPath) + 1));
			array_pop($templateFileName);
			array_pop($outputFileName);

			while (current($templateFileName) === current($outputFileName)) {
				array_shift($templateFileName);
				array_shift($outputFileName);
			}

			$relative = var_export(str_repeat("/..", count($outputFileName)) . "/" . implode("/", $templateFileName), true);
			$dirName = "__DIR__ . $relative";

		} else {
			$dirName = var_export(dirname($this->context->getParserContext()->getFileName()), true);
		}

		$ret .= "\$smarty = (object) array('current_dir' => {$dirName}, 'get' => \$_GET, 'post' => \$_POST, 'cookie' => \$_COOKIE, 'now' => time(), 'capture' => (object) array(), 'foreach' => (object) array(), 'section' => (object) array());";

		// prepare variable names
		$finder = new VariableNamesWalker;
		$variableNames = array_keys($finder->walk($this->context->getTemplate()));

		foreach ($variableNames as $variableName) {
			if ($variableName === "smarty") {
				continue;
			}

			$ret .= "if(isset(\$____['$variableName'])){";
			$ret .= "\$$variableName=\$____['$variableName'];";
			$ret .= "}else{";
			if (isset($defaults[$variableName])) {
				$statementAndExpression = $this->walkExpression($defaults[$variableName]);
				$ret .= $statementAndExpression->getStatement();
				$ret .= "\$$variableName={$statementAndExpression->getExpression()};";
			} else {
				$ret .= "\$$variableName=null;";
			}
			$ret .= "}\n";
		}

		// main
		$ret .= $this->context->getBeforeCode();
		$ret .= "\n";
		$ret .= implode("", $this->walkEach($body));
		$ret .= "\n";
		$ret .= $this->context->getAfterCode();

		$ret .= "\n\n";
		$ret .= "}\n";

		return $ret;
	}

	protected function walkTemplate(TemplateNode $template)
	{
		$ret = "";

		foreach ($template->getStatements() as $statement) {
			$ret .= $this->walk($statement);
		}

		return $ret;
	}

	protected function walkAssignment(AssignmentNode $assignment)
	{
		/** @var StatementAndExpressionVO $expressionSE */
		$expressionSE = $this->walk($assignment->getExpression());
		return
			$expressionSE->getStatement() .
			"\${$assignment->getVariableName()}{$assignment->getPath()}={$expressionSE->getExpression()};";
	}

	protected function walkCapture(CaptureNode $capture)
	{
		$ret = "ob_start();";

		foreach ($capture->getBody() as $statement) {
			$ret .= $this->walk($statement);
		}

		$resultSym = $this->gensym();

		$ret .= "{$resultSym} = ob_get_clean();";

		if ($capture->getName() !== null) {
			$ret .= "\$smarty->capture->{$capture->getName()} = {$resultSym};";
		}

		if ($capture->getAssign() !== null) {
			$ret .= "\${$capture->getAssign()} = {$resultSym};";
		}

		if ($capture->getAppend() !== null) {
			$ret .= "if (!isset(\${$capture->getAppend()})) { \${$capture->getAppend()} = array(); }";
			$ret .= "\${$capture->getAppend()}[] = {$resultSym};";
		}

		return $ret;
	}

	protected function walkComment(CommentNode $comment)
	{
		return "/* {$comment->getComment()} */";
	}

	protected function walkEcho(EchoNode $echo)
	{
		/** @var StatementAndExpressionVO $statementAndExpressionVO */
		$statementAndExpressionVO = $this->walk($echo->getExpression());
		if ($echo->getNoFilter()) {
			return $statementAndExpressionVO->getStatement() . "echo " . $statementAndExpressionVO->getExpression() . ";";
		} else {
			return
				$statementAndExpressionVO->getStatement() .
				"echo htmlspecialchars(" . $statementAndExpressionVO->getExpression() . ", ENT_QUOTES);";
		}
	}

	protected function walkFor(ForNode $for)
	{
		$ret = "for (\${$for->getVariableName()} = ";
		$ret .= $this->walk($for->getFrom()) . ", ";
		$toSym = $this->gensym();
		$ret .= "$toSym = " . $this->walk($for->getTo()) . ", ";
		$iterationsSym = $this->gensym();
		$ret .= "$iterationsSym = 0; ";
		$ret .= "\${$for->getVariableName()} <= $toSym; ";
		$ret .= "\${$for->getVariableName()} += " . ($for->getStep() === null ? "1" : $this->walk($for->getStep()));
		$ret .= ", ++$iterationsSym) {" . implode("", $this->walkEach((array)$for->getBody())) . "}";
		$ret .= "if ($iterationsSym < 1) {" . implode("", $this->walkEach((array)$for->getElseBody())) . "}";
		return $ret;
	}

	protected function walkForeach(ForeachNode $foreach)
	{
		$exprSym = $this->gensym();

		$ret = "";
		$ret .= "{$exprSym} = " . $this->walk($foreach->getExpression()) . ";";
		if ($foreach->hasElseBody()) {
			$ret .= "{$exprSym}else = true;";
		}

		if ($foreach->hasName()) {
			$name = $foreach->getName();
		} else {
			$name = $foreach->getValueVariableName();
		}

		$ret .= "\$smarty->foreach->{$name} = (object) array('index' => -1, 'iteration' => 0, 'first' => null, 'last' => null, 'total' => count({$exprSym}));";

		$ret .= "foreach ({$exprSym} as ";
		if ($foreach->getKeyVariableName()) {
			$ret .= "\${$foreach->getKeyVariableName()} => ";
		}
		$ret .= "\${$foreach->getValueVariableName()}) {";

		$ret .= "{$exprSym}else = false;";

		$ret .= "++\$smarty->foreach->{$name}->index;";
		$ret .= "++\$smarty->foreach->{$name}->iteration;";
		$ret .= "\$smarty->foreach->{$name}->first = \$smarty->foreach->{$name}->index == 0;";
		$ret .= "\$smarty->foreach->{$name}->last = \$smarty->foreach->{$name}->iteration == \$smarty->foreach->{$name}->total;";

		$ret .= implode("", $this->walkEach((array)$foreach->getBody()));
		$ret .= "}";

		if ($foreach->hasElseBody()) {
			$ret .= "if({$exprSym}else){" . implode("", $this->walkEach($foreach->getElseBody())) . "}";
		}

		return $ret;
	}

	protected function walkFunction(FunctionNode $function)
	{
		$functionCompiler = $this->context->getFunction($function->getName());

		if ($functionCompiler === null) {
			throw new CompilerException(
				$this->context,
				"Function `{$function->getName()}` not registered in compiler context. Template in " .
				$function->getFileName() . " @ " . $function->getRow() . ":" . $function->getColumn()
			);
		}

		$functionResult = $functionCompiler($this, $function);

		if (!($functionResult instanceof FunctionCompilerResult)) {
			throw new CompilerException(
				$this->context,
				"Function `{$function->getName()}` did not return " . __NAMESPACE__ . "\\FunctionCompilerResult."
			);
		}

		return $functionResult->getStatement();
	}

	protected function walkFunctionDeclaration(FunctionDeclarationNode $functionDeclaration)
	{
		return "";
	}

	protected function walkIf(IfNode $if)
	{
		$ret = "";
		$ret .= "if(" . $this->walk($if->getCondition()) . "){";
		$ret .= implode("", $this->walkEach($if->getBody()));
		$ret .= "}";

		foreach ((array)$if->getElseifs() as $elseif) {
			/** @var IfNode $elseif */
			$ret .= "elseif(" . $this->walk($elseif->getCondition()) . "){";
			$ret .= implode("", $this->walkEach($elseif->getBody()));
			$ret .= "}";
		}

		if ($if->hasElseBody()) {
			$ret .= "else{" . implode("", $this->walkEach($if->getElseBody())) . "}";
		}

		return $ret;
	}

	protected function walkInclude(IncludeNode $include)
	{
		$ret = "";
		$syms = array();

		if ($include->hasAssign()) {
			$ret .= "ob_start();";
		}

		foreach ($include->getLocals() as $name => $node) {
			$sym = $this->gensym();
			$syms[$sym] = $name;
			$ret .= "{$sym} = \$$name; \$$name = " . $this->walk($node) . ";";
		}

		if ($include->getScope() !== IncludeNode::SCOPE_PARENT) {
			foreach ($include->getTemplate()->getContext()->getVariableNames() as $name) {
				$sym = $this->gensym();
				$syms[$sym] = $name;
				$ret .= "{$sym} = \$$name;";
			}
		}

		$smartySym = $this->gensym();
		$ret .= "{$smartySym} = \$smarty;";
		$syms[$smartySym] = "smarty";
		$ret .= "\$smarty = (object) array('get' => \$_GET, 'post' => \$_POST, 'cookie' => \$_COOKIE, 'now' => {$smartySym}->now, 'capture' => (object) array(), 'foreach' => (object) array(), 'section' => (object) array());";

		$ret .= $this->walk($include->getTemplate());

		foreach (array_reverse($syms, true) as $sym => $name) {
			$ret .= "\$$name = {$sym};";
		}

		if ($include->hasAssign()) {
			$ret .= "\${$include->getAssign()} = ob_get_clean();";
		}

		return $ret;
	}

	protected function walkModifier(ModifierNode $modifier)
	{
		throw new TemplateException("Just don't throw me this shit!");
	}

	protected function walkPHP(PHPNode $php)
	{
		$code = $php->getCode();

		$code = preg_replace_callback(self::GET_TEMPLATE_VARS_REGEX, function ($match) {
			$varname = trim($match[1], "\"'() \t\r\n");
			return "\${$varname}"; //" /* {$match[0]} */";
		}, $code);

		$code = preg_replace_callback(self::ASSIGN_REGEX, function ($match) {
			$varname = trim($match[1], "() \t\r\n");
			$expression = $match[2];
			// remove )
			$expression = substr($expression, 0, -1);
			return "\${{$varname}} = {$expression}"; // " /* {$match[0]} */";
		}, $code);

		return $code;
	}

	protected function walkSection(SectionNode $section)
	{
		$exprSym = $this->gensym();

		$ret = "{$exprSym} = " . $this->walk($section->getExpression()) . ";";
		$ret .= "\$smarty->section->{$section->getName()} = (object) array('index' => -1, 'iteration' => 0, 'first' => null, 'last' => null, 'total' => (int) {$exprSym});";

		$ret .= "while (\$smarty->section->{$section->getName()}->iteration < \$smarty->section->{$section->getName()}->total) {";

		$ret .= "++\$smarty->section->{$section->getName()}->index;";
		$ret .= "++\$smarty->section->{$section->getName()}->iteration;";
		$ret .= "\$smarty->section->{$section->getName()}->first = \$smarty->section->{$section->getName()}->index == 0;";
		$ret .= "\$smarty->section->{$section->getName()}->last = \$smarty->section->{$section->getName()}->iteration == \$smarty->section->{$section->getName()}->total;";

		$ret .= implode("", $this->walkEach($section->getBody()));

		$ret .= "}";

		return $ret;
	}

	protected function walkStrip(StripNode $strip)
	{
		$sym = $this->gensym();
		$vsym = $this->gensym();
		$echosym = $this->gensym();
		$ret = "";

		$ret .= "ob_start();";
		$ret .= implode("", $this->walkEach($strip->getBody()));
		$ret .= "{$sym} = ob_get_clean();";
		$ret .= "{$sym} = preg_split('/\\r\\n|\\r|\\n/', {$sym});";
		$ret .= "{$echosym} = '';";
		$ret .= "foreach ({$sym} as {$vsym}) { {$echosym} .= trim({$vsym}); }";
		$ret .= "echo {$echosym};";

		return $ret;
	}

	protected function walkText(TextNode $text)
	{
		return "?>" . $text->getText() . "<?php ";
	}

	protected function walkBlock(BlockNode $block)
	{
		return implode("", $this->walkEach($block->getBody()));
	}

	protected function walkExpression(ExpressionNode $expression)
	{
		$ret = $this->walk($expression->getExpression());
		$statements = array();

		foreach ($expression->getModifiers() as $modifier) {
			$modifierCompiler = $this->context->getModifier($modifier->getName());
			if ($modifierCompiler === null) {
				if (!function_exists($modifier->getName())) {
					throw new CompilerException(
						$this->context,
						"Modifier `{$modifier->getName()}` not registered in compiler context. Template in " .
						$modifier->getFileName() . " @ " . $modifier->getRow() . ":" . $modifier->getColumn()
					);
				} else {
					$modifierCompiler = function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
						return new ModifierCompilerResult(
							$modifier->getName() . "({$expressionCode} " .
							($modifier->hasArgument(0) ? ", " : "") .
							implode(", ", $compiler->walkEach($modifier->getArguments())) .
							")"
						);
					};
				}
			}

			$modifierResult = $modifierCompiler($this, $modifier, $ret);

			if (!($modifierResult instanceof ModifierCompilerResult)) {
				throw new CompilerException(
					$this->context,
					"Modifier `{$modifier->getName()}` did not return " . __NAMESPACE__ . "\\ModifierCompilerResult."
				);
			}

			$statements[] = $modifierResult->getStatement();
			$ret = $modifierResult->getExpression();
		}

		return new StatementAndExpressionVO(implode("", $statements), $ret);
	}

	public function gensym()
	{
		return "\$____" . $this->counter++;
	}

}
