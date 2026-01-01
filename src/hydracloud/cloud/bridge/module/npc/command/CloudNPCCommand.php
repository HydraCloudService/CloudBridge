<?php

namespace hydracloud\cloud\bridge\module\npc\command;

use hydracloud\cloud\bridge\module\npc\form\NPCMainForm;
use hydracloud\cloud\bridge\language\Language;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class CloudNPCCommand extends Command {

    public function __construct() {
        parent::__construct("cloudnpc", Language::current()->translate("inGame.command.description.cloudnpc"), "/cloudnpc");
        $this->setPermission("hydracloud.command.cloudnpc");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                $sender->sendForm(new NPCMainForm());
            } else $sender->sendMessage(Language::current()->translate("inGame.no.permission"));
        }
        return true;
    }
}