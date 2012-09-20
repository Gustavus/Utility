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
    $this->string = new TestObject(new Utility\String('set up'));
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
   * @test
   */
  public function arrayAccess()
  {
    $string = new Utility\String('test');

    $this->assertSame('t', $string[0]);
    $this->assertSame('e', $string[1]);
    $this->assertSame('s', $string[2]);
    $this->assertSame('t', $string[3]);

    $this->assertTrue(isset($string[0]));
    $this->assertTrue(isset($string[1]));
    $this->assertTrue(isset($string[2]));
    $this->assertTrue(isset($string[3]));
    $this->assertFalse(isset($string[4]));

    $string[0] = 'b';

    $this->assertSame('best', $string->getValue());

    $string[1] = 'l';
    $string[2] = 'a';
    $string[3] = 'r';

    $this->assertSame('blar', $string->getValue());

    $string[4] = 'f';

    $this->assertSame('blarf', $string->getValue());

    // skip one
    $string[6] = '!';

    $this->assertSame('blarf !', $string->getValue());

    unset($string[1]);

    $this->assertSame('barf !', $string->getValue());
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
    $this->assertSame($title, $this->string->titleCase()->getValue());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function titleCaseWithExceptions($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($title, $this->string->titleCase(array_keys($this->string->titleCaseExceptions))->getValue());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function lowerCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($lower, $this->string->lowerCase()->getValue());
  }

  /**
   * @test
   * @dataProvider caseData
   */
  public function upperCase($value, $title, $lower, $upper)
  {
    $this->string->setValue($value);
    $this->assertSame($upper, $this->string->upperCase()->getValue());
  }


  /**
   * @test
   * @dataProvider urlData
   */
  public function url($expected, $url)
  {
    $this->string->setValue($url);
    $this->assertSame($expected, $this->string->url()->getValue());
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
    $this->assertSame($expected, $this->string->email()->getValue());
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
    $this->assertSame($expected, $this->string->widont($lastWordMaxLength)->getValue());
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
    $this->assertSame($expected, $this->string->possessive()->getValue());
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
  public function abbreviateState($expected, $value)
  { 
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->abbreviateState()->getValue());
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
      array('AK', 'alaska'),
      array('MI', 'Michigan'),
      array('ON', 'ONTARIO'),
    );
  }

  /**
   * @test
   * @dataProvider queryStringData
   */
  public function splitQueryString($expected, $value)
  {
    $this->string->setValue($value);
    $this->assertSame($expected, $this->string->splitQueryString()->getValue());
  }

  /**
   * @return array
   */
  public static function queryStringData()
  {
    return [
      [['revisionNumber' => '1', 'oldestRevision' => '0'], '&revisionNumber=1&oldestRevision=0'],
      [['revisionNumber' => '1', 'oldestRevision' => '0'], '?revisionNumber=1&oldestRevision=0'],
      [['revisionNumber' => '1'], '?revisionNumber=1&oldestRevision='],
      [['revisionNumber' => '1'], '?revisionNumber=1&oldestRevision'],
      [['revisionNumber' => '1', 'oldestRevision' => '0'], 'revisionNumber=1&oldestRevision=0'],
      [['revisionNumbers' => ['1', '2']], '?revisionNumbers[]=1&revisionNumbers[]=2'],
    ];
  }
  
  

  /** 
   * @test
   * @dataProvider findNearestInstanceData
   */
  public function testFindNearestInstance($string, $search, $offset, $expected)
  { 
    $this->string->setValue($string);
    $result = $this->string->findNearestInstance($search, $offset);
    
    $this->assertSame($expected, $result);
  }
  
  public function findNearestInstanceData()
  {
    return [
      ['_========================', '/\|/', 0,  false],
      ['============_============', '/\|/', 12, false],
      ['========================_', '/\|/', 25, false],
      ['_=====|==================', '/\|/', 0,  6],
      ['======|=====_============', '/\|/', 12, 6],
      ['======|=================_', '/\|/', 25, 6],
      ['_=====|=========|========', '/\|/', 0,  6],
      ['======|=====_===|========', '/\|/', 12, 16],
      ['======|=========|=======_', '/\|/', 25, 16],
      ['_=====|=========|====|===', '/\|/', 0,  6],
      ['======|=====_===|====|===', '/\|/', 12, 16],
      ['======|=========|====|==_', '/\|/', 25, 21], 

      ['_========================', '/\||\A|\z/', 0,  0],
      ['============_============', '/\||\A|\z/', 12, 0],
      ['========================_', '/\||\A|\z/', 25, 25],
      ['_=====|==================', '/\||\A|\z/', 0,  0],
      ['======|=====_============', '/\||\A|\z/', 12, 6],
      ['======|=================_', '/\||\A|\z/', 25, 25],
      ['_=====|=========|========', '/\||\A|\z/', 0,  0],
      ['======|=====_===|========', '/\||\A|\z/', 12, 16],
      ['======|=========|=======_', '/\||\A|\z/', 25, 25],
      ['_=====|=========|====|===', '/\||\A|\z/', 0,  0],
      ['======|=====_===|====|===', '/\||\A|\z/', 12, 16],
      ['======|=========|====|==_', '/\||\A|\z/', 25, 25]
    ];
  }
  
  
  /**
   * @test
   * @dataProvider excerptData
   */
  public function textExcerpt($string, $length, $offset, $expected)
  {
    $this->string->setValue($string);
    $this->string->excerpt($length, $offset);
  
    $this->assertSame($expected, $this->string->getValue());
  }
  
  public function excerptData()
  {
    return [
      // Unchanging stuff (no spaces to cleanly break on)...
      ['01234567890123456789012345678901234567890123456789', 10,  0, '01234567890123456789012345678901234567890123456789'],
      ['01234567890123456789012345678901234567890123456789', 10, 10, '01234567890123456789012345678901234567890123456789'],
      ['01234567890123456789012345678901234567890123456789', 20, 20, '01234567890123456789012345678901234567890123456789'],

      // Truncate tail...
      ['0123456789 012345678901234567890123456789 0123456789', 25, 0, '0123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 40, 0, '0123456789 012345678901234567890123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 45, 0, '0123456789 012345678901234567890123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 47, 0, '0123456789 012345678901234567890123456789 0123456789'],
      
      // Truncate head...
      ['0123456789 012345678901234567890123456789 0123456789', 20, 25, '...012345678901234567890123456789...'],
      ['0123456789 012345678901234567890123456789 0123456789', 30, 40, '...0123456789'],
      
      // Goofy inputs...
      ['01234 56789', 10, 400, '...56789'],
    ];
  }
  
  
  /**
   * @test
   */
  public function testPrepend()
  {
    $this->string->setValue('123');
    $this->string->prepend('abc');
    
    $this->assertSame('abc123', $this->string->getValue());
    
    try {
      $this->string->setValue('123');
      $this->string->prepend(array('NEIN!'));
      $this->assertTrue(false);
    } catch(\InvalidArgumentException $e) {
      // Yay!
    }
  }
  
  /**
   * @test
   */
  public function testAppend()
  {
    $this->string->setValue('abc');
    $this->string->append('123');
    
    $this->assertSame('abc123', $this->string->getValue());
    
    try {
      $this->string->setValue('abc');
      $this->string->append(array('NEIN!'));
      $this->assertTrue(false);
    } catch(\InvalidArgumentException $e) {
      // Yay!
    }
  }
  
  /**
   * @test
   */
  public function testEncaseInTag()
  {
    $this->string->setValue('abc');
    $this->string->encaseInTag('html');
    
    $this->assertSame('<html>abc</html>', $this->string->getValue());
  
    $failInputs = [
      'uhoh-',
      '789badjoke',
      ':(',
      'no spaces or',
      'punctuation!'
    ];
    
    foreach($failInputs as $input) {
      try {
        $this->string->setValue('abc');
        $this->string->encaseInTag($input);
        $this->assertTrue(false);
      } catch(\InvalidArgumentException $e) {
        $this->assertTrue(true);
      }
    }
  }
}
