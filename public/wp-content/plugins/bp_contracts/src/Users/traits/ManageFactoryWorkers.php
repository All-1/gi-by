<?php
namespace PersonalAccount\Users\traits;
trait ManageFactoryWorkers
{
  public function savePoints($data)
  {
    $idUser = intval($data->idUser);

    $idPoints = [];
    foreach ($data->idPoints as $idPoint) {
      $idPoints[] = intval($idPoint);
    }
    if (!empty($idPoints)) {
      $this->DBWorker->updateDBU('gi_new_points', 'id_point', $idPoints, 'id_manager', $idUser);
    } else {
      error_log('No points to update.');
    }
  }
  public function savePointsNew($data)
  {
    // $this->CatcherBugs->convPrintLog($data, 'savePointsNew', '$data');
    foreach ($data as $item) {
      $idManager = intval($item->idManager);
      $idPoint = intval($item->idPoint);
      $this->DBWorker->updateDBU('gi_new_points', 'id_point', $idPoint, 'id_manager', $idManager);
    }
  }
  //Управление точками сотрудников фабрики и всеми 
  //Управление всеми сотрудниками фабрики.
}