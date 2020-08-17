<?php

namespace RenanLiberato\ExposerStore\Store;

abstract class Store
{
    public $initialState;

    private $mainReducer;

    /**
     * @var array
     */
    private $state;

    private $middlewares;

    private $persistFunction;

    public function __construct($state, $reducers, $middlewares)
    {
        $this->initialState = $state;
        $this->state = $state;
        $this->mainReducer = $this->combineReducers($reducers);
        $this->middlewares = $middlewares;
        $this->middlewares = $this->applyMiddlewares();
        $this->persistFunction = function ($state) {
            return $state;
        };

        $this->action(['type' => 'INITIALIZE']);
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
        $this->state = $state;
    }

    public function action($action)
    {
        ($this->middlewares[0])($action);
        $this->state = ($this->mainReducer)($action);
    }

    public abstract function getPersistedState();

    public function setPersistFunction($persistFunction)
    {
        $this->persistFunction = $persistFunction;
    }

    public abstract function persistState();

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
