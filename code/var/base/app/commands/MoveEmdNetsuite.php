<?php
include_once("MoveEmdBase.php");

use \Lasdorf\Netsuite\EmdNetsuite as EmdNetsuite;

Class MoveEmdNetsuite extends MoveEmdBase{

  protected $name = 'command:moveemdnetsuite';

  public function __construct()
  {
      parent::__construct();
      $this->to = 'netsuite';
      $this->_items = array();
      //this is local cache
      $this->map = \Config::get('app.emdtonetsuite.servicemap');

      $this->rigged = false;

      $this->skip = array();

      $res = DB::connection()->table('emd_errs')->where('during','netsuite')->get();


      foreach($res as $line)
      {
          $this->skip[] = $line->emd_invoice_id;
      }
  }

  protected function riggme()
  {
       if($this->rigged) return;

       foreach($this->map as $k=>$v)
      {
          if($res = EmdNetsuite::returnReadResponse(EmdNetsuite::getIt('nonInventorySaleItem', $v->internalId)))
          {
               $this->map[$k]->itemId = $res->itemId;
               $this->map[$k]->department=(object)array('internalId'=>$res->department->internalId);
               $this->map[$k]->class=(object)array('internalId'=>$res->class->internalId);
               $this->map[$k]->incomeAccount=(object)array('internalId'=>$res->incomeAccount->internalId);
               $this->map[$k]->taxSchedule=(object)array('internalId'=>$res->taxSchedule->internalId);
          }
      }

      $this->rigged = true;
  }

  protected function mapService($service)
  {
      //TODO ADD Memcache here
       foreach($this->map as $k=>$v)
       {
           $this->info("looking for $service in " .strtolower($k));
           if(str_contains(strtolower($service), strtolower($k)))
           {
               return $v;
           }
       }
       return false;
  }

  public function _dopayment($emd_invoice_id, $payments)
  {
        if(count($payments)<1)
        {
            return 1; //nothing to do
        }

        //get invoice
        $existing_invoice = EmdNetsuite::findInvoice($emd_invoice_id);
        $res = EmdNetsuite::returnSearchResponseInternalId( $existing_invoice);

        if( !$res )
        {
              //not yet created
              $msg = "Netsuite invoice # $res does not exists yet for EMD invoice # " . $emd_invoice_id;
              $this->error($msg);
              Log::warning($msg);
              return false;
        }
          else
       {
          $existing_invoice_array = EmdNetsuite::returnSearchResponse($existing_invoice);
          if(count($existing_invoice_array) < 1)
          {
              //not yet created
              $msg = "Netsuite invoice # $res does not exists yet for EMD invoice # " . $emd_invoice_id;
              $this->error($msg);
              Log::warning($msg);
              return false;
          }

          $existing_invoice = $existing_invoice_array[0];
          unset($existing_invoice_array);

          $netsuite_invoice_id = $existing_invoice->internalId;
          $netsuite_customer_id = $existing_invoice->entity->internalId;
       }

       $i = 0;

       foreach($payments as $k=>$payment)
       {
            //see if payment already posted
            $existing_payment = EmdNetsuite::findPayment($emd_invoice_id, $payment->Payment_ID);
            $res = EmdNetsuite::returnReadResponseInternalId( $existing_payment);
            $existing_payment = EmdNetsuite::returnReadResponse($existing_payment);

            if(!$res)
            {
                //not yet created, so attempt
                $msg = "Applying payment " . print_r($payment, true);
                $this->info($msg);
                Log::info($msg);

                try{
                       //first unpack invoice and get required values
                        if($res = EmdNetsuite::addPayment( $netsuite_invoice_id, $emd_invoice_id, $netsuite_customer_id, $payment))
                        {
                             $msg = "Applied payment " . print_r($payment, true);
                             $this->info($msg);
                             Log::info($msg);
                             $i++;
                        }
                          else
                        {
                            return false; //error applying payment leave it to be updated for now
                        }
                    }
                     catch (Exception $e)
                    {
                        $msg = $e->getMessage();
                        $this->error($msg);
                        Log::warning($msg);
                    }
            }
              else
            {
                //payment already posted doing update
                $msg = "Payment " . $payment->Payment_ID . ' in amount ' . $payment->Payment_Payment . " already posted. Updating...";
                $this->error($msg);
                Log::warning($msg);

                //first unpack invoice and get required values
                if($res = EmdNetsuite::updatePayment( $netsuite_invoice_id, $emd_invoice_id, $netsuite_customer_id, $existing_payment, $payment))
                {
                     $msg = "Updated payment " . print_r($payment, true);
                     $this->info($msg);
                     Log::info($msg);
                     $i++;
                }
                  else
                {
                    return false; //error applying payment leave it to be updated for now
                }
                $i++;
            }
       }

       return $i;
  }

  public function _create($refid, $invoice, $charges=array(), $payments=array())
  {

     if(in_array($invoice->InvoiceNumber_EMD, $this->skip))
     {
         return;
     }

     $this->riggme();

     try{
              //first search for duplicate invoice on external key
              $res = EmdNetsuite::returnSearchResponseInternalId( EmdNetsuite::findInvoice($invoice->InvoiceNumber_EMD));

              if( $res )
              {
                   //already created
                   $msg = "Netsuite invoice # $res already exists for EMD invoice # " . $invoice->InvoiceNumber_EMD . " marking for an update";
                   $this->error($msg);
                   Log::warning($msg);
                   //update so
                   DB::table($this->keytable)->where('emd_invoice_number',$invoice->InvoiceNumber_EMD)->update(array( 'todo'=>'update','ext_key'=>$res));
                   return false;
              }

              //for customer search insurance in netsuite if not found create

              if(''==trim($invoice->InsuranceCompName))
              {
                  $invoice->InsuranceCompName = 'private pay';
                  $msg = "NO insurance company set for EMD invoice " . $invoice->InvoiceNumber_EMD;
                  Log::warning($msg);
              }

              if( $customer_internalId = EmdNetsuite::returnSearchResponseInternalId( EmdNetsuite::findCustomer($invoice->InsuranceCompName) ) )
              {
                  $msg = "found " . $invoice->InsuranceCompName . ', internalId:' . $customer_internalId;
                  $this->info($msg);
                  Log::warning($msg);
              }
                else
              {
                   //lets create one
                   $customer_internalId = EmdNetsuite::addCustomer($invoice->InsuranceCompName);
              }

              if(empty($customer_internalId))
              {
                  $msg = 'Can not add customer! exiting: ' . print_r($customer, true);
                  $this->error($msg);
                  Log::error($msg);
                  return;
              }

              //find root for this invoice by mapping
              if(!$root = $this->mapService($invoice->Invoice_Comment))
              {
                  $root = $this->map["catchall"];
              }

              if(empty($root))
              {
                  $msg = "Can not find root item category! exiting.";
                  $this->error($msg);
                  Log::error($msg);
                  die;
              }

              $root = (object)$root;

              $this->info("found " . $root->itemId);

              foreach($charges as $i => $charge)
              {
                  //foreach item in invoice, search netsuite, if not found create and pick the refid from netsuite;

                  $search = array( 'itemId' => array('operator'=>'is', 'searchValue' =>$root->itemId . ' : ' . $charge->InvoiceCPT_Code));

                  if(!$item_id = EmdNetsuite::returnSearchResponseInternalId(EmdNetsuite::findItem($search)))
                  {
                      $new_item = (object)array('name'=>$charge->InvoiceCPT_Code, 'description'=>$charge->InvoiceCPT_CodeBillDescription, 'root'=>$root);
                      $this->info("adding item " . $charge->InvoiceCPT_Code);
                      $item_id = EmdNetsuite::addItem($new_item);
                  }

                  $this->info("found item $item_id");

                  //TODO remove block when all solid update item here
                  if(false)
                  {
                      if(empty($this->_items[$item_id]))
                      {

                          if($existing_item = EmdNetsuite::returnReadResponse(EmdNetsuite::getIt('nonInventorySaleItem', $item_id)))
                          {
                              if($update = EmdNetsuite::updateRecord($existing_item))
                              {
                                  $updated_item = EmdNetsuite::returnReadResponse(EmdNetsuite::getIt('nonInventorySaleItem', $item_id));
                                  $this->info("updated item $item_id");
                                  $this->_items[$item_id] = 1;
                              }
                          }
                      }
                  }

                  $charges[$i]->netsuite_item_id = $item_id;
              }

              $invoice->customer_id = $customer_internalId;
              $invoice->charges = $charges;


              if($new_invoice_id = EmdNetsuite::addInvoice($invoice))
              {
                  //update with new netsuite invoice id to ext_key..
                  $this->info('inserted invoice ' . $new_invoice_id);
                  DB::table($this->keytable)->where('id',$refid)->update(array( 'todo'=>'done', 'ext_key'=>$new_invoice_id));

                  //process payments
                  if($apply_payments = $this->_dopayment($invoice->InvoiceNumber_EMD, $payments))
                  {
                     return DB::table($this->keytable)->where('id',$refid)->update(array( 'todo'=>'done', 'todopayment'=>'', 'ext_key'=>$new_invoice_id));
                  }
              }
                else
              {
                  $msg = "Failed to insert invoice ";
                  $this->error($msg);
                  Log::error($msg);
              }

      }catch (Exception $e)
      {
          throw new Exception('Exception:' . $e->getMessage());
      }
  }

public function _update($refid, $invoice, $charges=array(), $payments=array())
{
    if(in_array($invoice->InvoiceNumber_EMD, $this->skip))
    {
        return;
    }

    $this->riggme();

      try{

             $existing_invoice = EmdNetsuite::findInvoice($invoice->InvoiceNumber_EMD);
             $res = EmdNetsuite::returnSearchResponseInternalId( $existing_invoice);

              if( !$res )
              {
                   //not yet created
                   $msg = "Netsuite invoice # $res does not exists yet for EMD invoice # " . $invoice->InvoiceNumber_EMD;
                   $this->error($msg);
                   Log::warning($msg);
                   //update so
                   DB::table($this->keytable)->where('emd_invoice_number',$invoice->InvoiceNumber_EMD)->update(array( 'todo'=>'insert','ext_key'=>''));
                   return false;
              }
               else
              {
                $existing_invoice = $existing_invoice->searchResult->recordList->record[0];
              }

              //for customer search insurance in netsuite if not found create
              if(''==trim($invoice->InsuranceCompName))
              {
                   $invoice->InsuranceCompName = 'private pay';
                   $msg = "NO insurance company set for EMD invoice " . $invoice->InvoiceNumber_EMD;
                   Log::warning($msg);
              }

              if( $customer_internalId = EmdNetsuite::returnSearchResponseInternalId( EmdNetsuite::findCustomer($invoice->InsuranceCompName) ) )
              {
                  $msg = "found " . $invoice->InsuranceCompName . ', internalId:' . $customer_internalId;
                  $this->info($msg);
                  Log::info($msg);
              }
                else
              {
                   //lets create one
                   $customer_internalId = EmdNetsuite::addCustomer($invoice->InsuranceCompName);
              }

              if(empty($customer_internalId))
              {
                  $msg = 'Can not add customer! exiting: ' . print_r($customer, true);
                  $this->error($msg);
                  Log::error($msg);
                  return;
              }

              //find root for this invoice by mapping
              if(!$root = $this->mapService($invoice->Invoice_Comment))
              {
                  $root = $this->map["catchall"];
              }

              if(empty($root))
              {
                  $msg = "Can not find root item category! exiting.";
                  $this->error($msg);
                  Log::error($msg);
                  die;
              }

              $root = (object)$root;

              $this->info("found " . $root->itemId);

              foreach($charges as $i => $charge)
              {
                  //foreach item in invoice, search netsuite, if not found create and pick the refid from netsuite;

                  $search = array( 'itemId' => array('operator'=>'is', 'searchValue' =>$root->itemId . ' : ' . $charge->InvoiceCPT_Code));

                  if(!$item_id = EmdNetsuite::returnSearchResponseInternalId(EmdNetsuite::findItem($search)))
                  {
                      $new_item = (object)array('name'=>$charge->InvoiceCPT_Code, 'description'=>$charge->InvoiceCPT_CodeBillDescription, 'root'=>$root);
                      $this->info("adding item " . $charge->InvoiceCPT_Code);
                      $item_id = EmdNetsuite::addItem($new_item);
                  }

                  $this->info("found item $item_id");

                  //TODO remove block when all solid update item here
                  if(false)
                  {
                      if(empty($this->_items[$item_id]))
                      {
                          if($existing_item = EmdNetsuite::returnReadResponse(EmdNetsuite::getIt('nonInventorySaleItem', $item_id)))
                          {
                              if($update = EmdNetsuite::updateRecord($existing_item))
                              {
                                  $updated_item = EmdNetsuite::returnReadResponse(EmdNetsuite::getIt('nonInventorySaleItem', $item_id));
                                  $this->info("updated item $item_id");
                                  $this->_items[$item_id] = 1;
                              }
                          }
                      }
                  }

                  $charges[$i]->netsuite_item_id = $item_id;
              }

              $invoice->customer_id = $customer_internalId;
              $invoice->charges = $charges;

              if($new_invoice_id = EmdNetsuite::updateInvoice($existing_invoice, $invoice))
              {
                  //update with new netsuite invoice id to ext_key..
                  $this->info('updated invoice ' . $new_invoice_id);

                  //process payments

                  if($apply_payments = $this->_dopayment($invoice->InvoiceNumber_EMD, $payments))
                  {
                     return DB::table($this->keytable)->where('id',$refid)->update(array( 'todo'=>'done', 'todopayment'=>'', 'ext_key'=>$new_invoice_id));
                  }
              }
                else
              {
                  $msg = "Failed to update invoice ";
                  $this->error($msg);
                  Log::error($msg);
              }

      }catch (Exception $e)
      {
          throw new Exception($e->getMessage());
      }
  }
}
