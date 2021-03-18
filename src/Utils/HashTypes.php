<?php

namespace Geekcow\FonyCore\Utils;

abstract class HashTypes
{
    public const MD5 = '/^[a-f0-9]{32}$/i';
    public const SHA1 = '/^[0-9a-f]{40}$/i';
}
