<?php

namespace hydracloud\cloud\bridge\event\network;

use hydracloud\cloud\bridge\util\net\Address;
use pocketmine\event\Event;

final class NetworkConnectEvent extends Event {

    public function __construct(private readonly Address $address) {}

    public function getAddress(): Address {
        return $this->address;
    }
}