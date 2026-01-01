<?php

namespace hydracloud\cloud\bridge\network\packet\impl\normal;

use hydracloud\cloud\bridge\api\CloudAPI;
use hydracloud\cloud\bridge\api\object\template\Template;
use hydracloud\cloud\bridge\api\registry\Registry;

use hydracloud\cloud\bridge\network\packet\CloudPacket;
use hydracloud\cloud\bridge\network\packet\data\PacketData;

class TemplateSyncPacket extends CloudPacket {

    public function __construct(
        private ?Template $template = null,
        private bool $removal = false
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeTemplate($this->template);
        $packetData->write($this->removal);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->template = $packetData->readTemplate();
        $this->removal = $packetData->readBool();
    }

    public function getTemplate(): ?Template {
        return $this->template;
    }

    public function isRemoval(): bool {
        return $this->removal;
    }

    public function handle(): void {
        if (CloudAPI::templates()->get($this->template->getName()) === null) {
            if (!$this->removal) Registry::registerTemplate($this->template);
        } else {
            if ($this->removal) {
                Registry::unregisterTemplate($this->template->getName());
            } else Registry::updateTemplate($this->template->getName(), $this->template->toArray());
        }
    }
}