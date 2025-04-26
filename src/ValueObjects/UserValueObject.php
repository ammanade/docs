<?php

namespace Ammanade\Docs\ValueObjects;

class UserValueObject
{
    public function __construct(
        public string $name,
        public EmailValueObject $email,
        public string $phone,
        public ?string $someValue
    ) {}
}
