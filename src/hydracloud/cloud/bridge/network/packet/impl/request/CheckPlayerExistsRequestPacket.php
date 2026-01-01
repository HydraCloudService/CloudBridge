<?php

namespace hydracloud\cloud\bridge\network\packet\impl\request;

use hydracloud\cloud\bridge\network\packet\data\PacketData;
use hydracloud\cloud\bridge\network\packet\RequestPacket;

class CheckPlayerExistsRequestPacket extends RequestPacket {

    public function __construct(private string $player = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->player);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readString();
    }

    public function getPlayer(): string {
        return $this->player;
    }
}