<?php

namespace hydracloud\cloud\bridge\api\provider;

use Closure;
use hydracloud\cloud\bridge\api\object\template\Template;
use hydracloud\cloud\bridge\api\registry\Registry;
use hydracloud\cloud\bridge\util\GeneralSettings;
use RuntimeException;

final class TemplateProvider {

    public function current(): Template {
        return $this->get(GeneralSettings::getTemplateName()) ?? throw new RuntimeException("Current template shouldn't be null");
    }

    public function pick(Closure $filterClosure): array {
        return array_filter($this->getAll(), $filterClosure);
    }

    public function get(string $name): ?Template {
        return $this->getAll()[$name] ?? null;
    }

    /** @return array<Template> */
    public function getAll(): array {
        return Registry::getTemplates();
    }
}