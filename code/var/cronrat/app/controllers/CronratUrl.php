<?php
use \Lasdorf\CronratApi\CronratApi as Rat;
use \Lasdorf\CronratApi\Crontab as Crontab;

class CronratUrl extends BaseController {

    //start job

    public function getCr($ratkey=false)
    {
        if(Input::has('rat'))
        {
            $ratname = Input::Get('rat');
        }
        elseif(Input::has('RAT'))
        {
            $ratname = Input::Get('RAT');
        }
        else
        {
            $ratname=false;
        }

        if(Input::has('debug'))
        {
            $debug = 1;
        }
        elseif(Input::has('DEBUG'))
        {
            $debug = 1;
        }
        else
        {
            $debug=0;
        }

        if(Input::has('crontab'))
        {
            $crontab = Input::Get('crontab');
        }
        elseif(Input::has('CRONTAB'))
        {
            $crontab = Input::Get('CRONTAB');
        }
        else
        {
            $crontab = Config::Get('cronrat.defaults.crontab');
        }

        if(Input::has('allow'))
        {
            $allow = Input::Get('allow');
        }
        elseif(Input::has('ALLOW'))
        {
            $allow = Input::Get('ALLOW');
        }
        else
        {
            $allow = Config::Get('cronrat.defaults.allow');
        }

        if(intval($allow) < 300)
        {
            $allow = Config::Get('cronrat.defaults.min_allow');
        }

        $emailto=Input::Get('EMAILTO',false);
        if(empty($emailto)) //trylowercase
        {
            $emailto=Input::Get('emailto',false);
        }

        $urlto=Input::Get('URLTO',false);
        if(empty($urlto)) //trylowercase
        {
            $urlto=Input::Get('urlto',false);
        }

        $toutc=Input::Get('TOUTC',false);
        if(empty($toutc)) //trylowercase
        {
            $toutc=Input::Get('toutc',false);
        }
        if(empty($toutc)) //trylowercase
        {
            $toutc='0';
        }

        $toutc = intval($toutc);

        if(abs($toutc) > 14)
        {
            $toutc = 0;
        }

        $params = array('crontab'=>$crontab, 'allow'=>$allow, 'emailto'=>$emailto, 'urlto'=>$urlto, 'toutc'=>$toutc);

        if($toutc == 0)
        {
            date_default_timezone_set('UTC');
        }
        else
        {
            $tz = timezone_name_from_abbr(null, $toutc * 3600, true);
            if($tz === false) $tz = timezone_name_from_abbr(null, $toutc * 3600, false);
            date_default_timezone_set($tz);
        }

        //lets evaluate crontab
        $cron = Cron\CronExpression::factory($crontab);

        $scheduled_lastrun = $cron->getPreviousRunDate()->getTimeStamp();
        $now = time();
        $scheduled_nextrun = $cron->getNextRunDate()->getTimeStamp();

        if($debug)
        {
            echo "****************\n</br>";
            echo "RATKEY: $ratkey</br>\n";
            echo "RATNAME: $ratname</br>\n";
            echo "CRONTAB: $crontab</br>\n";
            echo "ALLOW: $allow</br>\n";
            echo "URLTO: $urlto</br>\n";
            echo "EMAILTO: $emailto</br>\n";
            echo "TOUTC: $toutc</br>\n";
            echo "****************\n</br>";
        }


        if($debug)
        {
            echo "LOCAL TIME " . date_default_timezone_get() . "</br>\n";
            echo "scheduled_lastrun: " . date('Y-m-d H:i:s', $scheduled_lastrun);
            echo "</br> \n";
            echo "now: " . date('Y-m-d H:i:s', $now);
            echo "</br> \n";
            echo "scheduled_nextrun: " . date('Y-m-d H:i:s', $scheduled_nextrun);
            echo "</br> \n";
        }
        //now convert this stamps to UTC time
        //ate_default_timezone_set('UTC');
        //$scheduled_lastrun = $cron->getPreviousRunDate()->getTimeStamp();
        //$now = time();
        //$scheduled_nextrun = $cron->getNextRunDate()->getTimeStamp();

        if($debug)
        {
            echo "</br>ON UTC TIME </br> \n";
            echo "scheduled_lastrun: " . date('Y-m-d H:i:s', $scheduled_lastrun + (60*60*(-1 * $toutc)));
            echo "</br> \n";
            echo "now: " . date('Y-m-d H:i:s', $now  + (60*60*(-1 * $toutc)));
            echo "</br> \n";
            echo "scheduled_nextrun: " . date('Y-m-d H:i:s', $scheduled_nextrun  + (60*60*(-1 * $toutc)));
            echo "</br> \n";
        }

        try{
            $res = Rat::check_set_rat($ratkey, $ratname, $crontab, $allow, $toutc, $emailto, $urlto, $scheduled_nextrun);

            if($res)
            {
                echo "OK\n";
                return;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            return;
        }
    }
}