<?php

class VerifyController extends BaseController {

    public function __construct()
    {

    }

    public function missingMethod($parameters = array())
    {
        try{
              App::abort(404);
        }catch(Exception $e){
              return Response::make('404 Not Found!', 404, array());
        }
    }

    public function getCronrat($verify)
    {
        try {
    				DB::beginTransaction();

    				$update = DB::table('cronrat')->where('verify',$verify)->
    				update(array('verify'=>'', 'active'=>1));

    				Log::info("DEBUG: " . DB::table('cronrat')->toSql());

    				DB::commit();

    				if(!$update)
    				{
    				    throw new Exception('Invalid Activation Code');
    				}

    				Session::flash('success', 'Activated');
		    	    return Redirect::to('/cronrat')->withInput();
    			}
    			catch (Exception $e)
    			{
    			    Log::error("ERROR:" . $e->getMessage());
    			    DB::rollback();
                    return "ERROR:" . $e->getMessage();
    			}
    }
}