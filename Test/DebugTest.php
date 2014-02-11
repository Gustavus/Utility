<?php
/**
 * DebugTest.php
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 */
namespace Gustavus\Utility\Test;

use Gustavus\Test\Test,
    Gustavus\Utility\Debug,
    Gustavus\Utility\DebugPrinter,

    StdClass;



/**
 * Test suite for the Debug class.
 *
 * @package Utility
 * @subpackage Test
 *
 * @author Chris Rog <crog@gustavus.edu>
 *
 * @coversDefaultClass Gustavus\Utility\Debug
 */
class DebugTest extends Test
{
  /**
   * @test
   * @dataProvider dataForDump
   *
   * @covers ::dump
   */
  public function testDumpWithCapture($var, $expected)
  {
    ob_start();
    $output = Debug::dump($var, true);
    $captured = ob_get_contents();
    ob_end_clean();

    $this->assertEmpty($captured);
    $this->assertEquals($expected, $output);
  }

  /**
   * @test
   * @dataProvider dataForDump
   *
   * @covers ::dump
   */
  public function testDumpNoCapture($var, $expected)
  {
    ob_start();
    $output = Debug::dump($var, false);
    $captured = ob_get_contents();
    ob_end_clean();

    $this->assertEquals($expected, $captured);
    $this->assertEmpty($output);
  }

