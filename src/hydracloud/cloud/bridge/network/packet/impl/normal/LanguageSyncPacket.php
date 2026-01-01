<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\command\CloudCommand;
use hydracloud\cloud\bridge\command\CloudNotifyCommand;
use hydracloud\cloud\bridge\command\TransferCommand;
use hydracloud\cloud\bridge\language\Language;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;
use pocketmine\Server;

final class LanguageSyncPacket extends CloudPacket {

    public function __construct(
        private string $language = "",
        private array $messages = []
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->language)
            ->write($this->messages);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->language = $packetData->readString();
        $this->messages = $packetData->readArray();
    }

    public function getLanguage(): string {
        return $this->language;
    }

    public function getMessages(): array {
        return $this->messages;
    }

    public function handle(): void {
        Language::get($this->language)?->sync($this->messages);
        Server::getInstance()->getCommandMap()->registerAll("cloudBridge", [
            new CloudCommand(),
            new TransferCommand(),
            new CloudNotifyCommand()
        ]);
    }
}