<?php

namespace sensen\interfaces;

interface ListenerInterface
{
    public function handle($event): void;
}