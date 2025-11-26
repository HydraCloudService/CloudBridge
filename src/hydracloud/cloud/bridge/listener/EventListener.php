<?php

namespace hydracloud\cloud\bridge\listener;

use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\api\object\player\CloudPlayer;
use hydracloud\cloud\bridge\network\Network;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerConnectPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerDisconnectPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CheckPlayerExistsRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CheckPlayerMaintenanceRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CheckPlayerNotifyRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CheckPlayerExistsResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CheckPlayerMaintenanceResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CheckPlayerNotifyResponsePacket;
use hydracloud\cloud\bridge\util\NotifyList;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class EventListener implements Listener {

    public function onLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        CheckPlayerExistsRequestPacket::makeRequest($player->getName())
            ->then(function (CheckPlayerExistsResponsePacket $packet) use ($player): void{
                if ($packet->getValue()) {
                    Network::getInstance()->sendPacket(new PlayerConnectPacket(CloudPlayer::fromPlayer($player)));

                    if (CloudAPI::templates()->current()->isMaintenance()) {
                        CheckPlayerMaintenanceRequestPacket::makeRequest($player->getName())
                            ->then(function (CheckPlayerMaintenanceResponsePacket $packet) use($player): void {
                                if (!$packet->getValue() && !$player->hasPermission("hydracloud.maintenance.bypass")) {
                                    $player->kick("§cThis server is in maintenance.");
                                }
                            });
                    }

                    CheckPlayerNotifyRequestPacket::makeRequest($player->getName())
                        ->then(function (CheckPlayerNotifyResponsePacket $packet) use($player): void {
                            if ($packet->getValue()) NotifyList::put($player);
                        });
                } else {
                    $player->kick("§cYou must join via the proxy server.");
                }
            });
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        CheckPlayerExistsRequestPacket::makeRequest($player->getName())
            ->then(function (CheckPlayerExistsResponsePacket $packet) use ($name): void {
                if ($packet->getValue()) {
                    Network::getInstance()->sendPacket(new PlayerDisconnectPacket($name));
                }
            });
    }
}