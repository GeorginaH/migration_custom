<?php
/**
 * Created by PhpStorm.
 * User: ada
 * Date: 07-03-17
 * Time: 01:10 PM
 */

namespace Drupal\custom_migration\Plugin\migrate\process;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * @see format_date plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "format_date_custom"
 * )
 */
class FormatDateCustom  extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($value)) {
      return '';
    }
    $transformed = '';
    // Validate the configuration.
    if (empty($this->configuration['from_format'])) {
      throw new MigrateException('Format date plugin is missing from_format configuration.');
    }
    if (empty($this->configuration['to_format'])) {
      throw new MigrateException('Format date plugin is missing to_format configuration.');
    }

    $fromFormat = $this->configuration['from_format'];
    $toFormat = $this->configuration['to_format'];
    $timezone = isset($this->configuration['timezone']) ? $this->configuration['timezone'] : NULL;
    $settings = isset($this->configuration['settings']) ? $this->configuration['settings'] : [];

    // Attempts to transform the supplied date using the defined input format.
    // DateTimePlus::createFromFormat can throw exceptions, so we need to
    // explicitly check for problems.
    // This is necessary if You are a programmer with different timezone than
    // your project migration.
    try {
      $datetimeplus = new DateTimePlus('now', $timezone, $settings);
      $date = \DateTime::createFromFormat($fromFormat, $value, $datetimeplus->getTimezone());

      if (!$date instanceof \DateTime) {
        throw new \InvalidArgumentException('The date cannot be created from a format.');
      }
      else {
        // Functions that parse date is forgiving, it might create a date that
        // is not exactly a match for the provided value, so test for that by
        // re-creating the date/time formatted string and comparing it to the input. For
        // instance, an input value of '11' using a format of Y (4 digits) gets
        // created as '0011' instead of '2011'.
        if ($date instanceof DateTimePlus) {
          $test_time = $date->format($fromFormat, $settings);
        }
        elseif ($date instanceof \DateTime) {
          $test_time = $date->format($fromFormat);
        }
        $datetimeplus->setTimestamp($date->getTimestamp());

        if ($settings['validate_format'] && $test_time != $value) {
          throw new \UnexpectedValueException('The created date does not match the input value.');
        }
      }
    }
    catch (\InvalidArgumentException $e) {
      throw new MigrateException(sprintf('Format date plugin could not transform "%s" using the format "%s". Error: %s', $value, $fromFormat, $e->getMessage()), $e->getCode(), $e);
    }
    catch (\UnexpectedValueException $e) {
      throw new MigrateException(sprintf('Format date plugin could not transform "%s" using the format "%s". Error: %s', $value, $fromFormat, $e->getMessage()), $e->getCode(), $e);
    }

    return $datetimeplus->format($toFormat);

  }

}
