<?php
/**
 * @file
 * Contains \Drupal\migrate_plus\CSVFileObject.php.
 */

namespace Drupal\migrate_plus;

/**
 * Defines a CSV file object.
 *
 * @package Drupal\migrate_plus.
 *
 * Extends SPLFileObject to:
 * - assume CSV format
 * - skip header rows on rewind()
 * - address columns by header row name instead of index.
 */
class CSVFileObject extends \SplFileObject {

  /**
   * The number of rows in the CSV file before the data starts.
   *
   * @var integer
   */
  protected $headerRows = 0;

  /**
   * The human-readable column headers, keyed by column index in the CSV.
   *
   * @var array
   */
  protected $csvColumns = array();

  /**
   * {@inheritdoc}
   */
  public function __construct($file_name) {
    parent::__construct($file_name);

    $this->setFlags(CSVFileObject::READ_CSV | CSVFileObject::READ_AHEAD | CSVFileObject::DROP_NEW_LINE | CSVFileObject::SKIP_EMPTY);
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->seek($this->getHeaderRows());
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    $row = parent::current();

    if ($row && !empty($this->csvColumns)) {
      // Only use rows specified in the defined CSV columns.
      $row = array_intersect_key($row, $this->csvColumns);
      // Set meaningful keys for the columns mentioned in $this->csvColumns.
      foreach ($this->csvColumns as $key => $value) {
        // Copy value to more descriptive key and unset original.
        $row[$value] = isset($row[$key]) ? $row[$key] : NULL;
        unset($row[$key]);
      }
    }

    return $row;
  }

  /**
   * Return a count of all available source records.
   */
  public function count() {
    return iterator_count($this);
  }

  /**
   * Number of header rows.
   *
   * @return int
   *   Get the number of header rows, zero if no header row.
   */
  public function getHeaderRows() {
    return $this->headerRows;
  }

  /**
   * Number of header rows.
   *
   * @param int $header_rows
   *   Set the number of header rows, zero if no header row.
   */
  public function setHeaderRows($header_rows) {
    $this->headerRows = $header_rows;
  }

  /**
   * CSV column names.
   *
   * @return array
   *   Get CSV column names.
   */
  public function getCsvColumns() {
    return $this->csvColumns;
  }

  /**
   * CSV column names.
   *
   * @param array $csv_columns
   *   Set CSV column names.
   */
  public function setCsvColumns(array $csv_columns) {
    $this->csvColumns = $csv_columns;
  }

}
