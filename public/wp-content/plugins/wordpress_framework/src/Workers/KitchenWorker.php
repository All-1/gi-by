<?php
namespace WPFramework\Workers;

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\UserUtilities;

class KitchenWorker
{
  /** @var DBWorker */
  private $dbWorker;
  /** @var DBUtilities */
  private $dbUtilities;
  /** @var SimpleUtilities */
  private $simpleUtilities;
  /** @var DataUtilities */
  private $dataUtilities;
  /** @var UserUtilities */
  private $userUtilities;
  /** @var Container */
  private $Container;
  public function __construct($Container)
  {
    $this->dbWorker = $Container->get('DBWorker');
    $this->dbUtilities = $Container->get('DBUtilities');
    $this->simpleUtilities = $Container->get('SimpleUtilities');
    $this->dataUtilities = $Container->get('DataUtilities');
    $this->userUtilities = $Container->get('UserUtilities');
  }
  private function resolveKitchenName($name, $type)
  {
    if ($type === 'kitchen') {
      return $name;
    } elseif ($type === 'wardrobe') {
      $result = $this->dbWorker->selectSimple('gi_wardrobe', 'name', $name, 'bind_to');
      $result = is_array($result) ? $result[0] : $result;
      return $result;
    } elseif ($type === 'commode') {
      $result = $this->dbWorker->selectSimple('gi_commode', 'name', $name, 'kitchen');
      return $result;
    }
    return $name;
  }

  public function getFacadesMain($nameKitchen, $type = 'kitchen')
  {
    $targetKitchen = $this->resolveKitchenName($nameKitchen, $type);
    error_log(print_r($targetKitchen, true));
    $resultMaterialType = $this->dbWorker->selectCrossTable(
      'FacadesList',
      'FacadesMaterial',
      'type',
      'id',
      'rank_material',
      'kitchens',
      $targetKitchen,
      'type'
    );
    $condition = $this->dbUtilities->createConditionQueryIN('id', $resultMaterialType);
    $condition['OrderBy'] = '`rank_material` ASC';
    $resultMaterial['main'] = $this->dbWorker->selectUni_2('FacadesMaterial', $condition);
    $key = isset($resultMaterial['main'][0]) ? $resultMaterial['main'][0]['id'] : $resultMaterial['main']['id'];
    $resultMain = [];
    if (isset($resultMaterial['main'][0])) {
      $resultMain['main'] = $resultMaterial['main'];
    } else {
      $resultMain['main'][0] = $resultMaterial['main'];
    }
    $facadesList = $this->getFacadesList($nameKitchen, $key, $type);
    $result = array_merge($resultMain, $facadesList);
    // error_log(print_r($result, true));
    return $result;
  }
  public function getFacadesList($nameKitchen, $value, $type = 'kitchen')
  {
    $targetKitchen = $this->resolveKitchenName($nameKitchen, $type);
    // error_log(print_r($targetKitchen, true));
    $condition = $this->dbUtilities->prepareEqualAndLike($value, $targetKitchen, 'type', 'kitchens');
    $condition['OrderBy'] = '`facade_rank` ASC';
    $result = $this->dbWorker->selectUni_2('FacadesList', $condition);
    $result = isset($result[0]) ? $result : [$result];
    return ['list' => $result];
  }
  public function getTabletopsMain()
  {
    $resultMaterialType = $this->dbWorker->selectDistinctColumn('Tabletop', 'made_by');
    $tabletopsList = $this->dbWorker->selectSimple('Tabletop', 'made_by', $resultMaterialType[0]);
    error_log(print_r($tabletopsList, true));
    return [
      'main' => $resultMaterialType,
      'list' => $tabletopsList
    ];
  }
  public function getHandlesMain($nameKitchen, $gola = false, $type = 'kitchen')
  {
    if ($type === 'wardrobe') {
      $handles = $this->dbWorker->selectSimple('gi_wardrobe', 'name', $nameKitchen, 'handles');

    } else {
      // For kitchen and commode (which uses kitchen handles)
      $targetKitchen = $this->resolveKitchenName($nameKitchen, $type);
      $handles = $this->dbWorker->selectSimple('gi_kitchen', 'name', $targetKitchen, 'handles');
    }

    // Handle empty or null handles
    if (empty($handles)) {
      return ['list' => []];
    }


    $handlesArray = explode(',', $handles);
    // Clean array - remove empty values
    $handlesArray = array_filter($handlesArray, function ($value) {
      return !empty(trim($value)); });

    if (empty($handlesArray)) {
      return ['list' => []];
    }

    $result = [];
    if ($gola) {
      foreach ($handlesArray as $handle) {
        $handle = trim($handle);
        if (strpos($handle, 'Gola') !== false) {
          $result[] = $handle;
        }
      }
    } else {
      foreach ($handlesArray as $handle) {
        $handle = trim($handle);
        if (!empty($handle) && strpos($handle, 'Gola') === false) {
          $result[] = $handle;
        }
      }
    }

    if (empty($result)) {
      return ['list' => []];
    }

    $condition = $this->dbUtilities->createConditionQueryIN('model_name', $result);
    $condition['OrderBy'] = '`rank_handle` ASC';
    $result = $this->dbWorker->selectUni_2('gi_handle', $condition);
    $result = isset($result[0]) ? $result : [$result];
    return ['list' => $result];
  }

  public function getFittingsMain($nameKitchen, $type = 'kitchen')
  {
    if ($type === 'wardrobe') {
      $fittings = $this->dbWorker->selectSimple('gi_wardrobe', 'name', $nameKitchen, 'fittings');
      $fittings = is_array($fittings) ? $fittings[0] : $fittings;
      // Handle empty or null fittings
      if (empty($fittings)) {
        return ['list' => []];
      }

      $fittingsArray = explode(',', $fittings);
      // Clean array - remove empty values and trim whitespace
      $fittingsArray = array_filter($fittingsArray, function ($value) {
        return !empty(trim($value)); });
      $fittingsArray = array_map('trim', $fittingsArray);

      if (empty($fittingsArray)) {
        return ['list' => []];
      }

      $condition = $this->dbUtilities->createConditionQueryIN('model_name', $fittingsArray);
      $condition['OrderBy'] = '`fitting_rank` ASC';
      $result = $this->dbWorker->selectUni_2('gi_wardrobe_fittings', $condition);
      return ['list' => $result];
    }
    return ['list' => []];
  }

  public function switchWhereLookFor($where, $value, $nameKitchen, $type = 'kitchen')
  {
    $result = [];
    if ($where === 'main') {
      if ($value === 'facades') {
        $result = $this->getFacadesMain($nameKitchen, $type);
      } else if ($value === 'tabletops') {
        $result = $this->getTabletopsMain();
      } else if ($value === 'handles') {
        $result = $this->getHandlesMain($nameKitchen, false, $type);
      } else if ($value === 'gola') {
        $result = $this->getHandlesMain($nameKitchen, true, $type);
      } else if ($value === 'fittings') {
        $result = $this->getFittingsMain($nameKitchen, $type);
      }
    } else if ($where === 'facades-list') {
      error_log(print_r($value, true));
      error_log(print_r($nameKitchen, true));
      error_log(print_r($type, true));
      error_log(print_r($where, true));
      $result = $this->getFacadesList($nameKitchen, $value, $type);
    } else if ($where === 'tabletops-list') {
      $result = $this->dbWorker->selectSimple('Tabletop', 'made_by', $value);
      $result = ['list' => $result];
    }
    return $result;
  }

}

