<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RatCheck extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:ratcheck';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Check Rats every 5 minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function debug($msg)
    {
        if(Config::get('app.debug'))
        {
            $this->info("DEBUG: $msg" );
        }
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $env = $this->option('env');

        if(empty($env))
        {
            $this->error('--env option required! local|production');
            die;
        }

        try{

            //create fresh copy of invoices
            $sql = "SELECT * FROM cronrat WHERE verify=''";
            $this->res = DB::table("cronrat")->get();

            $this->debug("Updating Valid Cronrats");

            Redis::pipeline(function($pipe)
            {
                foreach ($this->res as $row)
                {
                    $pipe->set($row->cronrat_code . ":VALID", 1);
                    $pipe->expire($row->cronrat_code . ":VALID", 60*60*24);
                }
            });

            $this->debug("Updating Valid Cronrats: Done");
            //check all rats and set mail to flags

        }
        catch(Exception $e)
        {
            $this->error($e->getMessage());
        }
    }

}