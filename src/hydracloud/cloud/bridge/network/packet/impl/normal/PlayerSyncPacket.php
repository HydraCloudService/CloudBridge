<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\api\object\player\CloudPlayer;
use hydracloud\cloud\bridge\api\registry\Registry;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;

class PlayerSyncPacket extends CloudPacket {

    public function __construct(
        private ?CloudPlayer $player = null,
        private bool $removal = false
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writePlayer($this->player);
        $packetData->write($this->removal);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readPlayer();
        $this->removal = $packetData->readBool();
    }

    public function getPlayer(): ?CloudPlayer {
        return $this->player;
    }

    public function isRemoval(): bool {
        return $this->removal;
    }

    public function handle(): void {
        if (CloudAPI::players()->get($this->player->getName()) === null) {
            if (!$this->removal) Registry::registerPlayer($this->player);
        } else {
            if ($this->removal) {
                Registry::unregisterPlayer($this->player->getName());
            } else if ($this->player->getCurrentServer() !== null) {
                Registry::updatePlayer($this->player->getName(), $this->player->getCurrentServer()->getName());
            }
        }
    }
}