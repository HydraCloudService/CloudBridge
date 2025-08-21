<?php

namespace hydracloud\cloud\bridge\network\packet\pool;

use GlobalLogger;
use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\DisconnectPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\KeepAlivePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\LanguageSyncPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CheckPlayerExistsRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\ServerHandshakeRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CheckPlayerExistsResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\response\ServerHandshakeResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CloudNotifyPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CloudServerSavePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CloudServerStatusChangePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CloudServerSyncStoragePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CloudSyncStoragesPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CommandSendAnswerPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\CommandSendPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\ConsoleTextPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\LibrarySyncPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\ModuleSyncPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerConnectPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerDisconnectPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerKickPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerNotifyUpdatePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerSwitchServerPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerSyncPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerTextPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\PlayerTransferPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\ServerSyncPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\TemplateSyncPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CheckPlayerMaintenanceRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CheckPlayerNotifyRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CloudServerStartRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\request\CloudServerStopRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CheckPlayerMaintenanceResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CheckPlayerNotifyResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CloudServerStartResponsePacket;
use hydracloud\cloud\bridge\network\packet\impl\response\CloudServerStopResponsePacket;
use pocketmine\utils\SingletonTrait;
use ReflectionClass;
use ReflectionException;

final class PacketPool {
    use SingletonTrait;

    /** @var array<CloudPacket> */
    private array $packets = [];

    public function __construct() {
        self::setInstance($this);

        $this->registerPacket(KeepAlivePacket::class);
        $this->registerPacket(DisconnectPacket::class);
        $this->registerPacket(ServerHandshakeRequestPacket::class);
        $this->registerPacket(ServerHandshakeResponsePacket::class);
        $this->registerPacket(CommandSendPacket::class);
        $this->registerPacket(CommandSendAnswerPacket::class);
        $this->registerPacket(ConsoleTextPacket::class);
        $this->registerPacket(PlayerConnectPacket::class);
        $this->registerPacket(PlayerDisconnectPacket::class);
        $this->registerPacket(PlayerTextPacket::class);
        $this->registerPacket(PlayerKickPacket::class);
        $this->registerPacket(PlayerNotifyUpdatePacket::class);
        $this->registerPacket(PlayerTransferPacket::class);
        $this->registerPacket(CloudServerSavePacket::class);
        $this->registerPacket(CloudServerStatusChangePacket::class);
        $this->registerPacket(PlayerSwitchServerPacket::class);
        $this->registerPacket(TemplateSyncPacket::class);
        $this->registerPacket(ServerSyncPacket::class);
        $this->registerPacket(PlayerSyncPacket::class);
        $this->registerPacket(CloudServerStartRequestPacket::class);
        $this->registerPacket(CloudServerStartResponsePacket::class);
        $this->registerPacket(CloudServerStopRequestPacket::class);
        $this->registerPacket(CloudServerStopResponsePacket::class);
        $this->registerPacket(CheckPlayerMaintenanceRequestPacket::class);
        $this->registerPacket(CheckPlayerMaintenanceResponsePacket::class);
        $this->registerPacket(CheckPlayerNotifyRequestPacket::class);
        $this->registerPacket(CheckPlayerNotifyResponsePacket::class);
        $this->registerPacket(CheckPlayerExistsRequestPacket::class);
        $this->registerPacket(CheckPlayerExistsResponsePacket::class);
        $this->registerPacket(CloudNotifyPacket::class);
        $this->registerPacket(ModuleSyncPacket::class);
        $this->registerPacket(LibrarySyncPacket::class);
        $this->registerPacket(LanguageSyncPacket::class);
        $this->registerPacket(CloudServerSyncStoragePacket::class);
        $this->registerPacket(CloudSyncStoragesPacket::class);
    }

    public function registerPacket(string $packetClass): void {
        if (!is_subclass_of($packetClass, CloudPacket::class)) return;
        try {
            $this->packets[(new ReflectionClass($packetClass))->getShortName()] = $packetClass;
        } catch (ReflectionException $e) {
            GlobalLogger::get()->logException($e);
        }
    }

    public function getPacketById(string $pid): ?CloudPacket {
        $get = $this->packets[$pid] ?? null;
        return ($get == null ? null : new $get());
    }

    public function getPackets(): array {
        return $this->packets;
    }
}