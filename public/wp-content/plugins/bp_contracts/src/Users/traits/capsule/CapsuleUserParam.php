<?php
namespace PersonalAccount\Users\traits\capsule;
trait CapsuleUserParam
{
  abstract protected function setProperty($name, $value);
  abstract protected function getProperty($name);
  public function getUserId()
  {
      return $this->getProperty('userId');
  }
  protected function getUserRole()
  {
    return $this->getProperty('userRole');
  }
  protected function getFirstName()
  {
    return $this->getProperty('firstName');
  }
  protected function getLastName()
  {
    return $this->getProperty('lastName');
  }
  protected function getIdPoint()
  {
    return $this->getProperty('idPoint');
  }
  protected function getStatusActivity()
  {
    return $this->getProperty('statusActivity');
  }
  // protected function getIdReplacement()
  // {
  //   return $this->getProperty('idReplacement');
  // }
  protected function getUnreadedMessages()
  {
    return $this->getProperty('unreadedMessages');
  }
  protected function getUnreadedDialogues()
  {
    return $this->getProperty('unreadedDialogues');
  }
  protected function getPotentialParticipant()
  {
    return $this->getProperty('potentialParticipant');
  }
  protected function getUserOrders()
  {
    return $this->getProperty('orders');
  }
  protected function getIdWebsocket()
  {
    return $this->getProperty('idWebsocket');
  }
  // Setters
  protected function setUserId($idUser)
  {
    $this->setProperty('userId', $idUser);
  }
  protected function setUserRole($userRole)
  {
    $this->setProperty('userRole', $userRole);
  }
  protected function setFirstName($firstName)
  {
    $this->setProperty('firstName', $firstName);
  }
  protected function setLastName($lastName)
  {
    $this->setProperty('lastName', $lastName);
  }
  protected function setIdPoint($idPoint)
  {
    $this->setProperty('idPoint', $idPoint);
  }
  protected function setStatusActivity($statusActivity)
  {
    $this->setProperty('statusActivity', $statusActivity);
  }
  protected function setIdReplacement($idReplacement)
  {
    $this->setProperty('idReplacement', $idReplacement);
  }
  protected function setUnreadedMessages($unreadedMessages)
  {
    $this->setProperty('unreadedMessages', $unreadedMessages);
  }
  protected function setUnreadedDialogues($unreadedDialogues)
  {
    $this->setProperty('unreadedDialogues', $unreadedDialogues);
  }

  protected function setPotentialParticipant($potentialParticipant)
  {
    $this->setProperty('potentialParticipant', $potentialParticipant);
  }
  protected function setUserOrders($orders)
  {
    $this->setProperty('orders', $orders);
  }
  protected function setUsersControler($usersControler)
  {
    $this->setProperty('usersControler', $usersControler);
  }
  protected function getControlerObjectsRelationship()
  {
    return $this->getProperty('ControlerObjectsRelationship');
  }

}