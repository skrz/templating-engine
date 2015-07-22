<?php
namespace Skrz\Templating\Engine;

/**
 * Manages parser
 *
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 */
class ParserContext
{

	const STRING_PSEUDO_FILE = "{string}";

	/** @var ParserContext */
	private $parent = null;

	/** @var string parsed string */
	private $string;

	/** @var string parsed file */
	private $file;

	/** @var string absolute path to file */
	private $fileName;

	/** @var string[] all file names used in this context and all derived contexts */
	private $allFileNames = [];

	/** @var string base template directory, from which templates get loaded */
	private $baseDirectory;

	/** @var string[] */
	private $path = array();

	/** @var Parser current parser instance */
	private $parser;

	/** @var array list of all encountered variable names */
	private $variableNames = array();

	/** @var array list of all assigned variable names */
	private $assignedVariableNames = array();

	/** @var array of local class name => fully qualified class name */
	private $uses = array();

	public function __construct()
	{

	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getString()
	{
		return $this->string;
	}

	public function setString($string)
	{
		$this->string = $string;
		return $this;
	}

	public function getFile()
	{
		return $this->file;
	}

	public function setFile($file)
	{
		$this->file = $file;
		return $this;
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function getAllFileNames()
	{
		return $this->allFileNames;
	}

	public function getBaseDirectory()
	{
		return $this->baseDirectory;
	}

	public function setBaseDirectory($baseDirectory)
	{
		$this->baseDirectory = $baseDirectory;
		return $this;
	}

	public function getVariableNames()
	{
		return array_keys($this->variableNames);
	}

	public function addVariableName($variableName)
	{
		$this->variableNames[$variableName] = true;
		return $this;
	}

	public function getAssignedVariableNames()
	{
		return $this->assignedVariableNames;
	}

	public function addAssignedVariableName($variableName)
	{
		$this->assignedVariableNames[$variableName] = true;
		return $this;
	}

	public function resolveFileName()
	{
		$path = $this->path;
		if (!in_array($this->baseDirectory, $this->path)) {
			$path[] = $this->baseDirectory;
		}

		if ($this->string === null) {
			if ($this->file === null || $this->file === self::STRING_PSEUDO_FILE) {
				throw new ParserException($this, "either string or file has to be supplied to context");
			}

			$fileName = null;

			if ($this->file[0] === "/") {
				$fileName = $this->file;

			} elseif ($this->file[0] === ".") {
				if (!$this->parent) {
					throw new ParserException($this, "Relative file '{$this->file}' and no parent context.");
				}

				$fileName = dirname($this->parent->resolveFileName()) . "/" . $this->file;

			} else {
				foreach ($path as $directory) {
					if (file_exists($directory . "/" . $this->file)) {
						$fileName = $directory . "/" . $this->file;
						break;
					}
				}

				if ($fileName === null) {
					throw new ParserException($this, "File '{$this->file}' was not found in path.");
				}
			}

			$this->fileName = $fileName;
			if (!in_array($this->fileName, $this->allFileNames)) {
				$this->allFileNames[] = $this->fileName;
			}
		}

		if ($this->file === null) {
			$this->file = $this->fileName = self::STRING_PSEUDO_FILE;
		}

		return $this->fileName;
	}

	/**
	 * @param string $directory
	 * @return $this
	 */
	public function addPath($directory)
	{
		if (!in_array($directory, $this->path)) {
			$this->path[] = $directory;
		}

		return $this;
	}

	/**
	 * @return array
	 */
	public function getUses()
	{
		return $this->uses;
	}

	/**
	 * @param string $alias
	 * @param string $fqn
	 * @return $this
	 */
	public function addUse($alias, $fqn)
	{
		$this->uses[$alias] = $fqn;
		return $this;
	}

	/**
	 * @param string $alias
	 * @return boolean
	 */
	public function hasUse($alias)
	{
		return isset($this->uses[$alias]);
	}

	/**
	 * @return \Skrz\Templating\Engine\AST\TemplateNode
	 * @throws \Skrz\Templating\Engine\ParserException
	 */
	public function parse()
	{
		$fileName = $this->resolveFileName();

		if ($this->string === null) {
			if ($this->file === null || $this->file === self::STRING_PSEUDO_FILE) {
				throw new ParserException($this, "either string or file has to be supplied to context");
			}

			$fileContents = @file_get_contents($fileName); // intentionally @
			if ($fileContents === false) {
				throw new ParserException($this, "file $fileName does not exists or is not readable");
			}

			$this->string = $fileContents;
		}

		$this->parser = new Parser($this);
		list($ok, $ast, $errinfo) = $this->parser->parse($this->string);

		if (!$ok) {
			throw new ParserException($this, $errinfo);
		}

		return $ast;
	}

	/**
	 * Create derived context - used by includes
	 *
	 * @param string $file
	 * @return ParserContext
	 */
	public function derive($file)
	{
		$derivedContext = clone $this;
		$derivedContext->parent = $this;
		$derivedContext->string = null;
		$derivedContext->parser = null;
		$derivedContext->fileName = null;
		$derivedContext->variableName = array();
		$derivedContext->assignedVariableNames = array();
		$derivedContext->uses =& $this->uses; // intentionally `=&` - derived contexts share same uses
		$derivedContext->allFileNames =& $this->allFileNames; // intentionally `=&` - derived contexts share all file names
		$derivedContext->setFile($file);

		return $derivedContext;
	}

}
