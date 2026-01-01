<?php

namespace hydracloud\cloud\bridge\module;

use hydracloud\cloud\bridge\module\hubcommand\HubCommandModule;
use hydracloud\cloud\bridge\module\npc\CloudNPCModule;
use hydracloud\cloud\bridge\module\sign\CloudSignModule;
use hydracloud\cloud\bridge\util\ModuleSettings;
use pocketmine\utils\SingletonTrait;

final class ModuleManager {
    use SingletonTrait;

    private array $modules;

    public function __construct() {
        self::setInstance($this);

        $this->modules = [
            HubCommandModule::class => new HubCommandModule(),
            CloudNPCModule::class => new CloudNPCModule(),
            CloudSignModule::class => new CloudSignModule()
        ];
    }

    public function addModule(BaseModule $baseModule): void {
        $this->modules[$baseModule::class] = $baseModule;
    }

    public function removeModule(BaseModule $baseModule): void {
        if (isset($this->modules[$baseModule::class])) unset($this->modules[$baseModule::class]);
    }

    /** @internal */
    public function syncModuleStates(): void {
        HubCommandModule::get()->setEnabled(ModuleSettings::isHubCommandModuleEnabled());
        CloudNPCModule::get()->setEnabled(ModuleSettings::isNpcModuleEnabled());
        CloudSignModule::get()->setEnabled(ModuleSettings::isSignModuleEnabled());
    }

    public function getModule(string $class): ?BaseModule {
        return $this->modules[$class] ?? null;
    }

    public function getModules(): array {
        return $this->modules;
    }
}