<?php
namespace PersonalAccount\Users\interfaces;
interface UserMainInterface
{
  public function updateAfterBindUser();
  public function bindOnMyOwn();
  public function updateStatus();
} 