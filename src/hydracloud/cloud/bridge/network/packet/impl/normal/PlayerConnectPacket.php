<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\api\object\player\CloudPlayer;
use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;


class PlayerConnectPacket extends CloudPacket {

    public function __construct(private ?CloudPlayer $player = null) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writePlayer($this->player);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readPlayer();
    }

    public function getPlayer(): ?CloudPlayer{
        return $this->player;
    }

    public function handle(): void {}
}