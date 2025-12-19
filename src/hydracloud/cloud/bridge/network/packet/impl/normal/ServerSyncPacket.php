<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\api\object\server\CloudServer;
use hydracloud\cloud\bridge\api\registry\Registry;

use hydracloud\cloud\bridge\event\server\CloudServerRegisterEvent;
use hydracloud\cloud\bridge\event\server\CloudServerUnregisterEvent;
use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;

class ServerSyncPacket extends CloudPacket {

    public function __construct(
        private ?CloudServer $server = null,
        private bool $removal = false
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeServer($this->server);
        $packetData->write($this->removal);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->server = $packetData->readServer();
        $this->removal = $packetData->readBool();
    }

    public function getServer(): ?CloudServer {
        return $this->server;
    }

    public function isRemoval(): bool {
        return $this->removal;
    }

    public function handle(): void {
        if (CloudAPI::servers()->get($this->server->getName()) === null) {
            if (!$this->removal) {
                Registry::registerServer($this->server);
                $ev = new CloudServerRegisterEvent($this->server);
                $ev->call();
            }
        } else {
            if ($this->removal) {
                Registry::unregisterServer($this->server->getName());
                $ev = new CloudServerUnregisterEvent($this->server);
                $ev->call();
            } else Registry::updateServer($this->server->getName(), $this->server->getServerStatus());
        }
    }
}