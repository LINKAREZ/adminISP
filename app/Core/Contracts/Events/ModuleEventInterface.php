<?php

namespace App\Core\Contracts\Events;

interface ModuleEventInterface
{
    public function getModuleName(): string;
    public function getEventData(): array;
}
