<?php

namespace hydracloud\cloud\bridge\network\packet\impl\response;

use hydracloud\cloud\bridge\network\packet\data\PacketData;
use hydracloud\cloud\bridge\network\packet\ResponsePacket;

class CheckPlayerExistsResponsePacket extends ResponsePacket {

    public function __construct(private bool $value = false) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->value);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->value = $packetData->readBool();
    }

    public function getValue(): bool {
        return $this->value;
    }

    public function handle(): void {}
}