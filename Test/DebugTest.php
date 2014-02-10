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
    $padding = str_repeat(' ', Debug::DUMP_DEPTH_INCREMENT);

    $selfrefarr = [];
    $selfrefarr[0] =& $selfrefarr;

    $selfrefobj = new StdClass();
    $selfrefobj->self =& $selfrefobj;

    $dummy = new DummyClass();
    $dummy->var2 = 'two';
    $dclass = get_class($dummy);

    $dummy2 = new DummyClassTwo();
    $dclass2 = get_class($dummy2);

    return [
      ['a string',    "(string[8]): \"a string\"\n"],
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

  public function generateDebugOutput($depth)
  {
    return str_repeat(' ', $depth) . get_class($this);
  }
}

