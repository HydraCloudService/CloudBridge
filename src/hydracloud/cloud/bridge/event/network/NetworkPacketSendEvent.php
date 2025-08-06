<?php

namespace hydracloud\cloud\bridge\event\network;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

final class NetworkPacketSendEvent extends Event implements Cancellable {
    use CancellableTrait;

    public function __construct(private readonly CloudPacket $packet) {}

    public function getPacket(): CloudPacket {
        return $this->packet;
    }
}