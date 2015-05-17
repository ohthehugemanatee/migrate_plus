<?php
/**
 * @file
 * Contains \Drupal\migrate_plus\Plugin\migrate\source\csv.
 */

namespace Drupal\migrate_plus\Plugin\migrate\source;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate_plus\CSVFileObject;

/**
 * Source for CSV.
 *
 * If the CSV file contains non-ASCII characters, make sure it includes a
 * UTF BOM (Byte Order Marker) so they are interpreted correctly.
 *
 * @MigrateSource(
 *   id = "csv"
 * )
 */
class CSV extends SourcePluginBase {

  /**
   * List of available source fields.
   *
   * @var array
   */
  protected $fields = array();

  /**
   * List of key fields, as indexes.
   *
   * @var array
   */
  protected $keys = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    // Path is required.
    if (empty($this->configuration['path'])) {
      throw new MigrateException('You must declare the "path" to the source CSV file in your source settings.');
    }

    // Key field(s) are required.
    if (empty($this->configuration['keys'])) {
      throw new MigrateException('You must declare the "keys" the source CSV file in your source settings.');
    }

  }

  /**
   * Return a string representing the source query.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    // File handler using header-rows-respecting extension of SPLFileObject.
    $file = new CSVFileObject($this->configuration['path']);

    // Set basics of CSV behavior based on configuration.
    $delimiter = !empty($this->configuration['delimiter']) ? $this->configuration['delimiter'] : ',';
    $enclosure = !empty($this->configuration['enclosure']) ? $this->configuration['enclosure'] : '"';
    $escape = !empty($this->configuration['escape']) ? $this->configuration['escape'] : '\\';
    $file->setCsvControl($delimiter, $enclosure, $escape);

    // Figure out what CSV column(s) to use. Use either the header row(s) or
    // explicitly provided column name(s).
    if (!empty($this->configuration['header_rows'])) {
      $file->setHeaderRows($this->configuration['header_rows']);

      // Find the last header line.
      $file->rewind();
      $file->seek($file->getHeaderRows() - 1);

      $row = $file->current();
      foreach ($row as $header) {
        $header = trim($header);
        $csv_columns[] = $header;
      }
      $file->setCsvColumns($csv_columns);
    }
    // An explicit list of column name(s) will override any header row(s).
    if (!empty($this->configuration['csv_columns'])) {
      $file->setCsvColumns($this->configuration['csv_columns']);
    }

    return $file;
  }

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    $ids = array();
    foreach ($this->configuration['keys'] as $key) {
      $ids[$key]['type'] = 'string';
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = array();
    foreach ($this->getIterator()->getCsvColumns() as $column) {
      $fields[$column] = $column;
    }

    // Any caller-specified fields with the same names as extracted fields will
    // override them; any others will be added.
    if (!empty($this->configuration['fields'])) {
      $fields = $this->configuration['fields'] + $fields;
    }

    return $fields;
  }

}
