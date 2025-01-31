<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Client;

use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;

class ClientHandler
{
    /**
     * @var array<string, ClientInterface>
     */
    private array $clients = [];

    /**
     * @param iterable<ClientInterface> $clients
     */
    public function addClients(iterable $clients): void
    {
        foreach ($clients as $client) {
            $this->clients[$client::getProxyValue()] = $client;
        }
    }

    /**
     * @throws \Exception
     */
    public function getClient(SourceProxy $proxy): ClientInterface
    {
        return $this->clients[$proxy->value] ?? throw new \Exception('Cannot found Client for proxy '.$proxy->value);
    }
}
