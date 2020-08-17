<?php

namespace RenanLiberato\Exposer\Persistors;

interface PersistorInterface
{
    /**
     * @param array $state
     * @return void
     */
    function persistState($state);

    /**
     * @return array
     */
    function getPersistedState();
}