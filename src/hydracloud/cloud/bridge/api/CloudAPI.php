<?php

namespace hydracloud\cloud\bridge\api;

use GlobalLogger;
use hydracloud\cloud\bridge\api\object\server\status\ServerStatus;
use hydracloud\cloud\bridge\api\provider\PlayerProvider;
use hydracloud\cloud\bridge\api\provider\ServerProvider;
use hydracloud\cloud\bridge\api\provider\TemplateProvider;
use hydracloud\cloud\bridge\CloudBridge;
use hydracloud\cloud\bridge\event\server\CloudServerRegisterEvent;
use hydracloud\cloud\bridge\language\Language;
use hydracloud\cloud\bridge\network\packet\impl\normal\CloudServerStatusChangePacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\ConsoleTextPacket;
use hydracloud\cloud\bridge\network\packet\impl\normal\KeepAlivePacket;
use hydracloud\cloud\bridge\network\packet\impl\request\ServerHandshakeRequestPacket;
use hydracloud\cloud\bridge\network\packet\impl\type\LogType;
use hydracloud\cloud\bridge\network\packet\impl\type\VerifyStatus;
use hydracloud\cloud\bridge\task\ChangeStatusTask;
use hydracloud\cloud\bridge\util\GeneralSettings;
use hydracloud\cloud\bridge\network\packet\impl\response\ServerHandshakeResponsePacket;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;

final class CloudAPI {
    use SingletonTrait;

    private VerifyStatus $verified;
    private static PlayerProvider $playerProvider;
    private static ServerProvider $serverProvider;
    private static TemplateProvider $templateProvider;

    public function __construct() {
        self::setInstance($this);
        $this->verified = VerifyStatus::NOT_APPLIED();

        self::$playerProvider = new PlayerProvider();
        self::$serverProvider = new ServerProvider();
        self::$templateProvider = new TemplateProvider();
    }

    public function processLogin(): void {
        if ($this->verified === VerifyStatus::VERIFIED()) return;
        ServerHandshakeRequestPacket::makeRequest(
            GeneralSettings::getServerName(), GeneralSettings::getCloudPassword(), getmypid(), Server::getInstance()->getMaxPlayers()
        )->then(function (ServerHandshakeResponsePacket $packet): void {
            if ($packet->getVerifyStatus() === VerifyStatus::VERIFIED()) {
                CloudBridge::getInstance()->getScheduler()->scheduleRepeatingTask(new ChangeStatusTask(), 20);
                GlobalLogger::get()->info(Language::current()->translate("inGame.server.verified"));
                $this->verified = VerifyStatus::VERIFIED();
                KeepAlivePacket::create()->sendPacket();
            } else {
                $this->verified = VerifyStatus::DENIED();
                GlobalLogger::get()->warning("§cVerification was denied, shutting down...");
                Server::getInstance()->shutdown();
            }
        })->failure(function(): void {
            $this->verified = VerifyStatus::DENIED();
            GlobalLogger::get()->warning("§cFailed to verify cloud server, shutting down...");
            Server::getInstance()->shutdown();
        });
    }

    public function changeStatus(ServerStatus $status): void {
        CloudServerStatusChangePacket::create($status)->sendPacket();
    }

    public function logConsole(string $text, ?LogType $logType = null): void {
        ConsoleTextPacket::create($text, $logType ?? LogType::INFO())->sendPacket();
    }

    public function isVerified(): bool {
        return $this->verified === VerifyStatus::VERIFIED();
    }

    public static function players(): PlayerProvider {
        return self::$playerProvider;
    }

    public static function servers(): ServerProvider {
        return self::$serverProvider;
    }

    public static function templates(): TemplateProvider {
        return self::$templateProvider;
    }

    public static function get(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    private static function getInstance(): self {
        return self::get();
    }
}