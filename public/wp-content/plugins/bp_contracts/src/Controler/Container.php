<?php
// namespace PersonalAccount\Controler;
// use Exception;
// class Container
// {
//   private $services = []; // Holds all registered services.
//   public function set(string $name, $service)
//   {
//     $this->services[$name] = $service;
//   }
//   public function get(string $name)
//   {
//     if (!isset($this->services[$name])) {
//       throw new Exception("Service $name not found.");
//     }
//     $item = &$this->services[$name];
//     return $item;
//   }

//   public function has(string $name): bool
//   {
//     return isset($this->services[$name]);
//   }
// }