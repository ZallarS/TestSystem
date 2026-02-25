<?php

namespace App\Console\Commands;

use App\Services\PluginManager;
use Illuminate\Console\Command;

class PluginSyncCommand extends Command
{
    protected $signature = 'plugin:sync';
    protected $description = 'Scan plugins directory and sync with database';

    public function handle(PluginManager $manager): int
    {
        $manager->scanAndSync();
        $this->info('Plugins synchronized successfully.');
        return Command::SUCCESS;
    }
}
