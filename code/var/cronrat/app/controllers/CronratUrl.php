<?php
use \Lasdorf\CronratApi\CronratApi as Rat;

class CronratUrl extends BaseController {

    //start job

    public function getCr($ratkey=false, $ratname=false)
    {

        //see if ratkey is valid 404 otherwise

        $nextcheck=Input::Get('NEXTCHECK',false);
        if(empty($nextcheck)) //trylowercase
        {
            $nextcheck=Input::Get('nextcheck',false);
        }

        if(empty($nextcheck)) //trylowercase
        {
            $nextcheck=1440; //default 1440 min 24hr
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

        $activeon=Input::Get('ACTIVEON',false);
        if(empty($activeon)) //trylowercase
        {
            $activeon=Input::Get('activeon',false);
        }
        if(empty($activeon)) //trylowercase
        {
            $activeon='MTWTFSS';
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

        $inactive = strpos($activeon,'0'); //see if any day s inactive
        $daystoskip = 0;

        if($inactive !== false)
        {
            //now lets see when this needs to run next. it be greater of $ttl (nextcheck) or next day to run on via active ON

            $weekday = date('w',time() + intval($toutc));

           //lets see if position
            $pos = intval($weekday);

            if(0 == $pos)
            {
                $pos=7; //sunday is 7, rest of wold has monday based weeks
            }

            //now see if next day is OFF
            if('0' == substr($activeon, $pos-1, 1))
            {
                $daystoskip = 1;

                while(true)
                {
                    //look for next working day

                    $pos++;

                    if(7 == $pos)
                    {
                        $pos=1; //reset to beginnig of the week
                    }

                    if('0' == substr($activeon, $pos-1, 1))
                   {
                      $daystoskip++;
                   }
                     else
                   {
                      break;
                   }
                }
            }
            $nextcheck = $nextcheck + (1440 * $daystoskip);
        }

        try{
            $res = Rat::check_set_rat($ratkey, $ratname, $nextcheck, $emailto, $urlto, $activeon,$toutc);
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