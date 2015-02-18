<?php
namespace Skrz\Templating\Engine;

interface TemplateInterface
{

	/**
	 * Returns true/false whether the template contains function by given name (and hence fetchFunction(), renderFunction() can be called)
	 *
	 * @param string $functionName
	 * @return boolean
	 */
	public function hasFunction($functionName);

	/**
	 * Renders named {function ...} into string
	 *
	 * @param string $functionName
	 * @param array $data
	 * @return string
	 */
	public function fetchFunction($functionName, array $data);

	/**
	 * Renders named {function ...} into output buffer
	 *
	 * @param string $functionName
	 * @param array $data
	 * @return void
	 */
	public function renderFunction($functionName, array $data);

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
