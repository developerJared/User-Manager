<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Import\ImportUsersController as Users;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacyUsers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from legacy DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Users $users)//
    {
        parent::__construct();
        $this->users = $users;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $this->users->legacyUserImport();
            $this->info('The import user service was successful!');
        }catch(\Exception $e){
            $this->info('The import user service failed with error:  ');
            $this->info($e->getMessage());
        }
    }
}
