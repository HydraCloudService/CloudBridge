<?php

namespace hydracloud\cloud\bridge\network\packet\impl\request;

use hydracloud\cloud\bridge\network\packet\RequestPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;

final class ServerHandshakeRequestPacket extends RequestPacket {

    public function __construct(
        private ?string $serverName = null,
        private ?string $authKey = null,
        private ?int $processId = null,
        private ?int $maxPlayers = null
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->serverName)
            ->write($this->authKey)
            ->write($this->processId)
            ->write($this->maxPlayers);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->serverName = $packetData->readString();
        $this->authKey = $packetData->readString();
        $this->processId = $packetData->readInt();
        $this->maxPlayers = $packetData->readInt();
    }

    public function getAuthKey(): ?string {
        return $this->authKey;
    }

    public function getServerName(): ?string {
        return $this->serverName;
    }

    public function getProcessId(): ?int {
        return $this->processId;
    }

    public function getMaxPlayers(): ?int {
        return $this->maxPlayers;
    }
}