  /**
   * Data provider for the dump test.
   *
   * @return array
   */
  public function dataForDump()
  {
    $padding = str_repeat(' ', Debug::DUMP_INDENT_INCREMENT);

    $selfrefarr = [];
    $selfrefarr[0] =& $selfrefarr;

    $selfrefobj = new StdClass();
    $selfrefobj->self =& $selfrefobj;

    $dummy = new DummyClass();
    $dummy->var2 = 'two';
    $dclass = get_class($dummy);

    $dummy2 = new DummyClassTwo();
    $dclass2 = get_class($dummy2);


    $longstr = "0123456789\x000123456789\n0123456789'0123456789\"0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789";
    $longstrexp = "0123456789\\00123456789\\n0123456789\\'0123456789\\\"01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901...";


    return [
      ['a string',    "(string[8]): \"a string\"\n"],
      [$longstr,      "(string[264]): \"{$longstrexp}\"\n"],
      ['',            "(string[0]): \"\"\n"],
      [true,          "(boolean): true\n"],
      [false,         "(boolean): false\n"],
      [123,           "(integer): 123\n"],
      [-321,          "(integer): -321\n"],
      [3.14159,       "(double): 3.14159\n"],
      [-2.71828,      "(double): -2.71828\n"],
      [STDOUT,        "(resource)\n"],
      [null,          "(null)\n"],

      [$dummy,        "(object): {$dclass} {\n{$padding}var => (null)\n{$padding}var2 => (string[3]): \"two\"\n}\n"],
      [$dummy2,       "(object): {$dclass2} {\n{$padding}{$dclass2}\n}\n"],

      [$selfrefarr,   "(array[1]) {\n{$padding}0 => (array[1]) {\n{$padding}{$padding}0 => (array[1]) {\n{$padding}{$padding}{$padding}**RECURSION: 1 level(s)**\n{$padding}{$padding}}\n{$padding}}\n}\n"],
      [$selfrefobj,   "(object): stdClass {\n{$padding}self => (object): stdClass {\n{$padding}{$padding}**RECURSION: 1 level(s)**\n{$padding}}\n}\n"],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   *
   * @covers ::dumpAll
   */
  public function testDumpAll()
  {
    $data = $this->dataForDump();
    $vars = [];
    $expected = '';

    // Combine all elements into the parameters and expected output...
    foreach ($data as $set) {
      $vars[] = $set[0];
      $expected .= $set[1];
    }

    // Throw them at dumpAll and check our output.
    ob_start();
    $output = call_user_func_array('\\Gustavus\\Utility\\Debug::dumpAll', $vars);
    $captured = ob_get_contents();
    ob_end_clean();

    $this->assertEquals($expected, $captured);
    $this->assertEmpty($output);
  }

  /**
   * @test
   *
   * @covers ::dumpAllToString
   */
  public function testDumpAllToString()
  {
    $data = $this->dataForDump();
    $vars = [];
    $expected = '';

    // Combine all elements into the parameters and expected output...
    foreach ($data as $set) {
      $vars[] = $set[0];
      $expected .= $set[1];
    }

    // Throw them at dumpAll and check our output.
    ob_start();
    $output = call_user_func_array('\\Gustavus\\Utility\\Debug::dumpAllToString', $vars);
    $captured = ob_get_contents();
    ob_end_clean();

    $this->assertEmpty($captured);
    $this->assertEquals($expected, $output);
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForIndentation
   *
   * @cover ::dump
   */
  public function testInitialIndent($var, $indent, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $result = Debug::dump($var, true, $indent);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the indent test.
   *
   * @return array
   */
  public function dataForIndentation()
  {
    $padding = str_repeat(' ', Debug::DUMP_INDENT_INCREMENT);

    $dummy = new DummyClass();
    $dummy->var2 = 'two';
    $dclass = get_class($dummy);

    $dummy2 = new DummyClassTwo();
    $dclass2 = get_class($dummy2);

    return [
      ['a string',  3, "   (string[8]): \"a string\"\n",  null],
      ['',          5, "     (string[0]): \"\"\n",        null],
      [true,        3, "   (boolean): true\n",            null],
      [false,       5, "     (boolean): false\n",         null],
      [123,         3, "   (integer): 123\n",             null],
      [-321,        5, "     (integer): -321\n",          null],
      [3.14159,     3, "   (double): 3.14159\n",          null],
      [-2.71828,    5, "     (double): -2.71828\n",       null],
      [STDOUT,      3, "   (resource)\n",                 null],
      [null,        5, "     (null)\n",                   null],

      [$dummy,      3,    "   (object): {$dclass} {\n   {$padding}var => (null)\n   {$padding}var2 => (string[3]): \"two\"\n   }\n", null],
      [$dummy2,     5,    "     (object): {$dclass2} {\n     {$padding}{$dclass2}\n     }\n", null],

      [null, -1, null, 'InvalidArgumentException'],
      [null, '', null, 'InvalidArgumentException'],
      [null, '1', null, 'InvalidArgumentException'],
      [null, true, null, 'InvalidArgumentException'],
      [null, false, null, 'InvalidArgumentException'],
      [null, 3.14159, null, 'InvalidArgumentException'],
      [null, -2.71828, null, 'InvalidArgumentException'],
      [null, [1], null, 'InvalidArgumentException'],
      [null, new StdClass(), null, 'InvalidArgumentException'],
      [null, STDOUT, null, 'InvalidArgumentException'],
    ];
  }

////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @test
   * @dataProvider dataForMaxDepth
   *
   * @cover ::dump
   */
  public function testMaxDepth($var, $maxdepth, $expected, $exception)
  {
    if (!empty($exception)) {
      $this->setExpectedException($exception);
    }

    $result = Debug::dump($var, true, 0, $maxdepth);
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for the max depth test(s).
   *
   * @return array
   */
  public function dataForMaxDepth()
  {
    $padding = str_repeat(' ', Debug::DUMP_INDENT_INCREMENT);

    $obj1 = new StdClass();
    $obj2 = new StdClass();
    $obj3 = new StdClass();

    $obj3->next = null;
    $obj2->next = $obj3;
    $obj1->next = $obj2;


    $arr1 = [];
    $arr2 = [];
    $arr3 = [];

    $arr3['next'] = null;
    $arr2['next'] = $arr3;
    $arr1['next'] = $arr2;

    return [
      [$obj1, 5, "(object): stdClass {\n{$padding}next => (object): stdClass {\n{$padding}{$padding}next => (object): stdClass {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$obj1, 4, "(object): stdClass {\n{$padding}next => (object): stdClass {\n{$padding}{$padding}next => (object): stdClass {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$obj1, 3, "(object): stdClass {\n{$padding}next => (object): stdClass {\n{$padding}{$padding}next => (object): stdClass {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$obj1, 2, "(object): stdClass {\n{$padding}next => (object): stdClass {\n{$padding}{$padding}next => (object): stdClass {\n{$padding}{$padding}{$padding}...\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$obj1, 1, "(object): stdClass {\n{$padding}next => (object): stdClass {\n{$padding}{$padding}...\n{$padding}}\n}\n", null],
      [$obj1, 0, "(object): stdClass {\n{$padding}...\n}\n", null],
      [$obj1, -1, "(object): stdClass {\n{$padding}next => (object): stdClass {\n{$padding}{$padding}next => (object): stdClass {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],

      [$arr1, 5, "(array[1]) {\n{$padding}next => (array[1]) {\n{$padding}{$padding}next => (array[1]) {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$arr1, 4, "(array[1]) {\n{$padding}next => (array[1]) {\n{$padding}{$padding}next => (array[1]) {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$arr1, 3, "(array[1]) {\n{$padding}next => (array[1]) {\n{$padding}{$padding}next => (array[1]) {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$arr1, 2, "(array[1]) {\n{$padding}next => (array[1]) {\n{$padding}{$padding}next => (array[1]) {\n{$padding}{$padding}{$padding}...\n{$padding}{$padding}}\n{$padding}}\n}\n", null],
      [$arr1, 1, "(array[1]) {\n{$padding}next => (array[1]) {\n{$padding}{$padding}...\n{$padding}}\n}\n", null],
      [$arr1, 0, "(array[1]) {\n{$padding}...\n}\n", null],
      [$arr1, -1, "(array[1]) {\n{$padding}next => (array[1]) {\n{$padding}{$padding}next => (array[1]) {\n{$padding}{$padding}{$padding}next => (null)\n{$padding}{$padding}}\n{$padding}}\n}\n", null],

      [null, '', null, 'InvalidArgumentException'],
      [null, '1', null, 'InvalidArgumentException'],
      [null, '-1', null, 'InvalidArgumentException'],
      [null, true, null, 'InvalidArgumentException'],
      [null, false, null, 'InvalidArgumentException'],
      [null, 3.14159, null, 'InvalidArgumentException'],
      [null, -2.71828, null, 'InvalidArgumentException'],
      [null, [1], null, 'InvalidArgumentException'],
      [null, new StdClass(), null, 'InvalidArgumentException'],
      [null, STDOUT, null, 'InvalidArgumentException'],    ];
  }
}

////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Test class used to ensure classes are output as expected.
 */
class DummyClass
{
  public static $svar;
  public $var;
}

////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Test class used to ensure classes are output as expected.
 */
class DummyClassTwo implements DebugPrinter
{
  public static $svar;
  public $var;

  public function generateDebugOutput($indent, $maxdepth)
  {
    return str_repeat(' ', $indent) . get_class($this);
  }
}

