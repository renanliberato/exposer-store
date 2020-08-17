<?php

namespace RenanLiberato\ExposerStore\Store;

use RenanLiberato\ExposerStore\Persistors\PersistorInterface;
use RenanLiberato\ExposerStore\Middlewares\AbstractMiddleware;

abstract class Store
{
    /**
     * @var array
     */
    public $initialState;

    /**
     * @var callable
     */
    private $mainReducer;

    /**
     * @var array
     */
    private $state;

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @var PersistorInterface
     */
    private $persistor;

    /**
     * @param array $state
     * @param array $reducers
     * @param array $middlewares
     */
    public function __construct($state, $reducers, $middlewares = [])
    {
        $this->initialState = $state;
        $this->state = $state;
        $this->mainReducer = $this->combineReducers($reducers);
        $this->middlewares = $middlewares;
        
        $this->applyMiddlewares();

        $this->action(['type' => 'INITIALIZE']);
    }

    public function setPersistor(PersistorInterface $persistor)
    {
        $this->persistor = $persistor;
    }

    private function combineReducers($reducers = [])
    {
        return function ($action) use ($reducers) {
            $state = $this->getState();

            foreach ($state as $key => $value) {
                if (isset($reducers[$key])) {
                    $state[$key] = $reducers[$key]($value, $action);
                }
            }

            return $state;
        };
    }

    /**
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = array_merge($this->state, $state);
    }

    public function action($action)
    {
        $this->middlewares[0]->process($action);
        $this->state = ($this->mainReducer)($action);
    }

    public function getPersistedState()
    {
        $this->setState($this->persistor->getPersistedState());
    }

    public function persistState()
    {
        $this->persistor->persistState($this->getState());
    }

    public function applyMiddlewares()
    {
        $defaultNext = new DefaultMiddleware($this);

        $i = 0;
        if ($this->middlewares == null || count($this->middlewares) == 0) {
            return [$defaultNext];
        }

        if (count($this->middlewares) == 1) {
            $theMiddleware = new $this->middlewares[0]($this);
            $theMiddleware->setNext($defaultNext);

            return [$theMiddleware];
        }

        $middlewaresLength = count($this->middlewares);
        
        $this->middlewares = array_map(function ($middleware) {
            return (new $middleware($this));
        }, $this->middlewares);
        
        $i = count($this->middlewares) - 1;
        
        while ($i >= 0) {
            if ($i == count($this->middlewares) - 1) {
                $this->middlewares[$i]->setNext($defaultNext);
            } else {
                $this->middlewares[$i]->setNext($this->middlewares[$i + 1]);
            }

            $i--;
        }
    }
}

class DefaultMiddleware extends AbstractMiddleware
{
    public function process($action)
    {
        return $action;
    }
}