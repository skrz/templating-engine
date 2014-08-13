# Skrz.cz templating engine

Like Smarty, but faster


## Why?

At [Skrz.cz](http://skrz.cz/), the largest Czech best-prices search engine, we heavily use [Smarty](http://www.smarty.net/).
However, Smarty's official implementation ceased to meet our needs, mainly regarding performance.

`Skrz\Templating\Engine` is Smarty syntax-compatible. Not every Smarty construct has been implemented. You can think of
what has been implemented as _the good parts_.

Key design decisions are:

- Compile time and run time are strictly separated. When you do `->display(...)`, or `->fetch(...)` in Smarty, many things
can happen. Smarty checks if the template has been compiled, if compiled code is still valid, if cached content hasn't been hit, etc.
`Skrz\Templating\Engine` does none of that.

- Compilation is managed by external tool. At Skrz we use [Grunt](http://gruntjs.com/) to compile CSS, Javascript,
and also templates in development.

- Compilation output is an autoload-able class, rendering should be encapsulated in a method. Smarty compiles templates
into a bunch of functions that reside inside `if` statements, so they cannot be declared twice.
With JIT-ing PHP implementations coming up (HHVM, PHP-NG, HippyVM), this is quite sub-optimal, because certain optimizations
cannot be applied (e.g. HHVM can optimize file inclusion that only contains classes at the top level, into just populating
class tables).


## Usage

`Skrz\Templating\Engine` provides only parser and compiler. Please refer to tests on how they are used.

Basically, you just create `ParserContext`, parse template file, create `CompilerContext`, and compile into output file.

Let's say, you have template `MyTemplate.tpl` in current directory and want to compile it into `MyTemplate.php`:


    $outputFileName = __DIR__ . "/MyTemplate.php";

    $parserContext = new ParserContext();
    $parserContext
        ->addPath(__DIR__);
        ->setFile("MyTemplate.tpl");

    $compilerContext = new CompilerContext();
    $compilerContext
        ->setParserContext($parserContext)
        ->setClassName("MyTemplate")
        ->setTemplate($parserContext->parse())
        ->setOutputFileName($outputFileName)
        ->dump();


## License

The MIT license. See `LICENSE` file.
