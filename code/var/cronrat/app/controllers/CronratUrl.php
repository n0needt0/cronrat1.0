<?php
use \Lasdorf\CronratApi\CronratApi as Rat;
use \Lasdorf\CronratApi\Crontab as Crontab;

class CronratUrl extends BaseController {

    //start job

    public function getCr($ratkey=false, $ratname=false)
    {

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
            echo "RATKEY: $ratkey</br>";
            echo "RATNAME: $ratname</br>";
            echo "CRONTAB: $crontab</br>";
            echo "ALLOW: $allow</br>";
            echo "URLTO: $urlto</br>";
            echo "EMAILTO: $emailto</br>";
            echo "TOUTC: $toutc</br>";
        }


        if($debug)
        {
            echo "LOCAL TIME " . date_default_timezone_get() . "</br>";
            echo "scheduled_lastrun: " . date('Y-m-d H:i:s', $scheduled_lastrun);
            echo "</br>";
            echo "now: " . date('Y-m-d H:i:s', $now);
            echo "</br>";
            echo "scheduled_nextrun: " . date('Y-m-d H:i:s', $scheduled_nextrun);
            echo "</br>";
        }
        //now convert this stamps to UTC time
        date_default_timezone_set('UTC');
        $scheduled_lastrun = $cron->getPreviousRunDate()->getTimeStamp();
        $now = time();
        $scheduled_nextrun = $cron->getNextRunDate()->getTimeStamp();

        if($debug)
        {
            echo "</br>UTC TIME </br>";
            echo "scheduled_lastrun: " . date('Y-m-d H:i:s', $scheduled_lastrun);
            echo "</br>";
            echo "now: " . date('Y-m-d H:i:s', $now);
            echo "</br>";
            echo "scheduled_nextrun: " . date('Y-m-d H:i:s', $scheduled_nextrun);
            echo "</br>";
        }

        try{
            $res = Rat::check_set_rat($ratkey, $ratname, $crontab, $allow, $toutc, $emailto, $urlto, $scheduled_nextrun + $allow);
            if($res)
            {
                echo "OK";
                die;
            }
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            die;
        }
    }
}