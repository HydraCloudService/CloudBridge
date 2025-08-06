<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\module\ModuleManager;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;
use hydracloud\cloud\bridge\util\ModuleSettings;

class ModuleSyncPacket extends CloudPacket {

    private array $data = [];

    public function __construct() {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->data);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->data = $packetData->readArray();
    }

    public function getData(): array {
        return $this->data;
    }

    public function handle(): void {
        ModuleSettings::sync($this->data);
        ModuleManager::getInstance()->syncModuleStates();
    }
}