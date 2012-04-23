<?php
/**
 * @package Utility
 * @subpackage Test
 */

namespace Gustavus\Utility\Test;

use Gustavus\Utility,
  Gustavus\Test\Test,
  Gustavus\Test\TestObject;

/**
 * @package Utility
 * @subpackage Test
 */
class StringTest extends Test
{
  /**
   * @var Utility\String
   */
  private $string;

  /**
   * sets up the object for each test
   * @return void
   */
  public function setUp()
  {
    $this->string = new Utility\String('set up');
  }

  /**
   * destructs the object after each test
   * @return void
   */
  public function tearDown()
  {
    unset($this->string);
  }

  /**
   * @test
   */
  public function toString()
  {
    $set = new Utility\String('one, two, and three');

    $this->expectOutputString('one, two, and three');
    echo $set;
  }

  /**
   * @test
   * @expectedException DomainException
   */
  public function setValue()
  {
    $this->assertInstanceOf('DomainException', new Utility\String(1));
  }

  /**
   * @test
   */
  public function valueIsValid()
  {
    $string = new TestObject($this->string);
    $this->assertTrue($string->valueIsValid('1'));
    $this->assertTrue($string->valueIsValid('hello'));
    $this->assertFalse($string->valueIsValid(1));
    $this->assertFalse($string->valueIsValid(array(1)));
  }

  /**
   * @return array
   */
  public static function caseData()
  {
    return array(
      array('testing case', 'Testing Case', 'testing case', 'TESTING CASE'),
      array('TESTING CASE', 'Testing Case', 'testing case', 'TESTING CASE'),
      array('a test of case', 'A Test of Case', 'a test of case', 'A TEST OF CASE'),
      array('king henry The viii', 'King Henry the VIII', 'king henry the viii', 'KING HENRY THE VIII'),
    );
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function titleCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($title, $this->string->titleCase());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function lowerCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($lower, $this->string->lowerCase());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function upperCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($upper, $this->string->upperCase());
  }


  /**
   * @test
   * @dataProvider urlData
   */
  public function url($expected, $url)
  {
    $this->string->setValue($url);
    $this->assertSame($expected, $this->string->url());
  }

  /**
   * @return array
   */
  public static function urlData()
  {
    return array(
      array('http://google.com', 'google.com'),
      array('http://google.com', ' google.com '),
      array('http://google.com', 'http://google.com'),
      array('https://google.com', 'https://google.com'),
      array('ftp://google.com', 'ftp://google.com'),

      array('http://google.com/test', 'google.com/test'),
      array('http://google.com/test', ' google.com/test '),
      array('http://google.com/test', 'http://google.com/test'),
      array('https://google.com/test', 'https://google.com/test'),
      array('ftp://google.com/test', 'ftp://google.com/test'),

      // Too short
      array('', 'ab'),

      // Gustavus URLs
      array('http://gustavus.edu', 'gac.edu'),
      array('http://gustavus.edu', ' gac.edu '),
      array('http://gustavus.edu', 'http://gac.edu'),
      array('https://gustavus.edu', 'https://gac.edu'),
      array('ftp://gustavus.edu', 'ftp://gac.edu'),
      array('http://gustavus.edu/test', 'gac.edu/test'),
      array('http://gustavus.edu/test', 'http://gac.edu/test'),
      array('https://gustavus.edu/test', 'https://gac.edu/test'),
      array('ftp://gustavus.edu/test', 'ftp://gac.edu/test'),

      array('http://gustavus.edu', 'gustavus.edu'),
      array('http://gustavus.edu', ' gustavus.edu '),
      array('http://gustavus.edu', 'http://gustavus.edu'),
      array('https://gustavus.edu', 'https://gustavus.edu'),
      array('ftp://gustavus.edu', 'ftp://gustavus.edu'),
      array('http://gustavus.edu/test', 'gustavus.edu/test'),
      array('http://gustavus.edu/test', 'http://gustavus.edu/test'),
      array('https://gustavus.edu/test', 'https://gustavus.edu/test'),
      array('ftp://gustavus.edu/test', 'ftp://gustavus.edu/test'),

      // Homepages
      array('http://homepages.gac.edu/~user/', 'http://homepages.gac.edu/~user/'),
    );
  }

  /**
   * @test
   * @dataProvider emailData
   */
  public function email($expected, $email)
  {
    $this->string->setValue($email);
    $this->assertSame($expected, $this->string->email());
  }

