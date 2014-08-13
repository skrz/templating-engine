<?php
namespace Skrz\Templating\Engine;

use Skrz\Templating\Engine\AST\TemplateNode;

/**
 * Manages compilation
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class CompilerContext
{

	/** @var string namespace of compiled templates' classes */
	private $namespace;

	/** @var string */
	private $className;

	/** @var callable[] */
	private $modifiers = array();

	/** @var callable[] */
	private $functions = array();

	/** @var TemplateNode */
	private $template;

	/** @var Compiler */
	private $compiler;

	/** @var ParserContext */
	private $parserContext;

	/** @var int */
	private $errorReporting;

	/** @var string */
	private $appPath;

	/** @var string */
	private $outputFileName;

	/** @var string */
	private $beforeCode;

	/** @var string */
	private $afterCode;

	public function __construct()
	{
	}

	public function getNamespace()
	{
		return $this->namespace;
	}

	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}

	public function getUses()
	{
		return $this->parserContext->getUses();
	}

	public function addUse($alias, $fqn)
	{
		$this->parserContext->addUse($alias, $fqn);
		return $this;
	}

	public function getClassName()
	{
		return $this->className;
	}

	public function setClassName($className)
	{
		$this->className = $className;
		return $this;
	}

	public function getErrorReporting()
	{
		return $this->errorReporting;
	}

	public function setErrorReporting($errorReporting)
	{
		$this->errorReporting = $errorReporting;
		return $this;
	}

	public function getModifier($name)
	{
		return isset($this->modifiers[$name]) ? $this->modifiers[$name] : null;
	}

	public function addModifier($name, $callback)
	{
		$this->modifiers[$name] = $callback;
		return $this;
	}

	public function getFunction($name)
	{
		return isset($this->functions[$name]) ? $this->functions[$name] : null;
	}

	public function addFunction($name, $callback)
	{
		$this->functions[$name] = $callback;
		return $this;
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function setTemplate($template)
	{
		$this->template = $template;
		return $this;
	}

	public function getCompiler()
	{
		return $this->compiler;
	}

	public function getParserContext()
	{
		return $this->parserContext;
	}

	public function setParserContext($parserContext)
	{
		$this->parserContext = $parserContext;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAppPath()
	{
		return $this->appPath;
	}

	/**
	 * @param string $appPath
	 * @return $this
	 */
	public function setAppPath($appPath)
	{
		$this->appPath = $appPath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOutputFileName()
	{
		return $this->outputFileName;
	}

	/**
	 * @param string $outputFileName
	 * @return $this
	 */
	public function setOutputFileName($outputFileName)
	{
		$this->outputFileName = $outputFileName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getBeforeCode()
	{
		return $this->beforeCode;
	}

	/**
	 * @param string $beforeCode
	 */
	public function setBeforeCode($beforeCode)
	{
		$this->beforeCode = $beforeCode;
	}

	/**
	 * @return string
	 */
	public function getAfterCode()
	{
		return $this->afterCode;
	}

	/**
	 * @param string $afterCode
	 */
	public function setAfterCode($afterCode)
	{
		$this->afterCode = $afterCode;
	}

	public function getFullyQualifiedClassName($templateFile = null)
	{
		if ($templateFile === null) {
			$templateFile = !is_null($this->template)
				? $this->template->getContext()->getFile()
				: $this->parserContext->getFile();

			if ($this->className !== null) {
				return "\\" . trim(trim($this->namespace, "\\") . "\\" . trim($this->className, "\\"), "\\");
			}
		}

		return "\\" . trim(trim($this->namespace, "\\") . "\\" . trim($this->formatClassName($templateFile), "\\"), "\\");
	}

	protected function formatClassName($templateFile)
	{
		if ($templateFile === ParserContext::STRING_PSEUDO_FILE) {
			return "Template";
		}

		if ($templateFile[0] === "/") {
			$templateFile = basename($templateFile);
		}

		if (strcasecmp(substr($templateFile, -4), ".tpl") === 0) {
			$templateFile = substr($templateFile, 0, -4);
		}

		$templateFile = preg_replace("~[^0-9A-Za-z/]+~", "_", $templateFile);
		$templateFile = str_replace("/", "\\", $templateFile);

		return $templateFile;
	}

	public function compile()
	{
		$this->compiler = new Compiler($this);
		return $this->compiler->compile();
	}

	public function dump()
	{
		if ($this->outputFileName === null) {
			throw new CompilerException(
				$this,
				"To be able to call dump(), you have to specify outputFileName (setOutputFileName(...))."
			);
		}

		if (@file_put_contents($this->outputFileName, $this->compile()) === false) {
			throw new CompilerException(
				$this,
				"Could not write output to file '{$this->outputFileName}'."
			);
		}
	}

}
