<?php
namespace PersonalAccount\Utilities;

class SimpleUtilities
{
  // use DependencyInjections;
  public function __construct($ServicesContainer)
  {
  }
  public function collectLeafs(...$args)
  {
    // error_log('collectLeafs');
    $leafs = [];
    foreach ($args as &$array) {
      if (!empty($array)) {
        $temporaryStorage = is_array($array) && !empty($array) ? $array : [$array];
        $leafs = array_merge($leafs, $temporaryStorage);
      } else {
        error_log('array is empty');
      }
    }
    return $leafs;
  }
  public function snakeToCamel($string)
  {
    return lcfirst(str_replace('_', '', ucwords($string, '_')));
  }
  public function previousDateTime($days = 0)
  {
    date_default_timezone_set('Europe/Moscow');
    return date('Y-m-d H:i:s', strtotime("-$days days"));
  }
  public function currentTime()
  {
    date_default_timezone_set('Europe/Moscow');
    return date('Y-m-d H:i:s');
  }
  public function combineValues(...$args)
  {
    $result = [];
    foreach ($args as $arg) {
      if (is_array($arg) && !empty($arg)) {
        $result = array_merge($result, $arg);
      } elseif (!empty($arg)) {
        $result[] = $arg;
      }
    }
    $result = array_unique($result);
    return $result;
  }
  public function mergeArrays($array1, $array2)
  {
    $array1 = $this->toArray($array1);
    $array2 = $this->toArray($array2);
    if (!empty($array1) && !empty($array2)) {
      return array_merge($array1, $array2);
    } else if (empty($array1) && !empty($array2)) {
      return $array2;
    } else if (!empty($array1) && empty($array2)) {
      return $array1;
    }
    return null;
  }
  public function diffTime($time, $previousTime)
  {
    $timeTimeStamp = convert_timestamp_index($time);
    $previousTimeStamp = convert_timestamp_index($previousTime);
    $diff = $timeTimeStamp - $previousTimeStamp;
    return $diff;
  }
  function checkReturn($value)
  {
    if (!empty($value)) {
      return $value;
    } else {
      // $this->CatcherBugs->convPrintLog($value, $nameMethod, $what);
      return null;
    }
  }
  public function toArray($value)
  {
    return is_array($value) ? $value : [$value];
  }
  public function sortedByAlphabet($array, $property)
  {
    // Sort the array based on the specified property
    usort($array, function ($a, $b) use ($property) {
      $valueA = isset($a[$property]) ? $a[$property] : '';
      $valueB = isset($b[$property]) ? $b[$property] : '';

      return strcmp($valueA, $valueB);
    });

    return $array;
  }
  public function formatNumber($number)
  {
    // Преобразуем число в строку и дополняем слева нулями до длины 6 символов
    return str_pad($number, 6, '0', STR_PAD_LEFT);
  }
  public function toTimestamp($value)
  {
    if (is_int($value)) {
      return $value;
    }

    if (is_numeric($value)) {
      return intval($value);
    }

    if (empty($value) || $value === '0000-00-00 00:00:00') {
      return 0;
    }

    $timestamp = strtotime($value);
    return $timestamp !== false ? $timestamp : 0;
  }
  public function diffTimeNew($time, $previousTime)
  {
    $timeTimeStamp = $this->toTimestamp($time);
    $previousTimeStamp = $this->toTimestamp($previousTime);

    if ($timeTimeStamp <= 0 || $previousTimeStamp <= 0) {
      return 0;
    }

    $diff = $timeTimeStamp - $previousTimeStamp;
    return $diff;
  }
  public function diffWorkHours($start, $end)
  {
    $start = $this->toTimestamp($start);
    $end = $this->toTimestamp($end);

    if ($start <= 0 || $end <= 0) {
      return 0;
    }

    if ($start > $end) {
      [$start, $end] = [$end, $start];
    }

    $workStart = '08:30:00';
    $workEnd = '16:30:00';

    $total = 0;
    $current = $start;

    while ($current < $end) {

      $day = date('Y-m-d', $current);

      // пропускаем выходные
      if (in_array(date('N', $current), [6, 7])) {
        $current = strtotime($day . ' +1 day');
        continue;
      }

      $dayStart = strtotime("$day $workStart");
      $dayEnd = strtotime("$day $workEnd");

      // пересечение интервалов
      $from = max($current, $dayStart);
      $to = min($end, $dayEnd);

      if ($from < $to) {
        $total += ($to - $from);
      }

      // переходим к следующему дню
      $current = strtotime($day . ' +1 day');
    }

    return $total;
  }
}