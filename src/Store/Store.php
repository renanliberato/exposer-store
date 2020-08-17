<?php

namespace RenanLiberato\ExposerStore\Store;

use RenanLiberato\ExposerStore\Persistors\PersistorInterface;

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
    public function __construct($state, $reducers, $middlewares)
    {
        $this->initialState = $state;
        $this->state = $state;
        $this->mainReducer = $this->combineReducers($reducers);
        $this->middlewares = $middlewares;
        $this->middlewares = $this->applyMiddlewares();

        $this->action(['type' => 'INITIALIZE']);
    }

    public function setPersistor(PersistorInterface $persistor)
    {
        $this->persistor = $persistor;
    }

    private function combineReducers($reducers = [])
    {
        return function ($action) use ($reducers) {
            return array_reduce($reducers, function ($state, $reducer) use ($action) {
                $newState = $reducer($state, $action);
                return $newState;
            }, $this->getState());
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
        ($this->middlewares[0])($action);
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
        $defaultNext = function ($action) {
            return $action;
        };

        $i = 0;
        if ($this->middlewares == null || count($this->middlewares) == 0) {
            return [$defaultNext];
        }

        if (count($this->middlewares) == 1) {
            return [
                (new $this->middlewares[0]($this))($defaultNext)
            ];
        }

        $middlewaresLength = count($this->middlewares);
        
        $this->middlewares = array_map(function ($middleware) {
            return (new $middleware($this));
        }, $this->middlewares);
        
        $i = count($this->middlewares) - 1;
        
        $middlewaresWithNext = [];
        while ($i >= 0) {
            if ($i == count($this->middlewares) - 1) {
                $middlewaresWithNext[$i] = $this->middlewares[$i]($defaultNext);
            } else {
                $middlewaresWithNext[$i] = $this->middlewares[$i]($middlewaresWithNext[$i + 1]);
            }

            $i--;
        }

        return $middlewaresWithNext;
    }
}
