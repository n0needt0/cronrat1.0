<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \Lasdorf\CronratApi\CronratApi as Rat;
use \Lasdorf\CronratApi\Crontab as Crontab;

class CronratUrlTest extends Illuminate\Foundation\Testing\TestCase {

	public function createApplication()
	{
		$unitTesting = true;

		$testEnvironment = 'testing';

		$this->ratkey = 'coOSSWrq';
		$this->jobName = 'Test Rat';
		$this->specKey = $this->ratkey . '::specs::' . $this->jobName;
		$this->statusKey = $this->ratkey . '::status::' . $this->jobName;
		$this->server = 'cronrat.this.com';

		$this->raturl = '/r/' . $this->ratkey . '/' . urlencode($this->jobName);

		$this->parameters = array();
		$this->debug = array('debug'=>'');


		return require __DIR__.'/../../bootstrap/start.php';
	}

	public function testBase()
	{
	    $this->args = array_merge($this->parameters, $this->debug);

		$crawler = $this->call('GET', $this->raturl, $this->args, array(), array($this->server));
        $this->assertResponseOk();

        //check redis set
        $account = Rat::lookup('account::' . $this->ratkey);
        $this->assertTrue(isset($account));

        $status= Rat::lookup($this->statusKey);
        $this->assertTrue(isset($status));
        $expected = Rat::lookup($this->specKey);
        $this->assertTrue(isset($expected['nextrun']));
        $this->assertTrue(isset($expected['email']));
        $this->assertTrue(isset($expected['crontab']) && $expected['crontab'] == Config::get('cronrat.defaults.crontab'));
        $this->assertTrue(isset($expected['toutc']) && $expected['toutc'] == 0);
        $this->assertTrue(isset($expected['allow']) && $expected['allow'] == Config::get('cronrat.defaults.allow'));
        $ttl = Rat::ttl($this->statusKey);
        $this->assertTrue($expected['nextrun'] + $expected['allow'] <= time() + $ttl);

        //clean keys
        Rat::remove_dead_rat($this->statusKey);
        $status= Rat::lookup($this->statusKey);
        $this->assertFalse($status);

        Rat::remove_dead_rat($this->specKey);
        $status= Rat::lookup($this->specKey);
        $this->assertFalse($status);
	}

	public function testEveryHrMonFri()
	{
	    $this->parameters = array('crontab'=>'/15 0 * * 1-5','allow'=>5);
	    $this->args = array_merge($this->parameters, $this->debug);

		$crawler = $this->call('GET', $this->raturl, $this->args, array(), array($this->server));
        $this->assertResponseOk();

        //check redis set
        $account = Rat::lookup('account::' . $this->ratkey);
        $this->assertTrue(isset($account));

        $status= Rat::lookup($this->statusKey);
        $this->assertTrue(isset($status));
        $expected = Rat::lookup($this->specKey);
        $this->assertTrue(isset($expected['nextrun']));
        $this->assertTrue(isset($expected['email']));
        $this->assertTrue(isset($expected['crontab']) && $expected['crontab'] == urldecode($this->parameters['crontab']));
        $this->assertTrue(isset($expected['toutc']) && $expected['toutc'] == 0);
        $this->assertTrue(isset($expected['allow']) && $expected['allow'] == Config::get('cronrat.defaults.min_allow'));
        $ttl = Rat::ttl($this->statusKey);
        $this->assertTrue($expected['nextrun'] + $expected['allow'] <= time() + $ttl);

        //clean keys
        Rat::remove_dead_rat($this->statusKey);
        $status= Rat::lookup($this->statusKey);
        $this->assertFalse($status);

        Rat::remove_dead_rat($this->specKey);
        $status= Rat::lookup($this->specKey);
        $this->assertFalse($status);
	}

    public function testTimezone()
	{
	    $this->parameters = array('crontab'=>'00 09-18 * * 2','toutc'=>'-7', 'allow'=>300);
	    $this->debug = array('debug'=>'');
	    $this->args = array_merge($this->parameters, $this->debug);

		$crawler = $this->call('GET', $this->raturl, $this->args, array(), array($this->server));
        $this->assertResponseOk();

        //check redis set
        $account = Rat::lookup('account::' . $this->ratkey);
        $this->assertTrue(isset($account));

        $status= Rat::lookup($this->statusKey);
        $this->assertTrue(isset($status));
        $expected = Rat::lookup($this->specKey);

        print_r($expected);

        $this->assertTrue(isset($expected['nextrun']));
        $this->assertTrue(isset($expected['email']));
        $this->assertTrue(isset($expected['crontab']) && $expected['crontab'] == urldecode($this->parameters['crontab']));
        $this->assertTrue(isset($expected['toutc']) && $expected['toutc'] == '-7');
        $this->assertTrue(isset($expected['allow']));
        $ttl = Rat::ttl($this->statusKey);
        $this->assertTrue($expected['nextrun'] + $expected['allow'] <= time() + $ttl);

        echo "\n local time \n";
        echo date('Y-m-d H:i:s', $expected['nextrun']);

        echo "\n";

        echo date('Y-m-d H:i:s', (time()+$ttl));

        echo "\n";

        //clean keys
        Rat::remove_dead_rat($this->statusKey);
        $status= Rat::lookup($this->statusKey);
        $this->assertFalse($status);

        Rat::remove_dead_rat($this->specKey);
        $status= Rat::lookup($this->specKey);
        $this->assertFalse($status);



	    //offset time by 0hr, so timezone shoul update it to 5hr in local timezone

	    $this->parameters = array('crontab'=>'00 09-18 * * 2','TOUTC'=>0, 'allow'=>300);
	    $this->args = array_merge($this->parameters, $this->debug);

		$crawler = $this->call('GET', $this->raturl, $this->args, array(), array($this->server));
        $this->assertResponseOk();

        //check redis set
        $account = Rat::lookup('account::' . $this->ratkey);
        $this->assertTrue(isset($account));

        $status= Rat::lookup($this->statusKey);
        $this->assertTrue(isset($status));
        $expected = Rat::lookup($this->specKey);
        $this->assertTrue(isset($expected['nextrun']));
        $this->assertTrue(isset($expected['email']));
        $this->assertTrue(isset($expected['crontab']) && $expected['crontab'] == urldecode($this->parameters['crontab']));
        $this->assertTrue(isset($expected['toutc']));
        $this->assertTrue(isset($expected['allow']));
        $ttl = Rat::ttl($this->statusKey);
        $this->assertTrue($expected['nextrun'] + $expected['allow'] <= time() + $ttl);

        echo "\n PST Time \n";

        echo date('Y-m-d H:i:s', $expected['nextrun']);

        echo "\n";

        echo date('Y-m-d H:i:s', (time()+$ttl));



        //clean keys
        Rat::remove_dead_rat($this->statusKey);
        $status= Rat::lookup($this->statusKey);
        $this->assertFalse($status);

        Rat::remove_dead_rat($this->specKey);
        $status= Rat::lookup($this->specKey);
        $this->assertFalse($status);

	}
}