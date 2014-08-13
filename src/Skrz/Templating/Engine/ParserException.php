<?php
namespace Skrz\Templating\Engine;

class ParserException extends TemplateException
{

	/**
	 * @var ParserContext
	 */
	private $context;

	/**
	 * @var \stdClass
	 */
	private $info;

	public function __construct($context, $reason = null)
	{
		$this->context = $context;

		if (is_object($reason) || is_array($reason)) {
			$this->info = (object)$reason;
		}

		parent::__construct(
			"Parsing failed for " . $this->context->getFile() .
			(
			$this->info !== null
				? " @ " . $this->info->line . ":" . $this->info->column .
				", expected " . implode(", ", $this->info->expected)
				: " - " . $reason
			)
		);
	}

	public function getContext()
	{
		return $this->context;
	}

	public function getInfo()
	{
		return $this->info;
	}

}
