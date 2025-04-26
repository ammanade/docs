<?php

namespace Ammanade\Docs\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Input
{
    public function __construct(
        /** @var class-string */
        public string $class
    ) {}
}
