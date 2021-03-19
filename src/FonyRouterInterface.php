<?php

namespace Geekcow\FonyCore;

interface FonyRouterInterface
{
    public function prestageEndpoints($endpoint, $request);
}