<?php
namespace Skrz\Templating\Engine;

interface TemplateInterface
{

	/**
	 * Renders template into string
	 *
	 * @param array $data
	 * @return string
	 */
	public function fetch(array $data);

	/**
	 * Renders template into output buffer
	 *
	 * @param array $data
	 * @return void
	 */
	public function render(array $data);

}
