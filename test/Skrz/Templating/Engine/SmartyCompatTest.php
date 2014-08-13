<?php
namespace Skrz\Templating\Engine;

use Smarty;
use SmartySnippetStorage;

class SmartyCompatTest extends \PHPUnit_Framework_TestCase
{

	private $temporaryDirectory;

	public function setUp()
	{
		$simpleClassName = basename(str_replace("\\", "/", __CLASS__));
		$this->temporaryDirectory = __DIR__ . "/{$simpleClassName}.d";
		@mkdir($this->temporaryDirectory, 0777);
	}

	public function tearDown()
	{
		if (isset($this->temporaryDirectory)) {
			system("rm -rf '{$this->temporaryDirectory}'");
		}
	}

	/**
	 * @dataProvider propertiesProvider
	 */
	public function testPropertiesCompatibility($property, $value)
	{
		$smarty = new Smarty;
		$compat = new SmartyCompat;

		$smarty->$property = $value;
		$compat->$property = $value;

		$this->assertEquals($smarty->$property, $compat->$property);
	}

	public function propertiesProvider()
	{
		return array(
			array("template_dir", __DIR__ . "/../../../templ/"),
			array("compile_dir", $this->temporaryDirectory),
			array("cache_dir", $this->temporaryDirectory),
			array("caching", false),
			array("use_sub_dirs", true),
			array("_dir_perms", 0777),
			array("compile_check", false),
			array("error_reporting", E_ALL & ~E_NOTICE & ~E_WARNING),
			array("debugging_ctrl", "URL"),
		);
	}

	/**
	 * @dataProvider methodsProvider
	 */
	public function testMethodsCompatibility($method, array $arguments)
	{
		$smarty = new Smarty;
		$compat = new SmartyCompat;

		$smarty->template_dir = __DIR__;
		$smarty->compile_dir = $this->temporaryDirectory;

		$compat->setNamespace("Skrz\\Templating\\Engine");
		$compat->template_dir = __DIR__;
		$compat->compile_dir = $this->temporaryDirectory;

		ob_start();
		call_user_func_array(array($smarty, $method), $arguments);
		call_user_func_array(array($compat, $method), $arguments);
		ob_end_clean();
	}

	public function methodsProvider()
	{
		return array(
			array("assign", array("config", (object)array("test" => true))),
			array("display", array(__DIR__ . "/JustATemplate.tpl")),
			array("fetch", array(__DIR__ . "/JustATemplate.tpl")),
		);
	}

	public function testItWorks()
	{
		$compat = new SmartyCompat;
		$compat->setNamespace("Skrz\\Templating\\Engine");
		$compat->template_dir = __DIR__;
		$compat->compile_dir = $this->temporaryDirectory;
		$compat->loadFilter("output", "trim");
		$this->assertEquals("Just a template.", $compat->fetch(__DIR__ . "/JustATemplate.tpl"));
	}

	public function testItWorksWithAssign()
	{
		$compat = new SmartyCompat;
		$compat->setNamespace("Skrz\\Templating\\Engine");
		$compat->template_dir = __DIR__;
		$compat->compile_dir = $this->temporaryDirectory;
		$compat->loadFilter("output", "trim");

		$compat->assign("foo", "bar");

		$this->assertEquals("bar", $compat->fetch(__DIR__ . "/FooEcho.tpl"));
	}

	/**
	 * @dataProvider modifiersProvider
	 */
	public function testModifiers($templateString, $locals)
	{
		$smarty = new Smarty;
		$compat = new SmartyCompat;

		$smarty->template_dir = __DIR__;
		$smarty->compile_dir = $this->temporaryDirectory;

		$compat->setNamespace("Skrz\\Templating\\Engine");
		$compat->template_dir = __DIR__;
		$compat->compile_dir = $this->temporaryDirectory;

		foreach ($locals as $k => $v) {
			$smarty->assign($k, $v);
			$compat->assign($k, $v);
		}

		//echo "\n---\n\n";
		//echo "$templateString\n\n";
		//echo "SMARTY: " . $smarty->fetch("string:{$templateString}") . "\n";
		//echo "  SKRZ: " . $compat->fetch("string:{$templateString}") . "\n\n---\n";

		$this->assertEquals($smarty->fetch("string:{$templateString}"), $compat->fetch("string:" . $templateString));
	}

	public function modifiersProvider()
	{
		return array(
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate}', array()),
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate:40}', array()),
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate:20}', array()),
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate:40:"[...]"}', array()),
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate:40:"...":true}', array()),
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate:40:"...":false:true}', array()),
			array('{"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis mollis purus, sit amet ultrices sapien pulvinar at."|truncate:40:"...":true:true}', array()),

			array('{"<b> foo </b>" nofilter}', array()),
			array('{"<b> foo </b>"|strip_tags:true|trim}', array()),

			array('{"<b> foo </b>" nofilter}', array()),
			array('{"<b> foo </b>"|escape:"html" nofilter}', array()),
			array('{"a b c d"}', array()),
			array('{"a b c d"|escape:"url"}', array()),

			array('"{"\\\t\r\n"|escape:"javascript"}"', array()),

			array('{$foo}', array('foo' => 'bar')),
			array('{$foo}', array('foo' => null)),
			array('{$foo|default}', array('foo' => 'bar')),
			array('{$foo|default}', array('foo' => null)),
			array('{$foo|default:"zooo!"}', array('foo' => 'bar')),
			array('{$foo|default:"zooo!"}', array('foo' => null)),

			array('{"abara"|replace:"bar":""}', array()),

			array('{$foo|count}', array("foo" => array(1, 2, 3))),

//			array('{"š"|ucfirst}', array()),
//			array('{"Š"|lcfirst}', array()),

			array('{123|string_format:"%05d"}', array()),

			array('{$foo|@json_encode nofilter}', array("foo" => (object)array("answer" => 42))),
			array('{$foo|@json_encode:128 nofilter}', array("foo" => (object)array("answer" => 42))),

			array('{section name="foo" loop=3}{$smarty.section.foo.iteration},{/section}', array()),

		);
	}

}
