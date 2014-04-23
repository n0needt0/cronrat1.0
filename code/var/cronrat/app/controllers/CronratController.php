<?php
use \Lasdorf\CronratApi\CronratApi as Rat;

class CronratController extends BaseController {

    public function __construct()
    {
       $this->beforeFilter('auth', array('except' => array('getFaq','getIndex')));
    }

    public function getFaq()
    {
        $data = array('cronrat_code'=>'YourCronRatCode');
        if( Sentry::check())
        {
        $data['cronrat_code'] =  Sentry::getUser()->cronrat_code;
        }
        return View::make('cronrat.faq')->with($data);
    }

    public function getIn()
    {
       return Redirect::to('/cronrat')->withInput();
    }

    public function getIndex()
    {

        // User is logged in
	    $user = Sentry::getUser();

        if(empty($user))
        {
            $data = array('cronrat_code'=>'YourCronRatCode');
            return View::make('cronrat.faq')->with($data);
        }

        $data['rats'] = array();
        $data['cronrat_code'] =  Sentry::getUser()->cronrat_code;

        $rats = array();

 		if($expected = Rat::get_account_expected_rats($user->cronrat_code,false))
 		{
 		    $live = Rat::get_account_live_rats($user->cronrat_code,false);

 		    //mark ones that are live
 		    foreach($expected as $ek=> $ev)
 		    {
 		        //get the matching part of a key
 		        $expected_match = str_replace($user->cronrat_code . '::specs::', '', $ek);

 		        $res[$expected_match] = $ev;
 		        $res[$expected_match]['cronrat_name'] = $expected_match;
 		        $res[$expected_match]['ts'] = false;
 		        $res[$expected_match]['active'] = 0;
 		        $res[$expected_match]['cronrat_code'] = $user->cronrat_code . '::::' . $expected_match;

 		        foreach($live as $lk=>$lv)
 		        {
 		            $live_match = str_replace($user->cronrat_code . '::specs::', '', $ek);
 		            $ts = false;

                    if($live_match == $expected_match)
                    {
                        //rat is live and well
                        $res[$expected_match]['ts'] = $lv;
                        $res[$expected_match]['active'] = 1;
                    }
 		        }
 		    }

 		    $data['rats'] = $res;
 		}

        return View::make('cronrat.index')->with($data);
    }

    public function postUpdate()
    {

        $action = Input::get('action');
        $actor = Input::get('actor');

        //here the coupling dont want to open whole $metod
        if($action == 'delete' && $actor !='')
        {
            $res = Rat::delete_rat($actor);
            Session::flash('success', 'success !');
        }

         return Redirect::to('/');
    }
}