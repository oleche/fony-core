<?php

/* Authenticator
 * Developed by OSCAR LECHE
 * V.1.0
 * DESCRIPTION: Session authenticator interface for multiple authentication methods
 */

namespace Geekcow\FonyCore\Utils;

interface AuthenticatorInterface
{
    public function validateBasicToken($token);

    public function validateBearerToken($token);

    public function getScopes();

    public function getUsername();

    public function getClientId();

    public function getExpiration();
}
