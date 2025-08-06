<?php

namespace hydracloud\cloud\bridge\task;

use GlobalLogger;
use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\CloudBridge;
use pocketmine\scheduler\Task;
use pocketmine\Server;

final class TimeoutTask extends Task {

    public function onRun(): void {
        if (!CloudAPI::get()->isVerified()) return;
        if ((CloudBridge::getInstance()->lastKeepALiveCheck + 10) <= time()) {
            GlobalLogger::get()->warning("§cServer timed out, shutting down...");
            Server::getInstance()->shutdown();
        }
    }
}