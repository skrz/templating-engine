<?php
namespace Skrz\Templating\Engine;

use Skrz\Templating\Engine\AST\FunctionNode;
use Skrz\Templating\Engine\AST\ModifierNode;
use Skrz\Templating\Engine\VO\StatementAndExpressionVO;

class CompilerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider sourcesProvider
	 */
	public function testCompiles($source, $expectedOutput, $locals)
	{
		//
		// parse
		//
		$parseContext = new ParserContext;
		$parseContext
			->setString($source)
			->setBaseDirectory(__DIR__);

		$template = $parseContext->parse();

		$this->assertInstanceOf('Skrz\\Templating\\Engine\\AST\\TemplateNode', $template);

		//
		// compile
		//
		$compilerContext = new CompilerContext;
		$compilerContext
			->setNamespace('Skrz\\Templating\\Engine')
			->setClassName("Template_" . sha1(uniqid('', true)))
			->setTemplate($template)
			->setParserContext($parseContext)
			->addModifier("strip_tags", function (Compiler $compiler, ModifierNode $modifier, $text) {
				return new ModifierCompilerResult("strip_tags($text)");
			})
			->addFunction("inferno", function (Compiler $compiler, FunctionNode $function) {
				/** @var StatementAndExpressionVO $hell */
				$hell = $compiler->walk($function->getArgument("hell"));
				$ret = "";
				$ret .= "echo '666 ';";
				$ret .= $hell->getStatement();
				$ret .= "echo " . $hell->getExpression() . ";";
				$ret .= "echo ' 666';";
				return new FunctionCompilerResult($ret);
			});

		$compiled = $compilerContext->compile();

		//
		// debug
		//
//		echo "\n\n";
//		echo "$source\n";
//		echo "--------\n";
//		echo $compiled;
//		echo "\n";

		//
		// run
		//
		$this->assertNull(eval("?>$compiled"));
		$fullyQualifiedClassName = $compilerContext->getFullyQualifiedClassName();
		$templateInstance = new $fullyQualifiedClassName;

		ob_start();
		$templateInstance($locals);
		$actualOutput = ob_get_contents();
		ob_end_clean();
		$this->assertEquals($expectedOutput, $actualOutput);
	}

	public function sourcesProvider()
	{
		if (!class_exists("CompilerTest", false)) {
			class_alias("Skrz\\Templating\\Engine\\CompilerTest", "CompilerTest");
		}

		return array(
			array('', '', array()),
			array('just some text', 'just some text', array()),
			array('{$foo}', '42', array("foo" => 42)),
			array('{$foo | strip_tags}', 'bold italic', array("foo" => '<b>bold</b> <i>italic</i>')),
			array('{$foo = 42}{$foo}', '42', array()),
			array('{capture assign=foo}bar{/capture}{$foo}', 'bar', array()),
			array('{capture name=foo}bar{/capture}{$smarty.capture.foo}', 'bar', array()),
			array('{capture append="foo"}hello, {/capture}{capture append="foo"}world!{/capture}{$x = implode("", $foo)}{$x}', 'hello, world!', array()),
			array('{for $x=1 to 3}{$x},{/for}', '1,2,3,', array()),
			array('{for $x=1 to 3}{$x},{forelse}nothing{/for}', '1,2,3,', array()),
			array('{for $x=3 to 1}{$x},{forelse}nothing{/for}', 'nothing', array()),
			array('{foreach $x as $i}{$i},{/foreach}', '1,2,3,', array("x" => array(1, 2, 3))),
			array('{foreach from=$x item=i}{$i},{/foreach}', '1,2,3,', array("x" => array(1, 2, 3))),
			array('{foreach from=$x item="i"}{$i},{/foreach}', '1,2,3,', array("x" => array(1, 2, 3))),
			array('{foreach from=$x item="i"}{$i},{foreachelse}nothing{/foreach}', 'nothing', array("x" => array())),
			array('{foreach from=$x item=i name=c}{$smarty.foreach.c.index}=>{$i},{/foreach}', '0=>1,1=>2,2=>3,', array("x" => array(1, 2, 3))),
			array('{foreach $x as $i}{$i@index}=>{$i},{/foreach}', '0=>1,1=>2,2=>3,', array("x" => array(1, 2, 3))),
			array('{foreach $x as $i}{$i@iteration}=>{$i},{/foreach}', '1=>1,2=>2,3=>3,', array("x" => array(1, 2, 3))),
			array('{foreach $x as $i}{$i@total}=>{$i},{/foreach}', '3=>1,3=>2,3=>3,', array("x" => array(1, 2, 3))),
			array('{foreach $x as $i}{$i@first}=>{$i},{/foreach}', '1=>1,=>2,=>3,', array("x" => array(1, 2, 3))),
			array('{foreach $x as $i}{$i@last}=>{$i},{/foreach}', '=>1,=>2,1=>3,', array("x" => array(1, 2, 3))),
			array('{foreach [1,2,3] as $i}{$i@index}=>{$i},{/foreach}', '0=>1,1=>2,2=>3,', array()),
			array('{inferno hell="BAR"}', '666 BAR 666', array()),
			array('{inferno hell="<b>BAR</b>"}', '666 <b>BAR</b> 666', array()),
			array('{inferno hell="<b>BAR</b>"|strip_tags}', '666 BAR 666', array()),
			array('{call inferno hell="<b>BAR</b>"}', '666 <b>BAR</b> 666', array()),
			array('{call name=inferno hell="<b>BAR</b>"}', '666 <b>BAR</b> 666', array()),
			array('{call name="inferno" hell="<b>BAR</b>"}', '666 <b>BAR</b> 666', array()),
			array('{if $x > 5}>5{elseif $x > 3}>3{else}<=3{/if}', '>5', array("x" => 10)),
			array('{if $x > 5}>5{elseif $x > 3}>3{else}<=3{/if}', '>3', array("x" => 5)),
			array('{if $x > 5}>5{elseif $x > 3}>3{else}<=3{/if}', '>3', array("x" => 4)),
			array('{if $x > 5}>5{elseif $x > 3}>3{else}<=3{/if}', '<=3', array("x" => 3)),
			array('{if $x > 5}>5{elseif $x > 3}>3{else}<=3{/if}', '<=3', array("x" => 1)),
			array("{strip}\n    A   \nB     \n      C\n{/strip}", 'ABC', array()),
			array('{include Includable.tpl}', "There is kinda nothin' :-(\n", array()),
			array('{include file=FooEcho.tpl foo="bar"}', "bar", array()),
			array('{$foo="bar"}{include file=FooAssign.tpl}{$foo}', "bar", array()),
			array('{$foo="bar"}{include file=FooAssign.tpl scope=parent}{$foo}', "foo", array()),
			array('{section foo 3}{$smarty.section.foo.iteration},{/section}', "1,2,3,", array()),
			array('{$a = array("x")}{$a[0]}', "x", array()),
			array('{$a = array(1, 2, 3)}{$a[2]}', "3", array()),
			array('{$a = array(1, 2, 3,)}{$a[2]}', "3", array()),
			array('{$a = array("foo" => "bar")}{$a["foo"]}', "bar", array()),
			array('{$a = array("foo" => "bar",)}{$a["foo"]}', "bar", array()),
			array('{$foo = [1, 2, 3]}{$foo[0]}', "1", array()),
			array('{$foo = [1, 2, 3]}{$foo[1]}', "2", array()),
			array('{extends Empty.tpl}', "", array()),
			array('{extends Includable.tpl}', "There is kinda nothin' :-(\n", array()),
			array('{extends Extendable.tpl}', "", array()),
			array('{extends Extendable.tpl} {block here}foo{/block}', "foo", array()),
			array('{extends ExtendableTitle.tpl}', "title", array()),
			array('{extends ExtendableTitle.tpl}  {block title}different title{/block}', "different title", array()),
			array('{extends ExtendableTitle.tpl}  {block title append}different {/block}', "different title", array()),
			array('{extends ExtendableTitle.tpl}  {block title prepend} is different{/block}', "title is different", array()),
			array('{$x}', '&lt;b&gt;foo&lt;/b&gt;', array("x" => "<b>foo</b>")),
			array('{$x nofilter}', '<b>foo</b>', array("x" => "<b>foo</b>")),
			array('{CompilerTest::formatStringPretty("foo")}', '   foo   ', array()),
			array('{function emptyFn}{/function}', '', array()),
			array('{function emptyFn}{/function}{emptyFn}', '', array()),
			array('{function name="barbar"}barbar{/function}{call barbar}', 'barbar', array()),
			array('{function menu}{if $level < 3}{$level},{menu level=$level+1}{/if}{/function}{menu level=0}', '0,1,2,', array()),
			array('{function myFn withDefault="hey!"}{$withDefault}{/function}{myFn}', 'hey!', array()),
			array('{function menu level=0}{if $level < 3}{$level},{menu level=$level+1}{/if}{/function}{menu}', '0,1,2,', array()),
			array('{function x}{$foo}{/function}{x}', 'bar', array('foo' => 'bar')),
		);
	}

	public static function formatStringPretty($s)
	{
		return "   $s   ";
	}

}
