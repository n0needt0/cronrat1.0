<?php
use \Lasdorf\CronratApi\CronratApi as Rat;

class CronratUrl extends BaseController {

    //start job

    public function getCr($ratkey=false, $ratname=false)
    {

        //see if ratkey is valid 404 otherwise

        $nextcheck=Input::Get('NEXTCHEK',1440);
        $emailto=Input::Get('EMAILTO',false);
        $urlto=Input::Get('URLTO',false);
        $activeon=Input::Get('ACTIVEON','MTWTFSS');

        try{
            $res = Rat::check_set_rat($ratkey, $ratname, $nextcheck, $emailto, $urlto, $activeon);
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