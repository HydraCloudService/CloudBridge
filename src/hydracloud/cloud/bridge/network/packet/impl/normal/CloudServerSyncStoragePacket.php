<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;


//sending to cloud
class CloudServerSyncStoragePacket extends CloudPacket {

    public function __construct(private array $data = []) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->data);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->data = $packetData->readArray();
    }

    public function getData(): array {
        return $this->data;
    }

    public function handle(): void {}
}