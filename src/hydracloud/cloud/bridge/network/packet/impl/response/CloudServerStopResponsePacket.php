<?php

namespace hydracloud\cloud\bridge\network\packet\impl\response;

use hydracloud\cloud\bridge\network\packet\impl\type\ErrorReason;
use hydracloud\cloud\bridge\network\packet\ResponsePacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;

class CloudServerStopResponsePacket extends ResponsePacket {

    public function __construct(private ?ErrorReason $errorReason = null) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeErrorReason($this->errorReason);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->errorReason = $packetData->readErrorReason();
    }

    public function getErrorReason(): ?ErrorReason {
        return $this->errorReason;
    }

    public function handle(): void {}
}