  /**
   * @return array
   */
  public function emailData()
  {
    return array(
      array('joe@google.com', 'joe@google.com'),
      array('joe@google.com', ' joe@google.com'),
      array('joe@google.com', 'joe@google.com '),
      array('joe@google.com', ' joe@google.com '),

      array('joe+test@google.com', 'joe+test@google.com'),
      array('joe+test@google.com', ' joe+test@google.com'),
      array('joe+test@google.com', 'joe+test@google.com '),
      array('joe+test@google.com', ' joe+test@google.com '),

      array('', ''),

      array('joe@gustavus.edu', 'joe'),
      array('joe@gustavus.edu', ' joe'),
      array('joe@gustavus.edu', 'joe '),
      array('joe@gustavus.edu', ' joe '),

      array('joe@gustavus.edu', 'joe@gac.edu'),
      array('joe@gustavus.edu', ' joe@gac.edu'),
      array('joe@gustavus.edu', 'joe@gac.edu '),
      array('joe@gustavus.edu', ' joe@gac.edu '),

      array('joe+test@gustavus.edu', 'joe+test@gac.edu'),
      array('joe+test@gustavus.edu', ' joe+test@gac.edu'),
      array('joe+test@gustavus.edu', 'joe+test@gac.edu '),
      array('joe+test@gustavus.edu', ' joe+test@gac.edu '),

      array('joe@gustavus.edu', 'joe@gustavus.edu'),
      array('joe@gustavus.edu', ' joe@gustavus.edu'),
      array('joe@gustavus.edu', 'joe@gustavus.edu '),
      array('joe@gustavus.edu', ' joe@gustavus.edu '),

      array('joe+test@gustavus.edu', 'joe+test@gustavus.edu'),
      array('joe+test@gustavus.edu', ' joe+test@gustavus.edu'),
      array('joe+test@gustavus.edu', 'joe+test@gustavus.edu '),
      array('joe+test@gustavus.edu', ' joe+test@gustavus.edu '),
    );
  }

  /**
   * @test
   * @dataProvider widontData
   */
  public function widont($expected, $value, $lastWordMaxLength = null)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->widont($lastWordMaxLength));
  }

  /**
   * @return array
   */
  public static function widontData()
  {
    return array(
      array('test ', 'test '),
      array(' test', ' test'),
      array(' test ', ' test '),

      array('test&nbsp;test', 'test test'),
      array('test&nbsp;test', 'test test '),
      array(' test&nbsp;test', ' test test'),
      array(' test&nbsp;test', ' test test '),

      array('test&nbsp;test', 'test  test'),
      array('test&nbsp;test', 'test  test '),
      array(' test&nbsp;test', ' test  test'),
      array(' test&nbsp;test', ' test  test '),

      array('test test&nbsp;test', 'test test test'),
      array('test test&nbsp;test', 'test test test '),
      array(' test test&nbsp;test', ' test test test'),
      array(' test test&nbsp;test', ' test test test '),

      array('test test&nbsp;test', 'test test  test'),
      array('test test&nbsp;test', 'test test  test '),
      array(' test test&nbsp;test', ' test test  test'),
      array(' test test&nbsp;test', ' test test  test '),

      // With max length
      array('test&nbsp;test', 'test test', 5),
      array('test&nbsp;test', 'test test', 4),
      array('test test', 'test test', 3),

      // With max length and ending tags
      array('<strong>test&nbsp;test</strong>', '<strong>test test</strong>', 5),
      array('<strong>test&nbsp;test</strong>', '<strong>test test</strong>', 4),
      array('<strong>test test</strong>', '<strong>test test</strong>', 3),
    );
  }

  /**
   * @test
   * @dataProvider possessiveData
   */
  public function possessive($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->possessive());
  }

  /**
   * @return array
   */
  public static function possessiveData()
  {
    return array(
      array("Gustavus'", 'Gustavus'),
      array("Jesus'", 'Jesus'),
      array("Nick's", 'Nick'),
      array('my', 'I'),
      array("RYAN'S", 'RYAN'),
      array("the hero's", 'the hero'),
      array('my', 'my'),
      array('HIS', 'HIS'),
      array('His', 'He'),
      array('your', 'you'),
      array('Our', 'We'),
      array('my', 'i'),
      array('her', 'she'),
      array('her', 'her'),
      array('its', 'it'),
      array('their', 'they'),
      array('Joe\'s', 'Joe\'s'),
      array('', ''),
    );
  }

  /**
   * @test
   * @dataProvider stateData
   */
  public function state($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->state());
  }

  /**
   * @return array
   */
  public static function stateData()
  {
    return array(
      array('OK', 'Oklahoma'),
      array('MN', 'Minnesota'),
      array('AK', 'Alaska'),
      array('Alaska', 'AK'),
      array('Alaska', 'ak'),
      array('AK', 'alaska'),
      array('MI', 'Michigan'),
      array('ON', 'ONTARIO'),
    );
  }
}
