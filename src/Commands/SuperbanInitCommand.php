<?php

namespace Superban\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;

class SuperbanInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'superban:activate';

    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activates the superban package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Run the migrate command with specific options
        Artisan::call('migrate', [
            '--path' => 'packages/superban/src/Migrations/superbanControlTable.php',
        ]);

        $this->info('Command executed successfully!');
        return 0; // Indicate successful completion

        // $path = $this->argument('path');
        // if (file_exists($path)) {
        //     $this->import($path);
        // } else {
        //     $this->error('File not exists');
        // }
    }
}
