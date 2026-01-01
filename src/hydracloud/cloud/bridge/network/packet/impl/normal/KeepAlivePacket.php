<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\CloudBridge;
use hydracloud\cloud\bridge\network\Network;
use hydracloud\cloud\bridge\network\packet\CloudPacket;

final class KeepAlivePacket extends CloudPacket {

    public function handle(): void {
        CloudBridge::getInstance()->lastKeepALiveCheck = time();
        Network::getInstance()->sendPacket(new KeepALivePacket());
    }
}