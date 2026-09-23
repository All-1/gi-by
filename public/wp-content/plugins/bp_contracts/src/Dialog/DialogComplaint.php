<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Dialog\DialogContract;
class DialogComplaint extends DialogContract
{
  public function __construct($dialog, $closures, $parent)
  {
    parent::__construct($dialog, $closures, $parent);
  }
  // idOrder - При создании диалога присваивается пользователем соответствует данный dialogComplaint 
  // getAllOrders При создании поиск по таблице заказов order_numbers которые содержат sn данного контракта.
}