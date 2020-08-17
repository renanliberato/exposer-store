<?php

namespace RenanLiberato\ExposerStore\Store;

class ProcessAction
{
    private $store;
    
    private $onAction;

    public function __construct(Store $store, $onAction)
    {
        $this->store = $store;
        $this->onAction = $onAction;
    }

    public function __invoke($action)
    {
        $this->store->action($action);
    
        $this->store->persistState();
    
        ($this->onAction)();
    }
}