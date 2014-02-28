<?php

use Lasdorf\CronratApi\CronratApi as Rat;

class CronratJobController extends BaseController {

    public function __construct()
    {

    }

    public function cronRat($ratkey, $job, $nextcheck=1440, alertemail=false, alerturl=false)
    {
        //see if ratkey is valid 404 otherwise
        $res = Rat
    }


    public function postAdd()
    {
    	// Gather Sanitized Input
		$input = array(
			'cronratName' => Input::get('cronratName'),
			'failEmail' => Input::get('failEmail'),
		    'failUrl' => Input::get('failUrl'),
		    'ttl' => Input::get('ttl')
			);

		// Set Validation Rules
		$rules = array (
			'cronratName' => 'required|min:1|max:32',
			'failEmail' => 'required|min:6|max:100|email',
		    'ttl' => 'required'
			);

		//Run input validation
		$v = Validator::make($input, $rules);

		if ($v->fails())
		{
			// Validation has failed
			return Redirect::to('/cronrat')->withErrors($v)->withInput();
		}
		  else
		{
		try {
				//create cronrat code
				DB::beginTransaction();

				$cronrat_code = $this->gen_uuid();
				$verify_code = $this->gen_uuid();

				$data = array(
                    'cronrat_code' => $cronrat_code,
                    'user_id' => Sentry::getUser()->id,
                    'cronrat_name' => $input['cronratName'],
                    'fail_email' => $input['failEmail'],
                    'fail_url' => $input['failUrl'],
                    'ttl' =>$input['ttl'],
                    'verify'=>$verify_code,
                    'active'=>0,
                    'created_on' =>time(),
                    'updated_on' => time()
                    );

				DB::table('cronrat')->insert(
                    $data
                );

				Log::info("DEBUG: " . DB::table('cronrat')->toSql());

				DB::commit();

				//send email with link to activate.

				Mail::send('emails.cronrat.verify', $data, function($m) use($data)
				{
				    $m->to($data['fail_email'])->subject('please verify Cronrat');
				});


				//success!
		    	Session::flash('success', 'Check email for activation link.');
		    	return Redirect::to('/cronrat')->withInput();

			}
			catch (Exception $e)
			{
			    Log::error("ERROR:" . $e->getMessage());
			    DB::rollback();
			    Session::flash('error', 'database error');
			    return Redirect::to('/cronrat')->withErrors($v)->withInput();
			}
		}
    }

    public function getUpgrade()
    {
        //Get the current user's id.
	    Sentry::check();
	    $user = Sentry::getUser();
		$allGroups = Sentry::getGroupProvider()->findAll();

		foreach ($allGroups as $group) {

    		if ($group->name == 'cronrat')
    		{
    				//The user should be added to this group

    		    if ($user->addGroup($group))
    			{
    			     Session::flash('success', 'Upgraded!');
    			}
    		    else
    			{
    			    Session::flash('error', 'Failed Upgrade!');
                }
    		}
		}

		return Redirect::to('/cronrat')->withInput();
    }

    public function postUpdate()
    {
        $url = Input::get('url');
        $success = Input::get('success');
        $error = Input::get('error');

        $action = Input::get('action');
        $actor = Input::get('actor');

        //here the coupling dont want to open whole $metod

        if($action == 'toggle' && $actor !='')
        {
            $res = $this->do_toggle($actor);
            $success = $res['success'];
            $error = $res['error'];
        }

        if($action == 'delete' && $actor !='')
        {
            $res = $this->do_delete($actor);
            $success = $res['success'];
            $error = $res['error'];
        }

        if($success){
            Session::flash('success', $success);
        }

        if($error){
            Session::flash('error', $error);
        }

        return Redirect::to($url);
    }

    public Function getPayment()
    {
        Session::flash('success', 'Check email for activation link.');
		return Redirect::to('/cronrat')->withInput();

    }

    public function getEdit($cronrat_id){
        Session::flash('success', 'Check email for activation link.');
		return Redirect::to('/cronrat')->withInput();
    }

    public function do_delete($cronrat_code){
            try {
    				$updated = 0;
    				DB::beginTransaction();

    				$updated= DB::table("cronrat")
    				    ->where("user_id", Sentry::getUser()->id)
    				    ->where("cronrat_code", $cronrat_code)
    				    ->delete();

    				Log::info("DEBUG: " . DB::table('cronrat')->toSql());

    				DB::commit();

    				if(!$updated)
    				{
    		    	    throw new Exception("Unverified! <a href='#needVerify' data-rel='popup' data-transition='slideup' class='ratId' id='$cronrat_code'>Resend Code</a>");
    				}

    				return array('success'=>'deleted!', 'error'=>'');
    			}
    			catch (Exception $e)
    			{
    			    Log::error("ERROR:" . $e->getMessage());
    			    DB::rollback();
    			    return array('success'=>false, 'error'=>$e->getMessage());
    			}
    }

    private function get_rats()
    {
        $data = array('rats'=>array());
        $rats = DB::table('cronrat')->where('user_id', Sentry::getUser()->id)->orderBy('cronrat_name','asc')->get();

        foreach($rats as $rat)
        {
            $data['rats'][] = $rat;
        }
        return $data;
    }

    public function getIn()
    {
       return Redirect::to('/cronrat')->withInput();
    }

    public function getIndex()
    {

        // User is logged in
	    $user = Sentry::getUser();

        if(empty($user)){
            return View::make('cronrat.faq');
        }
		// Get the user groups
		$groups = $user->getGroups();
		$pro = false;
		foreach($groups as $group)
		{
		    if('cronrat' == $group->name)
		    {
		        $pro = true;
		    }
		}

		$rats = $this->get_rats();
		$rats['pro'] = $pro;

        return View::make('cronrat.index')->with($rats);
    }

    private function is_active($cronrat_code){
        $rats = DB::table('cronrat')->where('user_id', Sentry::getUser()->id)->where('cronrat_code', $cronrat_code)->get();

        foreach($rats as $rat)
        {
            if($rat->verify !=''){
                return false;
            }
        }
        return true;
    }

    public function do_toggle($cronrat_code)
    {
        try {
    				$updated = 0;

    				if(!$this->is_active($cronrat_code))
    				{
    				    $res = DB::table('cronrat')->where('cronrat_code', $cronrat_code)->get();

    				    $res = json_decode(json_encode((array) $res), true);

    				     foreach($res as $data)
                        {
                            Mail::send('emails.cronrat.verify', $data, function($m) use($data)
            				{
            				    $m->to($data['fail_email'])->subject('please verify Cronrat');
            				});
                        }

    		    	    throw new Exception('Check email for activation link.');
    				}

    				DB::beginTransaction();
                    $sql = "UPDATE cronrat SET `active`=1-`active`, `updated_on`=" . time() . " WHERE verify='' and cronrat_code='" .  $cronrat_code . "' AND user_id='" . Sentry::getUser()->id . "'";
    				DB::statement($sql);

    				Log::info("DEBUG: " . DB::table('cronrat')->toSql());

    				DB::commit();

    				return array('success'=>'updated!', 'error'=>'');
    			}
    			catch (Exception $e)
    			{
    			    Log::error("ERROR:" . $e->getMessage());
    			    DB::rollback();
       			    return array('success'=>'', 'error'=>$e->getMessage());
    			}
    }

}