<?php

namespace hydracloud\cloud\bridge\api\object\server;

use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\api\object\player\CloudPlayer;
use hydracloud\cloud\bridge\api\object\server\data\CloudServerData;
use hydracloud\cloud\bridge\api\object\server\status\ServerStatus;
use hydracloud\cloud\bridge\api\object\server\storage\CloudServerStorage;
use hydracloud\cloud\bridge\api\object\template\Template;
use hydracloud\cloud\bridge\util\Utils;

final class CloudServer {

    private CloudServerStorage $storage;

    public function __construct(
        private readonly int $id,
        private readonly string $uuid,
        private readonly Template $template,
        private readonly CloudServerData $cloudServerData,
        private ServerStatus $serverStatus
    ) {
        $this->storage = new CloudServerStorage($this);
    }

    public function getName(): string {
        return $this->template->getName() . "-" . $this->id;
    }

    /**
     * @return string
     */
    public function getUuid(): string {
        return $this->uuid;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTemplate(): Template {
        return $this->template;
    }

    public function getCloudServerData(): CloudServerData {
        return $this->cloudServerData;
    }

    public function getServerStatus(): ServerStatus {
        return $this->serverStatus;
    }

    public function setServerStatus(ServerStatus $serverStatus): void {
        $this->serverStatus = $serverStatus;
    }

    public function getCloudServerStorage(): CloudServerStorage {
        return $this->storage;
    }

    public function getCloudPlayer(string $name): ?CloudPlayer {
        foreach ($this->getCloudPlayers() as $player) if ($player->getName() == $name) return $player;
        return null;
    }

    /** @return array<CloudPlayer> */
    public function getCloudPlayers(): array {
        return array_filter(CloudAPI::players()->getAll(), fn(CloudPlayer $player) => ($this->template->getTemplateType() === "SERVER" ? $player->getCurrentServer() === $this : $player->getCurrentProxy() === $this));
    }

    public function toArray(): array {
        return [
            "name" => $this->getName(),
            "id" => $this->id,
            "uuid" => $this->uuid,
            "template" => $this->template->getName(),
            "port" => $this->getCloudServerData()->getPort(),
            "maxPlayers" => $this->getCloudServerData()->getMaxPlayers(),
            "processId" => $this->getCloudServerData()->getProcessId(),
            "serverStatus" => $this->getServerStatus()->getName()
        ];
    }

    public static function fromArray(array $server): ?CloudServer {
        if (!Utils::containKeys($server, "name", "id", "uuid", "template", "port", "maxPlayers", "processId", "serverStatus")) return null;
        if (($template = CloudAPI::templates()->get($server["template"])) === null) return null;
        return new CloudServer(
            intval($server["id"]),
            $server["uuid"],
            $template,
            new CloudServerData(intval($server["port"]), intval($server["maxPlayers"]), intval($server["processId"])),
            ServerStatus::get($server["serverStatus"]) ?? ServerStatus::ONLINE()
        );
    }
}