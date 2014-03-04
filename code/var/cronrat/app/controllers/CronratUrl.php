<?php
use \Lasdorf\CronratApi\CronratApi as Rat;

class CronratUrl extends BaseController {

    //start job

    public function getCr($ratkey=false, $ratname=false, $ttlmin=1440, $alertemail=false, $alerturl=false)
    {

        //see if ratkey is valid 404 otherwise


        try{
            $res = Rat::check_set_rat($ratkey, $ratname, $ttlmin, $alertemail, $alerturl);
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