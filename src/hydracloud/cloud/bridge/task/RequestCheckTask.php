<?php

namespace hydracloud\cloud\bridge\task;

use hydracloud\cloud\bridge\network\packet\RequestPacket;
use hydracloud\cloud\bridge\network\request\RequestManager;
use pocketmine\scheduler\Task;

final class RequestCheckTask extends Task {

    public function __construct(private readonly RequestPacket $requestPacket) {}

    public function onRun(): void {
        if (isset(RequestManager::getInstance()->getAll()[$this->requestPacket->getRequestId()])) {
            if (($this->requestPacket->getSentTime() + 10) < time()) {
                RequestManager::getInstance()->reject($this->requestPacket);
                RequestManager::getInstance()->remove($this->requestPacket);
                $this->getHandler()->cancel();
            }
        } else {
            $this->getHandler()->cancel();
        }
    }

    public function getRequestPacket(): RequestPacket {
        return $this->requestPacket;
    }
}