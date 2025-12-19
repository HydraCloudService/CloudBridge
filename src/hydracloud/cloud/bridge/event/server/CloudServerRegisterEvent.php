<?php

namespace hydracloud\cloud\bridge\event\server;

use hydracloud\cloud\bridge\api\object\server\CloudServer;
use pocketmine\event\Event;

class CloudServerRegisterEvent extends Event {

    public function __construct(private readonly CloudServer $cloudServer) {}

    public function getCloudServer(): CloudServer {
        return $this->cloudServer;
    }
}