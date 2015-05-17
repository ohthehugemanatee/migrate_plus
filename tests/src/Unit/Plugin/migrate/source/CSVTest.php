<?php
/**
 * @file
 * Code for CSVTest.php.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate_plus\Plugin\migrate\source\CSV
 *
 * @group migrate_plus
 */
class CSVTest extends UnitTestCase {

  /**
   * The class under test.
   *
   * @var \Drupal\migrate_plus|Plugin|migrate|source\CSV
   */
  protected $csv;

  /**
   * The configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The plugin id.
   *
   * @var string
   */
  protected $plugin_id;

  /**
   * The plugin definition.
   *
   * @var array
   */
  protected $plugin_definition;

  /**
   * The mock migration plugin.
   *
   * @var \Drupal\migrate\Entity\MigrationInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->configuration = array(
      'path' => dirname(__FILE__) . '/../../../artifacts/data.csv',
      'keys' => array('id'),
      'header_rows' => 1,
    );
    $this->plugin_id = 'test csv migration';
    $this->plugin_definition = array();
    $this->plugin = $this->getMock('\Drupal\migrate\Entity\MigrationInterface');

    $this->csv = new CSV($this->configuration, $this->plugin_id, $this->plugin_definition, $this->plugin);
  }

  /**
   * @covers ::__construct
   */
  function testCreate() {
    $this->assertInstanceOf('\Drupal\migrate_plus\Plugin\migrate\source\CSV', $this->csv);
  }

  /**
   * @expectedException \Drupal\migrate\MigrateException
   * @expectedExceptionMessage You must declare the "path" to the source CSV file in your source settings.
   */
  public function testMigrateExceptionPathMissing() {
    new CSV(array(), $this->plugin_id, $this->plugin_definition, $this->plugin);
  }

  /**
   * @expectedException \Drupal\migrate\MigrateException
   * @expectedExceptionMessage You must declare the "keys" the source CSV file in your source settings.
   */
  public function testMigrateExceptionKeysMissing() {
    new CSV(array('path' => 'foo'), $this->plugin_id, $this->plugin_definition, $this->plugin);
  }

  /**
   * @covers ::__toString
   */
  function testToString() {
    $this->assertEquals($this->configuration['path'], (string) $this->csv);
  }

  /**
   * @covers ::initializeIterator
   */
  function testInitializeIterator() {
    $config_common = array(
      'path' => dirname(__FILE__) . '/../../../artifacts/data_edge_cases.csv',
      'keys' => array('id'),
    );
    $config_delimiter = array('delimiter' => '|');
    $config_enclosure = array('enclosure' => '%');
    $config_escape = array('escape' => '`');

    $csv = new CSV($config_common + $config_delimiter, $this->plugin_id, $this->plugin_definition, $this->plugin);
    $this->assertEquals(current($config_delimiter), $csv->initializeIterator()
      ->getCsvControl()[0]);
    $this->assertEquals('"', $csv->initializeIterator()->getCsvControl()[1]);

    $csv = new CSV($config_common + $config_enclosure, $this->plugin_id, $this->plugin_definition, $this->plugin);
    $this->assertEquals(',', $csv->initializeIterator()->getCsvControl()[0]);
    $this->assertEquals(current($config_enclosure), $csv->initializeIterator()
      ->getCsvControl()[1]);

    $csv = new CSV($config_common + $config_delimiter + $config_enclosure + $config_escape, $this->plugin_id, $this->plugin_definition, $this->plugin);
    $csvFileObject = $csv->getIterator();
    $row = array(
      '1',
      'Justin',
      'Dean',
      'jdean0@prlog.org',
      'Indonesia',
      '60.242.130.40',
    );
    $csvFileObject->rewind();
    $current = $csvFileObject->current();
    $this->assertArrayEquals($row, $current);

    $csvFileObject = $this->csv->getIterator();
    $row = array(
      'id' => '1',
      'first_name' => 'Justin',
      'last_name' => 'Dean',
      'email' => 'jdean0@prlog.org',
      'country' => 'Indonesia',
      'ip_address' => '60.242.130.40',
    );
    $second_row = array(
      'id' => '2',
      'first_name' => 'Joan',
      'last_name' => 'Jordan',
      'email' => 'jjordan1@tamu.edu',
      'country' => 'Thailand',
      'ip_address' => '137.230.209.171',
    );

    $csvFileObject->rewind();
    $current = $csvFileObject->current();
    $this->assertArrayEquals($row, $current);
    $csvFileObject->next();
    $next = $csvFileObject->current();
    $this->assertArrayEquals($second_row, $next);

    $csv_columns = array(
      'csv_columns' => array(
        'id',
        'first_name',
      ),
    );
    $csv = new CSV($this->configuration + $csv_columns, $this->plugin_id, $this->plugin_definition, $this->plugin);
    $csvFileObject = $csv->getIterator();
    $row = array(
      'id' => '1',
      'first_name' => 'Justin',
    );
    $second_row = array(
      'id' => '2',
      'first_name' => 'Joan',
    );

    $csvFileObject->rewind();
    $current = $csvFileObject->current();
    $this->assertArrayEquals($row, $current);
    $csvFileObject->next();
    $next = $csvFileObject->current();
    $this->assertArrayEquals($second_row, $next);
  }

  /**
   * @covers ::getIDs
   */
  function testGetIDs() {
    $expected = array('id' => array('type' => 'string'));
    $this->assertArrayEquals($expected, $this->csv->getIDs());
  }

  /**
   * @covers ::fields
   */
  function testFields() {
    $fields = array(
      'id' => 'identifier',
      'first_name' => 'User first name',
    );

    $expected = $fields + array(
      'last_name' => 'last_name',
      'email' => 'email',
      'country' => 'country',
      'ip_address' => 'ip_address',
    );
    $csv = new CSV($this->configuration + array('fields' => $fields), $this->plugin_id, $this->plugin_definition, $this->plugin);
    $this->assertArrayEquals($expected, $csv->fields());

    $csv_columns = array('id', 'first_name');
    $csv = new CSV($this->configuration + array('fields' => $fields, 'csv_columns' => $csv_columns), $this->plugin_id, $this->plugin_definition, $this->plugin);
    $this->assertArrayEquals($fields, $csv->fields());
  }

}
