<?php

namespace hydracloud\cloud\bridge;

use GlobalLogger;
use pmmp\thread\ThreadSafeArray;
use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\event\network\NetworkPacketReceiveEvent;
use hydracloud\cloud\bridge\language\Language;
use hydracloud\cloud\bridge\listener\EventListener;
use hydracloud\cloud\bridge\module\npc\listener\NPCListener;
use hydracloud\cloud\bridge\module\sign\listener\SignListener;
use hydracloud\cloud\bridge\network\Network;
use hydracloud\cloud\bridge\network\packet\handler\PacketSerializer;
use hydracloud\cloud\bridge\network\packet\impl\normal\DisconnectPacket;
use hydracloud\cloud\bridge\network\packet\impl\type\DisconnectReason;
use hydracloud\cloud\bridge\network\packet\ResponsePacket;
use hydracloud\cloud\bridge\network\request\RequestManager;
use hydracloud\cloud\bridge\task\TimeoutTask;
use hydracloud\cloud\bridge\util\GeneralSettings;
use hydracloud\cloud\bridge\util\net\Address;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\YmlServerProperties;

final class CloudBridge extends PluginBase {
    use SingletonTrait;

    public static function getPrefix(): string {
        return Language::current()->translate("inGame.prefix");
    }

    public array $signDelay = [];
    public float|int $lastKeepALiveCheck = 0.0;
    private Network $network;

    protected function onEnable(): void
    {
        self::setInstance($this);
        $configGroup = Server::getInstance()->getConfigGroup();
        $configGroup->setConfigBool(YmlServerProperties::AUTO_REPORT_SEND_SETTINGS, false);
        $configGroup->save();

        if (!file_exists($this->getDataFolder() . "skins/")) mkdir($this->getDataFolder() . "skins/");
        GeneralSettings::sync();

        $networkBuffer = new ThreadSafeArray();
        $this->network = new Network(new Address("127.0.0.1", GeneralSettings::getNetworkPort()), $this->getServer()->getTickSleeper()->addNotifier(function() use ($networkBuffer): void {
            while (($buffer = $networkBuffer->shift()) !== null) {
                if (($packet = PacketSerializer::decode($buffer)) !== null) {
                    ($ev = new NetworkPacketReceiveEvent($packet))->call();
                    if ($ev->isCancelled()) return;
                    $packet->handle();

                    if ($packet instanceof ResponsePacket) {
                        RequestManager::getInstance()->resolve($packet);
                        RequestManager::getInstance()->remove($packet->getRequestId());
                    }
                } else {
                    GlobalLogger::get()->warning("§cReceived an unknown packet from the cloud!");
                    GlobalLogger::get()->debug(GeneralSettings::isNetworkEncryptionEnabled() ? base64_decode($buffer) : $buffer);
                }
            }
        }), $networkBuffer);
        $this->network->start();

        $this->lastKeepALiveCheck = time();
        $this->getScheduler()->scheduleRepeatingTask(new TimeoutTask(), 20);

        $this->registerPermission("hydracloud.command.cloud", "hydracloud.command.notify", "hydracloud.notify.receive", "hydracloud.maintenance.bypass", "hydracloud.command.transfer", "hydracloud.command.cloudnpc", "hydracloud.command.template_group", "hydracloud.cloudsign.add", "hydracloud.cloudsign.remove");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new NPCListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignListener(), $this);

        CloudAPI::get()->processLogin();
    }

    public function registerPermission(string... $permissions): void {
        $operator = PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR);
        if ($operator !== null) {
            foreach ($permissions as $permission) {
                DefaultPermissions::registerPermission(new Permission($permission), [$operator]);
            }
        }
    }

    protected function onDisable(): void {
        $this->network->sendPacket(new DisconnectPacket(DisconnectReason::SERVER_SHUTDOWN()));
        $this->network->close();
    }
}