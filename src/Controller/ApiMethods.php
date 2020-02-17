<?php
/* API methods interface
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Interface for supported HTTP Methods
 */
namespace Geekcow\FonyCore\Controller;

interface ApiMethods{
  public function doPOST();
  public function doGET();
  public function doPUT();
  public function doDELETE();
}

?>
