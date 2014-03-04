<?php

use Illuminate\Http\Response;

use Predis\ResponseErrorTest;

use Lasdorf\Utils as Utils;

use Lasdorf\FormattedJsonResponse\FormattedJsonResponse;

use Lasdorf\CronratApi\CronratApi;

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	protected function json_out($data=array())
	{

        $response = new FormattedJsonResponse($data);

        $response->setCallback(Input::get('callback'));

        if(Config::get('app.debug_json'))
        {
            //overwriteData with formatted MAY BE BUSTED later
            $response-> indent_json();
        }

        return $response;
	}

function gen_uuid($len=8)
{
    $hex = md5(time() . uniqid("", true));

    $pack = pack('H*', $hex);

    $uid = base64_encode($pack);        // max 22 chars

    $uid = preg_replace("/[^A-Za-z0-9]/", "", $uid);    // mixed case

    if ($len<4)
        $len=4;
    if ($len>128)
        $len=128;                       // prevent silliness, can remove

    while (strlen($uid)<$len)
        $uid = $uid . gen_uuid(22);     // append until length achieved

    return substr($uid, 0, $len);
}
}