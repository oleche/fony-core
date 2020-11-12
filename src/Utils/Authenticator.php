<?php
/* Authenticator
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Session authenticator interface for multiple authentication methods
 */
namespace Geekcow\FonyCore\Utils;

interface Authenticator{
  public function validateBasicToken();
  public function validateBearerToken();

  public function getScopes();
  public function getUsername();
  public function getClient();
  public function getExpiration();
}
?>
