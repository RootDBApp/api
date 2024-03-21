<?php

namespace App\Services;

use App\Interfaces\ConnectorInterface;
use App\Models\ConfConnector;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Boolean;
use Str;

class ConnectorService
{
    /**
     * Simply initialize a connector. (not a singleton)
     *
     * @param ConfConnector $confConnector
     * @return bool|Connection
     */
    public function getConnection(ConfConnector $confConnector): bool|Connection
    {
        $connectorService = 'App\Services\\' . Str::studly($confConnector->connectorDatabase->name . '_connector_service');
        /** @var CommonConnectorService $connectorServiceInstance */
        $connectorServiceInstance = new $connectorService();
        if ($connectorServiceInstance->initialized === false) {

            $connectorServiceInstance->init($confConnector);
            return $connectorServiceInstance->getConnection();
        }

        return false;
    }

    /**
     *
     * If $confConnector != singleton's $confConnector, then we reset the singleton.
     *
     * @param ConfConnector $confConnector
     * @param bool $reset
     * @return ConnectorInterface
     */
    public function getInstance(ConfConnector $confConnector, bool $reset = false): ConnectorInterface
    {
        // If we explicitly ask for a reset.
        if ($reset === false) {

            return $this->_getSingleton($confConnector, true);
        } else {

            if ($confConnector->id !== $this->_getSingleton($confConnector)->confConnector->id) {

                Log::debug('[ConnectorService::getInstance] asked ConfConnector != current ConfConnector', [$confConnector->id, $this->_getSingleton($confConnector)->confConnector->id]);
                $reset = true;
            }
        }

        return $this->_getSingleton($confConnector, $reset);
    }

    private function _getSingleton(ConfConnector $confConnector, bool $reset = false): ConnectorInterface|Boolean
    {
        $connectorService = 'App\Services\\' . Str::studly($confConnector->connectorDatabase->name . '_connector_service');
        if (class_exists($connectorService)) {
            try {

                /** @var CommonConnectorService $connectorServiceInstance */
                $connectorServiceInstance = app()->make($connectorService);
                if ($connectorServiceInstance->initialized === false || $reset === true) {

                    $connectorServiceInstance->init($confConnector);
                }

                return $connectorServiceInstance;

            } catch (BindingResolutionException $e) {

                Log::debug($e);
            }
        }

        return false;
    }
}
