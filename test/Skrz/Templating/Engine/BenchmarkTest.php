<?php
namespace Skrz\Templating\Engine;

/**
 * @author Jakub Kulhan <jakub.kulhan@gmail.com>
 *
 * @group benchmark
 */
class BenchmarkTest extends \PHPUnit_Framework_TestCase
{

	private static $temporaryDirectory;

	public function tearDown()
	{
		if (isset(self::$temporaryDirectory)) {
			system("rm -rf '" . self::$temporaryDirectory . "'");
		}
	}

	/**
	 * @dataProvider engineProvider
	 */
	public function testBenchmark($name, $engine, $rowsCount)
	{
		$rows = array();
		for ($i = 0; $i < $rowsCount; ++$i) {
			$row = array();
			for ($j = 0; $j < 8; ++$j) {
				$row[] = "Data $i, $j";
			}
			$rows[] = $row;
		}


		$data = array(
			"title" => "Hello, world!",
			"stylesheets" => array("screen.css", "print.css"),
			"javascripts" => array("jquery.js", "application.js"),
			"rows" => $rows,
		);

		if ($engine instanceof \Smarty) {
			foreach ($data as $k => $v) {
				$engine->assign($k, $v);
			}

			$engine->fetch("Page.tpl");
		} else {
			$engine->fetch($data);
		}


		$startTime = microtime(true);
		for ($i = 0; $i < 100000 / $rowsCount; ++$i) {
			if ($engine instanceof \Smarty) {
				$engine->fetch("Page.tpl");
			} else {
				$engine->fetch($data);
			}
		}
		$endTime = microtime(true);

		echo "\n\n",
		"--- {$name} ({$rowsCount} rows)\n",
		"time: ", ($endTime - $startTime), "\n",
		"\n";
	}

	public function engineProvider()
	{
		$simpleClassName = basename(str_replace("\\", "/", __CLASS__));
		self::$temporaryDirectory = __DIR__ . "/{$simpleClassName}.d";
		@mkdir(self::$temporaryDirectory, 0777);

		// SMARTY
		$smarty = new \Smarty();
		$smarty->template_dir = __DIR__;
		$smarty->compile_dir = self::$temporaryDirectory;
		$smarty->default_modifiers = array('escape:"html"');

		// SKRZ
		$parserContext = new ParserContext();
		$parserContext
			->setFile("Page.tpl")
			->addPath(__DIR__);

		$compilerContext = new CompilerContext();
		$compilerContext
			->setParserContext($parserContext)
			->setNamespace("Skrz\\Templating\\Engine")
			->setClassName("PageView")
			->setTemplate($parserContext->parse())
			->setAppPath(__DIR__ . "/../../../..")
			->setOutputFileName(self::$temporaryDirectory . "/PageView.php");

		file_put_contents(self::$temporaryDirectory . "/PageView.php", $compilerContext->compile());

		require_once self::$temporaryDirectory . "/PageView.php";

		$skrz = new \Skrz\Templating\Engine\PageView;

		return array(
			array("SMARTY", $smarty, 10),
			array("SKRZ", $skrz, 10),
			array("SMARTY", $smarty, 100),
			array("SKRZ", $skrz, 100),
			array("SMARTY", $smarty, 1000),
			array("SKRZ", $skrz, 1000),
			array("SMARTY", $smarty, 10000),
			array("SKRZ", $skrz, 10000),
		);
	}

}
