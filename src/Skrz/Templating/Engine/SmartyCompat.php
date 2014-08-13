<?php
namespace Skrz\Templating\Engine;

use Skrz\Templating\Engine\AST\ModifierNode;
use Skrz\Templating\Engine\AST\PHPNode;

/**
 * Somewhat compatible implementation to Smarty, could be used as drop-in replacement for Smarty class
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class SmartyCompat
{

	/** @var ParserContext */
	private $parserContext;

	/** @var CompilerContext */
	private $compilerContext;

	/** @var string */
	private $classDirectory;

	/** @var string */
	private $cacheDirectory;

	/** @var bool */
	private $cachingEnabled;

	/** @var bool */
	private $useSubDirs;

	/** @var int */
	private $dirPerms = 0777;

	/** @var bool */
	private $compileCheck;

	/** @var bool */
	private $allowPHPTag;

	/** @var int */
	private $errorReporting;

	/** @var string */
	private $debuggingCtrl;

	/** @var callable[] */
	private $outputFilters = array();

	/** @var mixed[] template local variables */
	private $locals = array();

	public function __construct()
	{
		$this->parserContext = new ParserContext;
		$this->compilerContext = new CompilerContext;
		$this->compilerContext->setParserContext($this->parserContext);
		static::prepareModifiers($this->compilerContext);
		static::prepareFunctions($this->compilerContext);
	}

	public function setNamespace($namespace)
	{
		$this->compilerContext->setNamespace($namespace);
		return $this;
	}

