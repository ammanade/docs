<?php

namespace Ammanade\Docs\UseCases\Product\Create;

use Ammanade\Docs\ValueObjects\UserValueObject;

class ProductCreateResult
{
    public function __construct(
        public int $id,
        public string $name,
        public UserValueObject $user,
        /** @var UserValueObject[] */
        public array $users
    ) {}
}
