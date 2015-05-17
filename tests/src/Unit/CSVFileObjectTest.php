<?php
/**
 * @file
 * Code for CSVFileObjectTest.php.
 */

namespace Drupal\migrate_plus\UnitTests;

use Drupal\migrate_plus\CSVFileObject;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate_plus\CSVFileObject
 *
 * @group migrate_plus
 */
class CSVFileObjectTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\migrate_plus\CSVFileObject
   */
  protected $csvFileObject;


  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->csvFileObject = new CSVFileObject(dirname(__FILE__) . '/artifacts/data.csv');
  }

  /**
   * @covers ::__construct
   */
  function testCreate() {
    $this->assertInstanceOf('\Drupal\migrate_plus\CSVFileObject', $this->csvFileObject);
    $flags = CSVFileObject::READ_CSV | CSVFileObject::READ_AHEAD | CSVFileObject::DROP_NEW_LINE | CSVFileObject::SKIP_EMPTY;
    $this->assertEquals($flags, $this->csvFileObject->getFlags());
  }

  /**
   * @covers ::getHeaderRows
   * @covers ::setHeaderRows
   */
  public function testHeaderRows() {
    $expected = 1;
    $this->csvFileObject->setHeaderRows($expected);
    $actual = $this->csvFileObject->getHeaderRows();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers ::count
   */
  public function testCount() {
    $expected = 15;
    $this->csvFileObject->setHeaderRows(1);
    $actual = $this->csvFileObject->count();

    $this->assertEquals($expected, $actual);
  }

  /**
   * @covers ::current
   * @covers ::rewind
   * @covers ::getCsvColumns
   * @covers ::setCsvColumns
   */
  public function testCurrent() {
    $columns = array(
      'id',
      'first_name',
      'last_name',
      'email',
      'country',
      'ip_address',
    );
    $row = array(
      '1',
      'Justin',
      'Dean',
      'jdean0@prlog.org',
      'Indonesia',
      '60.242.130.40',
    );

    $this->csvFileObject->rewind();
    $current = $this->csvFileObject->current();
    $this->assertArrayEquals($columns, $current);

    $this->csvFileObject->setHeaderRows(1);
    $this->csvFileObject->rewind();
    $current = $this->csvFileObject->current();
    $this->assertArrayEquals($row, $current);

    $this->csvFileObject->setCsvColumns($columns);
    $this->csvFileObject->rewind();
    $current = $this->csvFileObject->current();
    $this->assertArrayEquals($columns, array_keys($current));
    $this->assertArrayEquals($row, array_values($current));
    $this->assertArrayEquals($columns, $this->csvFileObject->getCsvColumns());
  }

}
