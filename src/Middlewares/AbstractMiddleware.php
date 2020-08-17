<?php

namespace RenanLiberato\ExposerStore\Middlewares;

use RenanLiberato\ExposerStore\Store\Store;

abstract class AbstractMiddleware
{
    protected $store;

    protected $next;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public abstract function process($action);
}
