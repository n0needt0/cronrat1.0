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
        $this->rats = array();
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

            //get all rats
            $sql = "SELECT * FROM cronrat WHERE verify=''";
            $this->rats = DB::table("cronrat")->get();
            $this->debug(print_r($this->rats, true));

            Redis::pipeline(function($pipe)
            {
                $this->debug("Updating Valid Cronrats");
                foreach ($this->rats as $row)
                {
                    $pipe->set($row->cronrat_code . ":VALID", $row->ttl);
                    $pipe->expire($row->cronrat_code . ":VALID",60*60); // revalidate every hour
                }

                $this->debug("Updating Valid Cronrats: Done");
            });

            //check if up to date
           foreach ($this->rats as $row)
           {
               if($row->active == 1)
              {
                  if( $ts = Redis::get($row->cronrat_code . ':RAT') )
                  {
                      if($ts + $row->ttl < time())
                      {
                          //expired call the owner
                          $exp = array('cronrat_name'=>$row->cronrat_name, 'cronrat_code'=>$row->cronrat_code, 'email'=>$row->fail_email);
                          $this->notify($exp);
                          $this->debug('Expired ' . print_r($exp, true));
                      }
                  }
                    else
                  {
                      //expired call the owner
                      $exp = array('cronrat_name'=>$row->cronrat_name, 'cronrat_code'=>$row->cronrat_code, 'email'=>$row->fail_email);
                      $this->notify($exp);
                      $this->debug('Expired ' . print_r($exp, true));
                  }
              }
           }


        }
        catch(Exception $e)
        {
            $this->error($e->getMessage());
        }
    }

    private function notify($data)
    {
         Mail::send('emails.cronrat.down', $data, function($message) use ($data)
         {
            $message->to($data['email'])->subject($data['cronrat_name'] . ' Cronrat is down!');
         });
    }

}