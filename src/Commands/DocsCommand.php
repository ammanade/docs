<?php

namespace Ammanade\Docs\Commands;

use Illuminate\Console\Command;

class DocsCommand extends Command
{
    public $signature = 'docs';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