//	public function setUse($use)
//	{
//		$this->compilerContext->setUse($use);
//		return $this;
//	}

	public function addModifier($name, $callback)
	{
		$this->compilerContext->addModifier($name, $callback);
	}

	public function addFunction($name, $callback)
	{
		$this->compilerContext->addFunction($name, $callback);
	}

	public function __get($propertyName)
	{
		if (method_exists($this, $methodName = "get" . str_replace("_", "", $propertyName))) {
			return $this->$methodName();
		}
	}

	public function __set($propertyName, $value)
	{
		if (method_exists($this, $methodName = "set" . str_replace("_", "", $propertyName))) {
			return $this->$methodName($value);
		}
	}

	public function getTemplateDir()
	{
		return !is_null($this->parserContext->getBaseDirectory())
			? array($this->parserContext->getBaseDirectory() . "/")
			: null;
	}

	public function setTemplateDir($templateDir)
	{
		$this->parserContext->setBaseDirectory(rtrim($templateDir, "/"));
		return $this;
	}

	public function getCompileDir()
	{
		return !is_null($this->classDirectory)
			? $this->classDirectory . "/"
			: null;
	}

	public function setCompileDir($compileDir)
	{
		$this->classDirectory = rtrim($compileDir, "/");
		return $this;
	}

	public function getCacheDir()
	{
		return !is_null($this->cacheDirectory)
			? $this->cacheDirectory . "/"
			: null;
	}

	public function setCacheDir($cacheDir)
	{
		$this->cacheDirectory = rtrim($cacheDir, "/");
	}

	public function getCaching()
	{
		return $this->cachingEnabled;
	}

	public function setCaching($caching)
	{
		$this->cachingEnabled = $caching;
	}

	public function getUseSubDirs()
	{
		return $this->useSubDirs;
	}

	public function setUseSubDirs($useSubDirs)
	{
		$this->useSubDirs = $useSubDirs;
	}

	public function getDirPerms()
	{
		return $this->dirPerms;
	}

	public function setDirPerms($dirPerms)
	{
		$this->dirPerms = $dirPerms;
	}

	public function getCompileCheck()
	{
		return $this->compileCheck;
	}

	public function setCompileCheck($compileCheck)
	{
		$this->compileCheck = $compileCheck;
	}

	public function getAllowPHPTag()
	{
		return $this->allowPHPTag;
	}

	public function setAllowPHPTag($allowPHPTag)
	{
		$this->allowPHPTag = $allowPHPTag;
	}

	public function getErrorReporting()
	{
		return $this->errorReporting;
	}

	public function setErrorReporting($errorReporting)
	{
		$this->errorReporting = $errorReporting;
	}

	public function getDebuggingCtrl()
	{
		return $this->debuggingCtrl;
	}

	public function setDebuggingCtrl($debuggingCtrl)
	{
		$this->debuggingCtrl = $debuggingCtrl;
	}

	public function loadFilter($type, $name)
	{
		switch ($type) {
			case "output":
				switch ($name) {
					case "trim":
						$this->outputFilters[] = function ($unfileterdOutput) {
							return trim($unfileterdOutput);
						};
						break;

					default:
						throw new TemplateException("Output filter `$name` is not supported.");
				}
				break;

			default:
				throw new TemplateException("Filter of type `$type` is not supported.");
		}
	}

	public function assign($name, $value)
	{
		$this->locals[$name] = $value;
		return $this;
	}

	public function display($templateFile)
	{
		echo $this->fetch($templateFile);
	}

	public function fetch($templateFile)
	{
		if (strncmp($templateFile, "string:", strlen("string:")) === 0) {
			$this->parserContext->setString(substr($templateFile, strlen("string:")));
			$this->compilerContext->setClassName("Template_" . md5($this->parserContext->getString()));

		} else {
			if (
				!is_null($this->parserContext->getBaseDirectory()) &&
				strncmp(
					$templateFile,
					$this->parserContext->getBaseDirectory(),
					strlen($this->parserContext->getBaseDirectory())
				) === 0
			) {
				$templateFile = substr($templateFile, strlen($this->parserContext->getBaseDirectory()) + 1);
			}

			// must be set before CompilerContext::getFullyQualifiedClassName() is called
			// FIXME: uncouple this
			$this->parserContext->setFile($templateFile);
		}

		$className = $this->compilerContext->getFullyQualifiedClassName();

		if (!class_exists($className)) {
			$saveFileName = $this->classDirectory . "/" . str_replace("\\", "/", trim($className, "\\") . ".php");

			if (file_exists($saveFileName)) {
				require_once $saveFileName;

			} else {
				$oldUmask = umask(0);

				$compiledTemplate = $this->compilerContext->setTemplate($this->parserContext->parse())->compile();

				if (!is_dir(dirname($saveFileName))) {
					@mkdir(dirname($saveFileName), $this->dirPerms, true);
				}

				file_put_contents($saveFileName, $compiledTemplate);
				@chmod($saveFileName, $this->dirPerms);

				umask($oldUmask);

				require_once $saveFileName;
			}
		}

		$templateInstance = new $className;
		ob_start();
		$templateInstance($this->locals);
		$output = ob_get_clean();

		foreach ($this->outputFilters as $filter) {
			$output = $filter($output);
		}

		return $output;
	}

	public function registerResource($name, $resource)
	{
		// do nothing
	}

	/**
	 * Prepare some of default smarty modifiers
	 */
	public static function prepareModifiers(CompilerContext $compilerContext)
	{
		$compilerContext->addModifier("truncate", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			return new ModifierCompilerResult(
				"\\Skrz\\Templating\\Engine\\Helpers::truncate({$expressionCode}" .
				($modifier->hasArgument(0)
					?
					"," .
					implode(",", array_map(function (PHPNode $php) {
						return $php->getCode();
					}, $modifier->getArguments()))
					: ""
				) .
				")"
			);
		});

		$compilerContext->addModifier("strip_tags", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			if (!$modifier->hasArgument(0) || $modifier->hasArgument(0) && strcasecmp($modifier->getArgument(0)->getCode(), "true") === 0) {
				return new ModifierCompilerResult("preg_replace('/<[^>]*?>/', ' ', {$expressionCode})");
			} else {
				return new ModifierCompilerResult("strip_tags({$expressionCode})");
			}
		});

		$compilerContext->addModifier("escape", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			$type = strtolower(trim($modifier->hasArgument(0) ? $modifier->getArgument(0)->getCode() : "html", "\"'() \t\r\n"));

			switch ($type) {
				case "html":
					return new ModifierCompilerResult("htmlspecialchars({$expressionCode}, ENT_QUOTES)");

				case "url":
					return new ModifierCompilerResult("rawurlencode({$expressionCode})");

				case "javascript":
					return new ModifierCompilerResult("\\Skrz\\Templating\\Engine\\Helpers::escapeJavascript({$expressionCode})");

				default:
					throw new CompilerException(
						$compiler->getContext(),
						"Only `html`, `url`, and `javascript` modes supported for `truncate` modifier, `{$type}` supplied. Template in " .
						$modifier->getFileName() . " @ " . $modifier->getRow() . ":" . $modifier->getColumn()
					);
			}
		});

		$compilerContext->addModifier("default", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			$resultSym = $compiler->gensym();
			return new ModifierCompilerResult(
				$resultSym,
				"{$resultSym} = @({$expressionCode}); " .
				"if ({$resultSym} == null) { {$resultSym} = " .
				($modifier->hasArgument(0) ? $compiler->walk($modifier->getArgument(0)) : "''") .
				"; }"
			);
		});

		$compilerContext->addModifier("replace", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			return new ModifierCompilerResult(
				"str_replace(" .
				$compiler->walk($modifier->getArgument(0)) . ", " .
				$compiler->walk($modifier->getArgument(1)) . ", " .
				$expressionCode .
				")"
			);
		});

		$compilerContext->addModifier("count", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			return new ModifierCompilerResult("count({$expressionCode})");
		});

		$compilerContext->addModifier("mb_ucfirst", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			$resultSym = $compiler->gensym();
			return new ModifierCompilerResult(
				"(mb_strtoupper(mb_substr({$resultSym}, 0, 1)) . mb_substr({$resultSym}, 1))",
				"{$resultSym} = $expressionCode;"
			);
		});

		$compilerContext->addModifier("mb_lcfirst", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			$resultSym = $compiler->gensym();
			return new ModifierCompilerResult(
				"(mb_strtolower(mb_substr({$resultSym}, 0, 1)) . mb_substr({$resultSym}, 1))",
				"{$resultSym} = $expressionCode;"
			);
		});

		$compilerContext->addModifier("string_format", function (Compiler $compiler, ModifierNode $modifier, $expressionCode) {
			return new ModifierCompilerResult(
				"sprintf(" . $compiler->walk($modifier->getArgument(0)) . ", {$expressionCode})"
			);
		});

		// TODO: add needed modifiers
	}

	/**
	 * Prepare some of default smarty functions
	 */
	public static function prepareFunctions(CompilerContext $compilerContext)
	{
		// TODO: add needed functions
	}

}
