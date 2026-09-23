<?php
namespace PersonalAccount\Dialog;
use PersonalAccount\Dialog\DialogContract;
use PersonalAccount\Dialog\traits\ConsultationOrComplaint;
class DialogConsultation extends DialogContract
{
  use ConsultationOrComplaint;
  public function __construct($dialog, $closures, $parent)
  {
    parent::__construct($dialog, $closures, $parent);
  }
}