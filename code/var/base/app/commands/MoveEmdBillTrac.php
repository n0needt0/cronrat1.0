<?php
include_once("MoveEmdBase.php");

Class MoveEmdBillTrac extends MoveEmdBase{

    protected $name = 'command:moveemdbilltrac';

    public function __construct(){
        parent::__construct();
        $this->to = 'billtrac';
    }


 private function is_billtrac_exists($emd_invoice_id)
    {
        try{

            $id = false;
            $res = DB::connection('billtrac')->table("ticket_custom")->whereRaw('`name`= "emdinvoicenumber" AND `value` = "' . $emd_invoice_id .'"')->get();

            foreach($res as $r)
            {
                //there could be case when ref integrity is broken.
                //so we peek if base of ticket exists too and if it is not we will whipe loose ends
                $id = $r->ticket;
            }

            $root = false;

            if($id)
            {
                $res = DB::connection('billtrac')->table("ticket")->whereRaw("`id`= '" . $id . "'")->get();
                foreach($res as $r)
                {
                    $root = $r->id;
                }

                if(empty($root))
                {
                    //looks like torn ticket
                    //ts clean is
                    $this->error('Billtrac ticket ' . $id . ' is shredded, removing remnants');
                    if( \Config::get('app.emdtobilltrac.db_delete'))
                    {
                        DB::connection('billtrac')->table("ticket_custom")->whereRaw("`ticket`='" . $id . "'")->delete();
                    }
                    return false;
                }

                return $id;
            }

            return false;
        }
          catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }

  public function _create($refid, $invoice, $charges=array(), $payments=array())
  {
      if($res = $this->is_billtrac_exists($invoice->InvoiceNumber_EMD))
      {
           //already created
           $this->error("billtrac ticket # $res already exists for EMD invoice # " . $invoice->InvoiceNumber_EMD);

           //update so
           if( \Config::get('app.emdtobilltrac.db_write'))
           {
               DB::table($this->keytable)->where('emd_invoice_number',$invoice->InvoiceNumber_EMD)->update(array( 'todo'=>'done','ext_key'=>$res));
           }

           return false;
      }

      /**
       * inserting
       * - summary
       * - carrier
       * - emdId
       * - type
       * - claim
       * - location
       * - mail date
       * - bill amount
       * - ptrac id
       */


      try{

             $time = $changetime = time() * 1000000;

             DB::connection('billtrac')->getPdo()->beginTransaction();

             //create base ticket
             $invoice->Invoice_Comment = trim($invoice->Invoice_Comment);

             if(empty($invoice->Invoice_Comment))
             {
                 $type = 'new';
             }
                else
             {
                 $type = addslashes($invoice->Invoice_Comment);
             }

             $dos_first=false;
             $dos = array();

             if($cpts = DB::connection('emds')->table('VIEW_API_InvoiceCPT')->where('Invoice_ID', $invoice->Invoice_ID)->get())
             {
                 foreach($cpts as $line)
                 {
                     if(!$dos_first)
                     {
                         $dos_first = $line->sdos;
                     }
                     $dos[$line->sdos] = 1;
                 }
                 $dos = 'DOS: ' . implode(', ', array_keys($dos));

             }

             $data = array(
                                       'type'=>$type,
                                       'time'=>$time,
                                       'changetime'=>$changetime,
                                       'priority'=>'normal',
                                       'status'=>'new',
                                       'summary'=>addslashes($invoice->PatientName),
                                       'reporter'=>'emdimport',
                                       'description'=>$dos,
                                       'owner'=> $this->assign_ownerbyalpha(strtolower(substr($invoice->PatientName,0,1)))
                                   );

             if($owner = $this->assign_ownerbyinsurance(strtolower($invoice->InsuranceCompName)))
             {
                 $data['owner'] = $owner;
             }

             if( \Config::get('app.emdtobilltrac.db_write'))
             {
                  DB::connection('billtrac')->table('ticket')->insert( $data );
             }
              else
             {
                  $this->info("dry insert: \n" . print_r($data, true));
             }

             $id = DB::connection('billtrac')->getPdo()->lastInsertId();

             $this->info("created ticket number $id");

             $importMap = array(
                                       'billdate'=>$invoice->InvoiceCreatedAt,
                                       'emdstatus'=>$invoice->InvoiceStatus_Code,
                                       'maildate'=>$invoice->InvoiceCreatedAt,
                                       'dosdate'=>$dos_first,
                                       'billamount'=>number_format($invoice->InvoiceTotal,2),
                                       'duedate'=>date_create_from_format('m-d-Y', $invoice->InvoiceCreatedAt)->modify('+17 day')->format('m-d-Y'),
                                       'paidamount'=>number_format($invoice->PaymentTotal,2),
                                       'location'=>$this->translateLocation($invoice->Organization_Name),
                                       'carrier'=>addslashes($invoice->InsuranceCompName),
                                       'claimnumber'=>$invoice->Case_ClaimNumber,
                                       'step'=>'new',
                                       'emdinvoicenumber'=>$invoice->InvoiceNumber_EMD,
                                       'ptracnumber'=>'EMD:'.$invoice->InvoiceNumber_EMD
                                   );

             foreach($importMap as $key=>$value)
            {
                  $data = array(
                                       'ticket'=>$id,
                                       'name'=>$key,
                                       'value'=>$value
                                       );

                  if( \Config::get('app.emdtobilltrac.db_write'))
                  {
                      DB::connection('billtrac')->table('ticket_custom')->insert( $data );
                  }
                   else
                  {
                      $this->info("dry insert: \n" . print_r($data, true));
                  }
            }

            DB::connection('billtrac')->getPdo()->commit();

            if( \Config::get('app.emdtobilltrac.db_write'))
            {
                return DB::table($this->keytable)->where('id',$refid)->update(array( 'todo'=>'done', 'ext_key'=>$id));
            }
              else
            {
                return false;
            }

      }catch (Exception $e)
      {
          DB::connection('billtrac')->getPdo()->rollBack();
          throw new Exception($e->getMessage());
      }
  }

public function _update($refid, $invoice, $charges=array(), $payments=array())
  {
      if(!$res = $this->is_billtrac_exists($invoice->InvoiceNumber_EMD))
      {
           //billtrac is not yet create so set it
           $this->error("billtract ticket does not yet exists for EMD invoice # " . $invoice->InvoiceNumber_EMD);
           //update so

           if( \Config::get('app.emdtobilltrac.db_write'))
           {
               DB::table($this->keytable)->where('emd_invoice_number',$invoice->InvoiceNumber_EMD)->update(array( 'todo'=>'insert'));
           }

           return false;
      }

      $billtracid = $res;

      /**
       * Since we have no idea what is updated we simply update every thing we inserted before
       * - inserting
       * - summary
       * - carrier
       * - emdId
       * - type
       * - claim
       * - location
       * - mail date
       * - bill amount
       * - ptrac id
       */


      try{
            $time = $changetime = time() * 1000000;

            DB::connection('billtrac')->getPdo()->beginTransaction();

             //create base ticket
             $invoice->Invoice_Comment = trim($invoice->Invoice_Comment);
             if(empty($invoice->Invoice_Comment))
             {
                 $type = 'new';
             }
                else
             {
                 $type = addslashes($invoice->Invoice_Comment);
             }

             $dos_first=false;
             $dos = array();

             if($cpts = DB::connection('emds')->table('VIEW_API_InvoiceCPT')->where('Invoice_ID', $invoice->Invoice_ID)->get())
             {
                 foreach($cpts as $line)
                 {
                     if(!$dos_first)
                     {
                         $dos_first = $line->sdos;
                     }
                          $dos[$line->sdos] = 1;
                 }
                 $dos = 'DOS: ' . implode(', ', array_keys($dos));
             }

             $data = array(
                                       'type'=>$type,
                                       'time'=>$time,
                                       'changetime'=>$changetime,
                                       'summary'=>addslashes($invoice->PatientName),
                                       'description'=>$dos,
                                       'owner'=> $this->assign_ownerbyalpha(strtolower(substr($invoice->PatientName,0,1)))
                                   );

             if($owner = $this->assign_ownerbyinsurance(strtolower($invoice->InsuranceCompName)))
             {
                 $data['owner'] = $owner;
             }

             if( \Config::get('app.emdtobilltrac.db_write'))
             {
                 DB::connection('billtrac')->table('ticket')->where('id',$billtracid)->update( $data );
             }
               else
             {
                 $this->info("dry insert: \n" . print_r($data, true));
             }

             $this->info("updated ticket number $billtracid");

              $importMap = array(
                                       'emdstatus'=>$invoice->InvoiceStatus_Code,
                                       'billdate'=>$invoice->InvoiceCreatedAt,
                                       'maildate'=>$invoice->InvoiceCreatedAt,
                                       'dosdate'=>$dos_first,
                                       'billamount'=>number_format($invoice->InvoiceTotal,2),
                                       'duedate'=>date_create_from_format('m-d-Y', $invoice->InvoiceCreatedAt)->modify('+30 day')->format('m-d-Y'),
                                       'paidamount'=>number_format($invoice->PaymentTotal,2),
                                       'location'=>$this->translateLocation($invoice->Organization_Name),
                                       'carrier'=>addslashes($invoice->InsuranceCompName),
                                       'claimnumber'=>$invoice->Case_ClaimNumber,
                                       'emdinvoicenumber'=>$invoice->InvoiceNumber_EMD,
                                       'ptracnumber'=>'EMD:'.$invoice->InvoiceNumber_EMD
                                   );

             foreach($importMap as $key=>$value)
            {
                $sql = "INSERT INTO `ticket_custom` ( `ticket`,`name`,`value` ) VALUES ( '$billtracid', '$key', '$value' ) ON DUPLICATE KEY UPDATE `value` = '$value'";
                if( \Config::get('app.emdtobilltrac.db_write'))
                {
                    DB::connection('billtrac')->statement($sql);
                }
                 else
                {
                    $this->info("dry insert: \n" . print_r($data, true));
                }
            }

            DB::connection('billtrac')->getPdo()->commit();

            if( \Config::get('app.emdtobilltrac.db_write'))
            {
                return DB::table($this->keytable)->where('id',$refid)->update(array( 'todo'=>'done', 'ext_key'=>$billtracid));
            }
             else
            {
                return false;
            }

      }catch (Exception $e)
      {
          DB::connection('billtrac')->getPdo()->rollBack();
          throw new Exception($e->getMessage());
      }
  }

   protected function assign_ownerbyalpha($char)
   {
        foreach(Config::get('app.emdtobilltrac.alpha_owner') as $alpha)
        {
            if($char >= $alpha['start'] && $char <= $alpha['end'])
            {
                return $alpha['owner'];
            }
        }

        return 'tbd';
    }

   protected function assign_ownerbyinsurance($orgname)
   {
        foreach(Config::get('app.emdtobilltrac.insurance_owner') as $pattern)
        {
            if(stristr($orgname, $pattern['pattern']))
            {
                $this->info("owner by insurance : $orgname : " . $pattern['owner']);
                return $pattern['owner'];
            }
        }

        return false;
    }


}
