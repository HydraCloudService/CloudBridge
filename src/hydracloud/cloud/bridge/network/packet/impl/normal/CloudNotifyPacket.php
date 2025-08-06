<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;
use hydracloud\cloud\bridge\util\NotifyList;
use pocketmine\player\Player;
use pocketmine\Server;

class CloudNotifyPacket extends CloudPacket {

    public function __construct(private string $message = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->message);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->message = $packetData->readString();
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function handle(): void {
        foreach (array_filter(Server::getInstance()->getOnlinePlayers(), fn(Player $player) => $player->hasPermission("hydracloud.notify.receive") && NotifyList::exists($player)) as $player) {
            $player->sendMessage($this->message);
        }
    }
}