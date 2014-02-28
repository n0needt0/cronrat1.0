<?php namespace Lasdorf\CronratApi;

use Lasdorf\CronratApi\CronratBase;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Redis as Redis;
use Illuminate\Support\Facades\Config as Config;
use Illuminate\Log;


Class CronratApi extends CronratBase{

    public function __construct(){

    }

    /**
     * check for key in redis
     * @param string $ratkey
     */
    protected static function lookup($ratkey)
    {
        $result = Redis::get($ratkey);

        if($result === false)
        {
            \Log::info("miss $ratkey");
            return false;
        }
        \Log::info("hit $ratkey");
        return unserialize($result);
    }

    /**
     * Set key value in redis
     * @param string $ratkey
     * @param mixed $value
     * @param int $ttlsec
     */
    protected static function store($ratkey, $value, $ttlsec)
    {
        if( Redis::set($ratkey, serialize($value)) )
        {
            if( Redis::expire($ratkey, $ttl) )
            {
                \Log::info("stored $ratkey for $ttl sec");
                return true;
            }
              else
            {
                \Log::info("failed set expire on $ratkey for $ttl sec");
                 return false;
            }
        }
         \Log::info("failed store $ratkey for $ttl sec");
         return false;
    }

    /**
     * Returns account data or false
     * @param strings $ratkey
     * @return mixed <boolean, mixed>
     */
    public static function get_account($ratkey)
    {
        if(!$res = self::lookup("account::$ratkey"))
        {
            return false;
        }
        //return account info
        return $res;
    }

    /**
     * @param string $ratkey
     * retrieves all status rats for account
     */
    public static function get_account_rats($ratkey)
    {
        return Redis::keys($ratkey . '::status::*');
    }

     /**
     * @param string $ratkey
     * retrieves all rat specs for account
     */
    public static function get_account_rats_specs($ratkey)
    {
        return Redis::keys($ratkey . '::specs::*');
    }

    public static function set_rat($ratkey, $ratname, $ttl, $email, $url)
    {
         //set status key , this is sgnifies that rat is running
         $r = self::store($ratkey . '::status::' . $ratname, 1, $ttl * 60);

         //set specs array, this is what tells us what we expect should be alive and what to do if not
         $spec = array('ttl'=>$ttl, 'email'=>$email, 'url'=>$url);
         $s = self::store($ratkey . '::specs::' . $ratname, $spec, 7 * 24 * 60 * 60);
         //this tracks what we expect to be live
         $i = self::store('liveindex::' . $ratkey . '::' . $ratname, 1, 7 * 24 * 60 * 60);

         if( $r && $s && $i )
         {
             return true;
         }

         return false;
    }

    public static function check_set_rat($ratkey, $ratname, $ttlmin=1440, $email=false, $url=false )
    {
        //this function sets rat key ast ttl and rat spec key at ttl of 48 hr.
        //this also sets index key as index::$ratkey = 1 of ttl of 48 hr
        //rat key may eventually expire, then we will need to
        if(!is_int($ttlmin) || $ttlmin < 15 )
        {
            $ttlmin = 1440;
        }
        $ttlsec = $ttlmin *60;

        //see if user exists
        if(!$acct = get_account($ratkey))
        {
            throw new Exception("Invalid account");
            return false;
        }

        if(!$email)
        {
            $email = $acct['email'];
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
       {
            throw new Exception("Invalid email");
            return false;
        }

        //see if rat exists, if so update its ttl otherwise go forward
        if($rat = self::lookup($ratkey . '::status::' . $ratname))
        {
            //it is here just update update.
            return self::set_rat($ratkey, $ratname, $ttl, $email, $url);
        }

        //this is new rat, so more work required

        $user_rats = self::get_account_rats($ratkey);

        if(count($user_rats) > $acct['ratlimit'])
        {
            throw new Exception("Too many Rats, upgrade account");
            return false;
        }

        if($ttlmin < $acct['ttlmin'])
        {
            throw new Exception("Time too short upgrade account");
            return false;
        }

        if($url && !$acct['url'])
        {
            throw new Exception("Cannot use url feature. Upgrade account");
            return false;
        }

        //set url
        return self::set_rat($ratkey, $ratname, $ttl, $email, $url);
    }
}