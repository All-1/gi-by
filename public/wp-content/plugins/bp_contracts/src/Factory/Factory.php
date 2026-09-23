<?php
namespace PersonalAccount\Factory;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Users\Distributor;
use PersonalAccount\Users\FreeDealer;
use PersonalAccount\Users\Dealer;
use PersonalAccount\Users\Contractor;
use PersonalAccount\Users\ContractsWorker;
use PersonalAccount\Users\FactoryWorker;
use PersonalAccount\Users\Bookkeeper;
use PersonalAccount\Users\ShipmentManager;
use PersonalAccount\Users\Admin;
use PersonalAccount\Users\SalesManager;
use PersonalAccount\ObjectRelationship\Contract;
use PersonalAccount\ObjectRelationship\Invoice;
use PersonalAccount\ObjectRelationship\Shipment;
use PersonalAccount\Dialog\DialogConsultation;
use PersonalAccount\Dialog\DialogOrder;
use PersonalAccount\Dialog\DialogComplaint;
use PersonalAccount\Dialog\DialogShipment;
use PersonalAccount\Dialog\DialogInvoice;
use PersonalAccount\Dialog\DialogContract;
use PersonalAccount\Dialog\Message;
use PersonalAccount\Workers\CatcherBugs;
class Factory {
  /** @var Container */
  private $сontainer;
  /** @var UserUtilities */
  private $userUtilities;
  /** @var CatcherBugs */
  private $catcherBugs;
  public function __construct($сontainer)
  {
    $this->сontainer = &$сontainer;
    $this->userUtilities = $this->сontainer->get('UserUtilities');
    $this->catcherBugs = $this->сontainer->get('CatcherBugs');
  }
  public function createUser($idUser, $closures)
  {
    $currentUser = '';
    $userRole = $this->userUtilities->getUserRole($idUser);
    // $this->catcherBugs->convPrintLog($userRole, 'Factory', '$userRole');
    switch ($userRole) {
      case 'distributor':
        $currentUser = new Distributor($idUser, $closures);
        break;
      case 'free_dealer':
        $currentUser = new FreeDealer($idUser, $closures);
        break;
      case 'dealer':
        $currentUser = new Dealer($idUser, $closures);
        break;
      case 'designer_dealer':
        $currentUser = new Contractor($idUser, $closures);
        break;
      case 'consultant':
      case 'manager':
      case 'complaint_handler':
        $currentUser = new ContractsWorker($idUser, $closures);
        break;
      case 'specialist':
        $currentUser = new FactoryWorker($idUser, $closures);
        break;
      case 'bookkeeper':
        $currentUser = new Bookkeeper($idUser, $closures);
        break;
      case 'shipment_manager':
        $currentUser = new ShipmentManager($idUser, $closures);
        break;
      case 'administrator':
        $currentUser = new Admin($idUser, $closures);
        break;
      case 'sales_manager':
        $currentUser = new SalesManager($idUser, $closures);
        break;
    }
    return $currentUser;
  }
  public function instanciateComponents($nameObject){
    $object = new $nameObject($this->сontainer);
    $this->сontainer->set($nameObject, $object);
  }
  public function createDependentObjects($newObject, $closures, $paramDependentObjects, $parent = null)
  {
    $dependentObjects = [];
    // $paramDependentObjects = $single ? [$paramDependentObjects] : $paramDependentObjects;
    // $this->catcherBugs->convPrintLog($paramDependentObjects, 'Utilit', 'allPointsData');
    if ($closures && !empty($paramDependentObjects)) {
      foreach ($paramDependentObjects as $params) {
        switch ($newObject) {
          case 'Contract':
            $dependentObjects[] = new Contract($params, $closures, $parent);
            break;
          case 'Invoice':
            $dependentObjects[] = new Invoice($params, $closures, $parent);
            break;
          case 'Shipment':
            $dependentObjects[] = new Shipment($params, $closures, $parent);
            break;
          case 'DialogConsultation':
            $dependentObjects[] = new DialogConsultation($params, $closures, $parent);
            break;
          case 'DialogOrder':
            $dependentObjects[] = new DialogOrder($params, $closures, $parent);
            break;
          case 'DialogComplaint':
            $dependentObjects[] = new DialogComplaint($params, $closures, $parent);
            break;
          case 'DialogShipment':
            $dependentObjects[] = new DialogShipment($params, $closures, $parent);
            break;
          case 'DialogInvoice':
            $dependentObjects[] = new DialogInvoice($params, $closures, $parent);
            break;
          case 'DialogContract':
            $dependentObjects[] = new DialogContract($params, $closures, $parent);
            break;
          case 'Message':
            $dependentObjects[] = new Message($params, $closures, $parent);
            break;
            
            
        }
      }
    }
    return $dependentObjects;
  }
  public function createDependentObjects_2($newObject, $closures, $paramDependentObjects)
  {
    $dependentObjects = [];
    $check = is_array($paramDependentObjects) && !empty($paramDependentObjects) && !isset($paramDependentObjects[0]);
    $paramDependentObjects = $check ? [$paramDependentObjects] : $paramDependentObjects;
    // $this->catcherBugs->convPrintLog($paramDependentObjects, 'Utilit', 'allPointsData');
    if ($closures && !empty($paramDependentObjects)) {
      foreach ($paramDependentObjects as $params) {
        // $this->catcherBugs->convPrintLog($params, 'Utilit', 'params');
        $dependentObjects[] = new $newObject($params, $closures);
      }
    }
    return $dependentObjects;
  }
}