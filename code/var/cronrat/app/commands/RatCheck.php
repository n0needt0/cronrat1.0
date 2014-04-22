<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Lasdorf\CronratApi\CronratApi as Rat;

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

            //refresh accounts from db
            Rat::refresh_accounts_from_db();

            //get all expected rats
            $expected = Rat::get_expected_rats();

            $this->info("expecting" . print_r($expected, true));

            foreach($expected as $ek => $ev)
            {
                $rp = explode('::', $ek);
                if(count($rp) != 3)
                {
                    $this->error("Invalid rat key $ek");
                    continue;
                }

                $livekey = str_replace('::specs::', '::status::', $ek);
                $deadratkey = str_replace('::specs::', '::dead::', $ek);

                $this->debug("livekey: $livekey");
                $this->debug("deadratkey: $deadratkey");

                if(Rat::lookup($livekey))
                {
                    //rat is live
                    if(Rat::lookup($deadratkey))
                    {
                        Rat::remove_dead_rat($deadratkey);
                        $exp = array('cronrat_name'=>$rp[2], 'cronrat_code'=>$rp['0'], 'email'=>$ev['email'], 'nextrun'=>$ev['nextrun'], 'url'=>$ev['url'], 'crontab'=>$ev['crontab'], 'allow'=>$ev['allow']);
                        $this->notify_up($exp);
                        $this->debug('Alive again ' . print_r($exp, true));
                    }
                }
                else
                {
                    //rat died
                    $exp = array('cronrat_name'=>$rp[2], 'cronrat_code'=>$rp['0'], 'email'=>$ev['email'], 'nextrun'=>$ev['nextrun'], 'url'=>$ev['url'], 'crontab'=>$ev['crontab'], 'allow'=>$ev['allow']);

                    if(!$deadrat = Rat::lookup($deadratkey))
                    {
                        //first time rat dies, mark it and notify
                        Rat::mark_dead($deadratkey, array('cnt'=>1, 'ts'=>time()));

                        $this->notify_down($exp);
                        $this->debug('Expired ' . print_r($exp, true));
                    }
                    else
                    {
                        if(!empty($deadrat['cnt']) && $deadrat['cnt'] < 3)
                        {
                            Rat::mark_dead($deadratkey, array('cnt'=>($deadrat['cnt']+1), 'ts'=>time()));
                            $this->notify_down($exp);
                            $this->debug('Expired ' . print_r($exp, true));
                        }
                    }
                }
            }
        }
        catch(Exception $e)
        {
            $this->error($e->getMessage());
        }
    }

    private function notify_down($data)
    {
        $data['random'] = $this->get_random_text();

        Mail::send('emails.cronrat.down', $data, function($message) use ($data)
        {
            $message->to($data['email'])->subject($data['cronrat_name'] . ' Cronrat is down!');
        });
    }

    private function notify_up($data)
    {
        $data['random'] = $this->get_random_text();

        Mail::send('emails.cronrat.up', $data, function($message) use ($data)
        {
            $message->to($data['email'])->subject($data['cronrat_name'] . ' Recovered!');
        });
    }

    function get_random_text()
    {
        $file = __DIR__ . '/misc/random.txt';
        $returnlines = 20;
        $i=0;
        $buffer="\n";
        $rand = rand(1, filesize($file));

        $handle = @fopen($file, "r");
        fseek($handle, $rand);

        if ($handle)
        {
            while (!feof($handle) && $i<=$returnlines)
            {
                $buffer .= fgets($handle, 4096);
                $i++;
            }

            fclose($handle);
        }

        return "\n</br>\n</br>\n</br>*************RANDOM TEXT TO ESCAPE SPAM FILTER****************************\n</br>\n</br>\n</br>" . $buffer;
    }

}