<?php
namespace PersonalAccount\Utilities;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\Utilit;

class DBUtilities
{
  // use DependencyInjections;
  /** @var Container */
  protected $Container;
  /** @var SimpleUtilities */
  protected $SimpleUtilities;
  use Utilit;
  public function __construct($Container)
  {
    $this->Container = &$Container;
    $this->SimpleUtilities = $this->Container->get('SimpleUtilities');
  }
  private function preparePlaceholderValue($value)
  {
    // error_log(print_r($value, true));
    if (is_null($value)) {
      // NULL
      return 'NULL';
    } elseif (is_bool($value)) {
      // Булевые значения
      return '%d'; // Приведение true/false к 1/0
    } elseif (is_int($value)) {
      // Целые числа
      return '%d';
    } elseif (is_float($value)) {
      // Числа с плавающей точкой
      return '%f';
    } elseif (is_array($value)) {
      // Массивы нельзя напрямую вставить — конвертируем в JSON
      return '%s';
    } elseif (is_object($value)) {
      // Объекты также конвертируем в JSON
      return '%s';
    } elseif (strtotime($value) !== false) {
      // Дата или время
      return '%s';
    } elseif (is_string($value)) {
      // Обычная строка
      return '%s';
    } else {
      // На случай неизвестного типа
      error_log('Error: DBWorker->preparePlaceholderValue');
    }
  }
  public function convertDataFromDB($data)
  {
    if (!empty($data)) {
      $result = [];
      foreach ($data as $i => $row) {
        foreach ($row as $property => $value) {
          $key = $this->SimpleUtilities->snakeToCamel($property);
          $value = is_numeric($value) ? (strpos($value, '.') !== false ? floatval($value) : intval($value)) : $value;
          $result[$i][$key] = $value;
        }
      }
      return $result;
    }
  }
  public function whatExist($data)
  {
    if (!empty($data) && is_array($data)) {
      if (count($data) === 1 && count($data[0]) === 1) {
        $singleKey = key($data[0]);
        $result = $data[0][$singleKey];
      } elseif (count($data) > 1 && count($data[0]) === 1) {
        $temporary = [];
        foreach ($data as $item) {
          if (is_array($item) && count($item) === 1) {
            $singleKey = key($item);
            $temporary[] = $item[$singleKey];
          }
        }
        $result = $temporary;
      }
      return $result;
    }
  }
  public function prepareInsert(array $rows, $batchSize = 1000)
  {
    $batches = [];
    // Process rows in chunks of the given batch size.
    foreach (array_chunk($rows, $batchSize) as $batch) {
      $sqlSegments = [];
      $values = [];
      foreach ($batch as $row) {
        $placeholders = [];
        foreach ($row as $value) {
          $ph = $this->preparePlaceholderValue($value);
          if ($ph === 'NULL') {
            // Insert the literal NULL directly into the SQL segment.
            $placeholders[] = 'NULL';
          } else {
            // Otherwise add the placeholder and record the value.
            $placeholders[] = $ph;
            $values[] = $value;
          }
        }
        // Build a single SQL segment for the row.
        $sqlSegments[] = '(' . implode(', ', $placeholders) . ')';
      }
      // Combine segments for the batch into one SQL string.
      $batches[] = [
        'sql' => implode(', ', $sqlSegments),
        'values' => $values,
      ];
    }
    return $batches;
  }
  public function prepareWhereForUpdate($whereValue, $whereColumn)
  {
    if (is_array($whereValue)) {
      $whereSql = " WHERE `$whereColumn` IN (" . implode(", ", array_fill(0, count($whereValue), '%s')) . ")";
    } else {
      $whereSql = " WHERE `$whereColumn` = %s";
    }
    return $whereSql;
  }
  public function prepareQueryForUpdate($whereValue, $setValue)
  {
    // Подготовка данных для запроса
    if (is_array($whereValue)) {
      $queryParams = array_merge([$setValue], $whereValue);
    } else {
      $queryParams = [$setValue, $whereValue];
    }
    return $queryParams;
  }
  public function prepareInsertTrust(array $rows, $batchSize = 1000)
  {
    $sqlBatches = [];

    foreach (array_chunk($rows, $batchSize) as $batch) {
      $sqlSegments = [];

      foreach ($batch as $row) {
        // Экранирование значений
        $escapedValues = [];
        foreach ($row as $line) {
          $escapedValues[] = "'$line'";
        }
        // Формирование строки без ключей, только VALUES
        $sqlSegments[] = "(" . implode(", ", $escapedValues) . ")";
      }

      // Объединяем строки в пакет
      $sqlBatches[] = implode(", ", $sqlSegments);
    }
    return $sqlBatches;
  }
  public function createConditionQueryIN($column, $param)
  {
    $setPlaceholders = [];
    $setValue = [];
    $param = is_array($param) ? $param : [$param];
    foreach ($param as $value) {
      $placeholder = $this->preparePlaceholderValue($value);
      $setPlaceholders[] = $placeholder;
      $setValue[] = $value;
    }
    $placeholders = implode(',', $setPlaceholders);
    $whereSql = "`$column` IN ($placeholders)";
    return [
      'sql' => $whereSql,
      'values' => $setValue
    ];
  }
  public function preparenSingleOperSepar($columns, $values, $operator, $separator)
  {
    $valuesArr = is_array($values) ? $values : [$values];
    $columnsNew = is_array($columns) ? $columns : [$columns];
    $temporaryPlaceholders = [];
    $temporaryValues = [];
    foreach ($valuesArr as $indexValue => $item) {
      $setParts = [];
      foreach ($columnsNew as $indexColumn => $key) {
        if ($indexValue === $indexColumn) {
          $placeholder = $this->preparePlaceholderValue($item);
          $setParts[] = "`$key` $operator $placeholder";
        }
      }
      $temporaryPlaceholders[] = implode($separator, $setParts);
      $temporaryValues[] = $item;
    }
    $resultPlaceholders = implode($separator, $temporaryPlaceholders);
    $result = [
      'sql' => $resultPlaceholders,
      'values' => $temporaryValues,
    ];
    return $result;
  }
  public function prepareColumnEqual($firstColumn, $secondColumn)
  {
    $whereSql = "`$firstColumn` = `$secondColumn`";
    return [
      'sql' => $whereSql,
      'values' => []
    ];
  }
  public function prepareInAndEqual($valueIn, $valueEqual, $columnIn, $columnEqual)
  {
    $queryWhereIn = $this->createConditionQueryIN($columnIn, $valueIn);
    $queryWhereEqual = $this->preparenSingleOperSepar($columnEqual, $valueEqual, ' = ', '');

    $whereSql = $queryWhereIn['sql'] . ' AND ' . $queryWhereEqual['sql'];
    $values = $this->SimpleUtilities->mergeArrays($queryWhereIn['values'], $queryWhereEqual['values']);
    $result = [
      'sql' => $whereSql,
      'values' => $values
    ];
    return $result;
  }
  public function prepareEqualAndEqual($firstValue, $secondValue, $firstColumn, $secondColumn)
  {
    $firstQuery = $this->preparenSingleOperSepar($firstColumn, $firstValue, ' = ', '');
    $secondQuery = $this->preparenSingleOperSepar($secondColumn, $secondValue, ' = ', '');
    $result = $this->mergeCondition($firstQuery, $secondQuery, ' AND ');
    return $result;
  }
  public function prepareEqualAndEqualColumn($firstValue, $secondValue, $firstColumn, $secondColumn)
  {
    $firstQuery = $this->preparenSingleOperSepar($firstColumn, $firstValue, ' = ', '');
    $secondQuery = $this->prepareColumnEqual($secondColumn, $secondValue);
    $result = $this->mergeCondition($firstQuery, $secondQuery, ' AND ');
    return $result;
  }
  public function prepareEqualAndLike($firstValue, $secondValue, $firstColumn, $secondColumn)
  {
    $firstQuery = $this->preparenSingleOperSepar($firstColumn, $firstValue, ' = ', '');
    $secondQuery = $this->preparenSingleOperSepar($secondColumn, '%' . $secondValue . '%', ' LIKE ', '');
    $result = $this->mergeCondition($firstQuery, $secondQuery, ' AND ');
    return $result;
  }
  public function mergeCondition($firstQuery, $secondQuery, $delimiter)
  {
    $whereSql = $firstQuery['sql'] . $delimiter . $secondQuery['sql'];
    $values = $this->SimpleUtilities->mergeArrays($firstQuery['values'], $secondQuery['values']);
    $result = [
      'sql' => $whereSql,
      'values' => $values
    ];
    return $result;
  }
  public function selectColumns($what)
  {

    $columns = is_string($what)
      ? $what
      : "`" . implode('`, `', $what) . "`"; // Без кавычек для выбора всех столбцов
    return $columns;
  }
  public function prepareBetween($column, $dateFrom, $dateTo)
  {
    $condition = "`$column` BETWEEN %s AND %s";
    $result = [
      'sql' => $condition,
      'values' => [$dateFrom, $dateTo]
    ];
    return $result;
  }
  public function prepareSelect(array $columns, array $tables)
  {

  }
  public function prepareFrom(array $tables, array $joins, array $on)
  {
    $from = '';
    foreach ($tables as $key => $table) {

      $from .= "`$joins[$key]` ON `$joins[$key]`.$joins[$key] = `$table`.$joins[$key]";
    }
  }
}