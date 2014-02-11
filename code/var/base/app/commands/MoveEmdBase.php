<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Log;

class MoveEmdBase extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = '';

    protected $to = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Connect to eMds and moves its data to consumers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function build_schema($key)
    {

        //create main job table

        $table_name= 'emd_'.$key.'_refs';

        if (!Schema::hasTable($table_name))
        {
                Schema::create($table_name, function($table)
                {
                    $table->increments('id');
                    $table->string('emd_invoice_number');
                    $table->string('emd_invoice_id');
                    $table->string('ext_key');
                    $table->string('todo');
                    $table->string('todopayment');
                    $table->string('timekey');
                    $table->string('paymentkey');
                    $table->text('invoice');
                    $table->text('charges');
                    $table->text('payments');
                    $table->timestamps();

                    // We'll need to ensure that MySQL uses the InnoDB engine to
                    // support the indexes, other engines aren't affected.
                    $table->engine = 'InnoDB';
                    $table->unique('emd_invoice_number');
                    $table->unique('emd_invoice_id');
                    $table->index('ext_key');
                });

                //stupid schema can not create right


                $sql = "ALTER TABLE $table_name CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                DB::statement($sql);
        }

        //create main errors

        $table_name= 'emd_errs';

        if (!Schema::hasTable($table_name))
        {
                Schema::create($table_name, function($table)
                {
                    $table->increments('id');
                    $table->string('emd_invoice_id');
                    $table->string('ext_key');
                    $table->string('during');
                    $table->text('error');
                    $table->text('invoice');
                    $table->text('charges');
                    $table->text('payments');
                    $table->timestamps();

                     $table->unique(array('emd_invoice_number', 'during', 'error'));
                    // We'll need to ensure that MySQL uses the InnoDB engine to
                    // support the indexes, other engines aren't affected.
                    $table->engine = 'InnoDB';
                });

                //stupid schema can not create right

                $sql = "ALTER TABLE $table_name CHANGE COLUMN updated_at updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
                DB::statement($sql);
        }
    }
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $dowhat = $this->argument('dowhat');
        $this->done = array('insert'=>0,'update'=>0);

        //hard coded here since any adition will require touching this

        $this->validto = array('billtrac','netsuite');

        $env = $this->option('env');

        if(empty($dowhat) || empty($env))
        {
            $this->info('usage===> php artisan command:[movetoemd|movetonetsuite] [insert, update,all] --env=local|production ');

            if(empty($env))
            {
                $this->error('--env option required!');
                die;
            }

            if(!in_array($this->to, $this->validto))
            {
                $this->error('invalid destination to option required!');
                die;
            }
        }

        //set and create key table if needed
        $this->keytable= 'emd_'.$this->to.'_refs';

        $this->build_schema($this->to);

        $workload = array('update'=>0, 'insert'=>0, 'done'=>0);

        try{
                $invoices_src = DB::connection('emds')->table("VIEW_Export_InvoiceIndex")->get();
                $invoice_dst = DB::connection()->table($this->keytable)->get();

                //generate lookup, perhaps we should put it in REDIS later
                $refs = array();
                $payments = array();
                $active_invoice_ids = array();

                foreach($invoice_dst as $dest_ref)
                {
                    $refs[$dest_ref->emd_invoice_number] = $dest_ref->timekey;
                    $payments[$dest_ref->emd_invoice_number] = $dest_ref->paymentkey;

                    //calculate exsting workload
                    if($dest_ref->todo)
                    {
                        $workload[$dest_ref->todo]++;
                    }
                }

                $this->info('existing_workload:' . json_encode($workload));

                foreach($invoices_src as $invoice)
                {
                    foreach($invoice as &$value)
                    {
                        $value = mb_convert_encoding($value, "UTF-8", "Windows-1252");
                    }

                    $active_invoice_ids[$invoice->InvoiceNumber_EMD] = 1;

                    // write as acid queue

                    if(!isset($refs[$invoice->InvoiceNumber_EMD]))
                    {
                        //convert to UTF 8
                        $arrinvoice =  (array)$invoice;

                        //this is totally new new invoice
                        $data = array(
                                        'emd_invoice_number'=>$invoice->InvoiceNumber_EMD,
                                        'emd_invoice_id'=>$invoice->Invoice_ID,
                                        'todo'=>'insert',
                                        'invoice' => json_encode((array)$invoice),
                                        'charges' => json_encode((array)$this->getCharges($invoice->Invoice_ID)),
                                        'payments' => json_encode((array)$this->getPayments($invoice->Invoice_ID)),
                                        'timekey' => md5($invoice->InvoiceUpdatedAt),
                                        'paymentkey' => md5($invoice->PaymentTotal),
                                        'created_at'=>date('Y-m-d H:i:s', time())
                                    );

                        if( ('netsuite' == $this->to) && ((int)$invoice->PaymentTotal > 0))
                        {
                            $data['todopayment'] = 'true';
                        }

                        if(DB::table($this->keytable)->insert( $data ))
                        {
                            $workload['insert']++;
                        }

                    }
                      else if(($refs[$invoice->InvoiceNumber_EMD] != md5($invoice->InvoiceUpdatedAt) ) || ($payments[$invoice->InvoiceNumber_EMD] != md5($invoice->PaymentTotal)))
                    {

                        //this needs to be updated
                        $data = array(
                                        'todo'=>'update',
                                        'invoice' => json_encode((array)$invoice),
                                        'charges' => json_encode((array)$this->getCharges($invoice->Invoice_ID)),
                                        'payments' => json_encode((array)$this->getPayments($invoice->Invoice_ID)),
                                        'timekey' => md5($invoice->InvoiceUpdatedAt),
                                        'paymentkey' => md5($invoice->PaymentTotal)
                                    );

                        if( ('netsuite' == $this->to) && ((int)$invoice->PaymentTotal > 0))
                        {
                            $data['todopayment'] = 'true';
                        }

                        if(DB::table($this->keytable)
                            ->where('emd_invoice_number',$invoice->InvoiceNumber_EMD)
                            ->update($data))
                        {
                            $workload['update']++;
                        }
                    }
                      else
                    {
                        //up to date
                    }
                }

                if(('insert' == $dowhat) || ('all' == $dowhat))
                {
                    $this->info('processing:' . json_encode(array('insert'=>$workload['insert'])));
                    $this->do_inserts();
                }

                if(('update' == $dowhat) || ('all' == $dowhat))
                {
                    $this->info('processing:' . json_encode(array('update'=>$workload['update'])));
                    $this->do_updates();
                }

                //cleanup sometimes invoices are deleted from emds no need to drag them
                foreach($refs as $k=>$i)
                {
                     if(!isset($active_invoice_ids[$k]))
                     {
                          //clean refs table
                          $workload = DB::connection()->table($this->keytable)->where('emd_invoice_number',$k)->delete();
                     }
                }

                $data = array('done'=>$this->done);

                Mail::send('emd.moveto', $data, function($message)
                {
                    $messagebody = 'Emd Move ' . $this->to . ' : ' . print_r($this->done, true);
                    $message->to(Config::get('app.emails.admin'))->subject($messagebody);
                    \Log::info($messagebody);
                });

        }
          catch(Exception $e)
        {
                $this->error($e->getMessage());
        }
    }

    protected function getCharges($emd_invoice_id)
    {
        $res = DB::connection('emds')->table("VIEW_API_InvoiceCPT")->where('Invoice_ID', $emd_invoice_id)->get();
        $result = array();

        foreach($res as $r)
        {
            $result[] = (array)$r;
            //unset secure data here
        }

        return $result;

    }

    protected function getPayments($emd_invoice_id)
    {
       $res = DB::connection('emds')->table("VIEW_API_PaymentIndex")->where('Invoice_ID', $emd_invoice_id)->get();
       $result = array();

        foreach($res as $r)
        {
            $result[] = (array)$r;
            //unset secure data here
        }

        return $result;
    }

    protected function do_inserts()
    {
        try{
                 $workload = DB::connection()->table($this->keytable)->where('todo','insert')->get();

                 foreach($workload as $create)
                 {
                     $this->info($create->invoice);
                     $invoice = json_decode($create->invoice);
                     $charges = json_decode($create->charges);
                     $payments = json_decode($create->payments);
                     $this->_create($create->id, $invoice, $charges, $payments);
                     $this->done['insert']++;
                 }
        }
         catch(Exception $e)
         {
             throw new Exception($e->getMessage());
         }

    }

    protected function do_updates()
    {
         try{
                     $workload = DB::connection()->table($this->keytable)->where('todo','update')->get();
                     foreach($workload as $update)
                     {
                         $this->info($update->invoice);
                         $invoice = json_decode($update->invoice);
                         $charges = json_decode($update->charges);
                         $payments = json_decode($update->payments);
                         $this->_update($update->id, $invoice, $charges, $payments);
                         $this->done['update']++;
                     }
            }
             catch(Exception $e)
             {
                 throw new Exception($e->getMessage());
             }
    }

    protected function translateLocation($location='na')
    {
        $locs = Config::get('app.locationmap');
        if(empty($locs) || empty($locs[$location]))
        {
            return $location;
        }
        return $locs[$location];
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('dowhat', InputArgument::OPTIONAL, 'Do What'),
        );
    }
}