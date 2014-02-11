<?php namespace Lasdorf\Netsuite;

use Lasdorf\Netsuite\NetsuiteBase;

Class EmdNetsuite extends NetsuiteBase
{


    static public function mapLocation($locationEmdCode)
    {
        if($loc = \Config::get('app.emdtonetsuite.location.' . $locationEmdCode))
        {
            return $loc;
        }

        //else use catch all

        return \Config::get('app.emdtonetsuite.location.catchall');
    }

    /**
     * @param array( 'itemId' => array('operator'=>'is', 'searchValue' =>$item_code)
     * @throws Exception
     * @return boolean
     */
 static public function findItem($item)
 {
     try{

            $service = new \NetSuiteService();
            $itemSearch = new \ItemSearchBasic();
            $searchItems = $item;
            setFields($itemSearch, $searchItems);

            $request = new \SearchRequest();
            $request->searchRecord = $itemSearch;

            $searchResponse = $service->search($request);

            if ($searchResponse->searchResult->status->isSuccess)
            {
                 return $searchResponse;
            }

            return false;

     }
       catch(Exception $e)
     {
         throw new Exception($e->getMessage());
     }
  }
/**
 * https://webservices.netsuite.com/xsd/platform/v2013_2_0/coreTypes.xsd
 * @param $type
 * @param $internalId
 * @throws Exception
 * @return Ambigous <GetResponse, mixed>|boolean
 */
 static public function getIt($type, $internalId=false, $externalId = false)
 {
     try{
           $service = new \NetSuiteService();

           $request = new \GetRequest();
           $request->baseRef = new \RecordRef();

           if($externalId)
           {
               $request->baseRef->externalId = $externalId;
           }

           if($internalId)
           {
               $request->baseRef->internalId = $internalId;
           }

           $request->baseRef->type = $type;
           $getResponse = $service->get($request);

           if ($getResponse->readResponse->status->isSuccess)
           {
               return $getResponse;
           }

           return false;

     }
       catch(Exception $e)
     {
         throw new Exception($e->getMessage());
     }
 }

static public function updateRecord($new_item)
{
     try{
                $service = new \NetSuiteService();
                $request = new \UpdateRequest();
                $request->record = $new_item;
                $Response = $service->update($request);

                if ($Response->writeResponse->status->isSuccess)
                {
                    return true;
                }

                return false;
      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
  }

  /**
   *
   * @param $new_item object
   * (obj)[name,parent_id,description,emdid]
   * @throws Exception
   * @return boolean
   */

static public function addItem($new_item)
{
     try{
                $service = new \NetSuiteService();
                $item = new \NonInventorySaleItem();
                $request = new \AddRequest();

                $item->parent = new \RecordRef();
                $item->parent->internalId = $new_item->root->internalId;
                $item->itemId = $new_item->name;
                $item->displayName = $item->salesDescription = $new_item->description;
                $item->incomeAccount = new \RecordRef();
                $item->incomeAccount->internalId = $new_item->root->incomeAccount->internalId;

                $subsidiaryList = new \RecordRefList();
                $subsidiary = new \RecordRef();
                $subsidiary->internalId = \Config::get('app.emdtonetsuite.subsidiary');
                $subsidiaryList->recordRef[] = $subsidiary;
                $item->subsidiaryList = $subsidiaryList;

                $item->taxSchedule = new \RecordRef();
                $item->taxSchedule->internalId = $new_item->root->taxSchedule->internalId;

                $item->class = new \RecordRef();
                $item->class->internalId = $new_item->root->class->internalId;

                $item->department = new \RecordRef();
                $item->department->internalId = $new_item->root->department->internalId;
                $item->includeChildren=1;

                $request->record = $item;
                $addResponse = $service->add($request);

                if ($addResponse->writeResponse->status->isSuccess)
                {
                    return $addResponse->writeResponse->baseRef->internalId;
                }

                return false;
      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
  }

  /**
   *
   * @param unknown $NameStartsWith
   * @throws Exception
   * @return Ambigous <SearchResponse, mixed>|boolean
   */
static public function findCustomer($customer_name)
 {
     try{

                $service = new \NetSuiteService();
                $service->setSearchPreferences(false, 20);

                $nameSearchField = new \SearchStringField();
                $nameSearchField->operator = "is";
                $nameSearchField->searchValue = $customer_name;

                $search = new \CustomerSearchBasic();
                $search->companyName = $nameSearchField;

                $request = new \SearchRequest();
                $request->searchRecord = $search;

                $searchResponse = $service->search($request);

                if ($searchResponse->searchResult->status->isSuccess)
                {
                     return $searchResponse;
                }

                return false;

     }catch(Exception $e)
     {
         throw new Exception($e->getMessage());
     }
  }
  /**
   *
   * @param unknown $customerobj name, subsidiary id
   * @throws Exception
   * @return boolean
   */
  static public function addCustomer($new_customer_name)
 {
     try{

                $service = new \NetSuiteService();

                $customer = new \Customer();
                $customer->companyName = $new_customer_name;

                $customer->subsidiary = new \RecordRef();
                $customer->subsidiary->internalId = \Config::get('app.emdtonetsuite.subsidiary');

                $customer->entityStatus = new \RecordRef();
                $customer->entityStatus->internalId = 13;

                $customer->receivablesAccount = new \RecordRef();
                $customer->receivablesAccount->internalId =  \Config::get('app.emdtonetsuite.defaultreceivables');

                $request = new \AddRequest();
                $request->record = $customer;

                $addResponse = $service->add($request);

                if ($addResponse->writeResponse->status->isSuccess)
                {
                    return $addResponse->writeResponse->baseRef->internalId;
                }

                return false;
      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
   }

    /**
     * @param array( 'itemId' => array('operator'=>'is', 'searchValue' =>$CptCode))
     * @throws Exception
     * @return boolean
     */
 static public function findInvoice($emd_invoice_id)
 {
     try{

            //lets try another way
            $service = new \NetSuiteService();

            $service->setSearchPreferences(false, 20);

            $nameSearchField = new \SearchStringField();
            $nameSearchField->operator = "is";
            $nameSearchField->searchValue = $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix');;

            $search = new \TransactionSearchBasic();

            $search->tranId = $nameSearchField;

            $request = new \SearchRequest();
            $request->searchRecord = $search;

            $searchResponse = $service->search($request);

            if ($searchResponse->searchResult->status->isSuccess)
            {
                 return $searchResponse;
            }

            return false;

     }
       catch(Exception $e)
     {
         throw new Exception($e->getMessage());
     }
  }

 static public function findPayment($emd_invoice_id , $emd_payment_id)
 {
     /*
      *  find all payments for this customer for Emd Invoice X. If amount and date is same is
      */
     try{
            return self::getIt('customerPayment', false, $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix') . ':' . $emd_payment_id);

            //lets try another way
            $service = new \NetSuiteService();

            $service->setSearchPreferences(false, 20);

            $nameSearchField = new \SearchStringField();
            $nameSearchField->operator = "is";
            $nameSearchField->searchValue = $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix') . ':' . $emd_payment_id;

            $search = new \TransactionSearchBasic();
            $search->memo = $nameSearchField;

            $request = new \SearchRequest();
            $request->searchRecord = $search;

            $searchResponse = $service->search($request);

            print_r($searchResponse);

            if ($searchResponse->searchResult->status->isSuccess)
            {
                 return $searchResponse;
            }

            return false;

     }
       catch(Exception $e)
     {
         throw new Exception($e->getMessage());
     }
  }

  static private function checkPaymentResponse($Response, $netsuite_invoice_id, $emd_invoice_id, $payment)
  {
      echo "Updating payment...\n";

      print_r($Response);

      if(isset($Response) && isset($Response->writeResponse) && isset($Response->writeResponse->status) && isset($Response->writeResponse->status->statusDetail[0]) && isset($Response->writeResponse->status->statusDetail[0]->message) && $Response->writeResponse->status->statusDetail[0]->message == "One or more of the bills or invoices has had a payment made on it since you retrieved the form")
      {

          $errors[] = array('invoice_number'=>$emd_invoice_id, 'error'=>'Payments are greater than invoice');
          $data = array('errors'=>$errors,'valid_services'=>array() );

          if(\Config::get('app.emdtonetsuite.error_email') != true)
          {
              return false;
          }

          \Mail::send('emd.email_netsuite_errors', $data, function($message)
          {
               $message->to(\Config::get('app.emails.admin'))->subject('Emd Data Errors');
          });
          return false;
      }
      return true;
  }

 static private function checkInvoiceResponse($Response, $invoice)
  {
      echo "Updating invoice...\n" . print_r($invoice, true);
      print_r($Response);

      if(isset($Response) && isset($Response->writeResponse) && isset($Response->writeResponse->status) && isset($Response->writeResponse->status->statusDetail[0]) && isset($Response->writeResponse->status->statusDetail[0]->message) && $Response->writeResponse->status->statusDetail[0]->message == "The record has been deleted since you retrieved it.")
      {

          $errors[] = array('invoice_number'=>print_r($Response, true), 'error'=>'Payments are greater than invoice');

          $data = array('errors'=>$errors,'valid_services'=>array() );

          if(\Config::get('app.emdtonetsuite.error_email') != true)
          {
              return false;
          }

          \Mail::send('emd.email_netsuite_errors', $data, function($message)
          {
               $message->to(\Config::get('app.emails.admin'))->subject('Emd Data Errors');

          });
          return false;
      }
      return true;
  }

static public function updatePayment( $netsuite_invoice_id, $emd_invoice_id, $netsuite_customer_id, $old_payment, $new_payment)
{
                if(($new_payment->Payment_Payment + $new_payment->Payment_Adjustment) < 0.01)
                {
                    return 1; //weird emd 0 deposits
                }

     try{

                $payment = $old_payment;

                //IMPORTANT this s key
                $payment->memo = $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix') . ':' . $new_payment->Payment_ID;
                //TODO remove when decided
                //******************************
                $payment->subsidiary = new \RecordRef();
                $payment->subsidiary->internalId = \Config::get('app.emdtonetsuite.invoice.subsidiary');
                $payment->location = new \RecordRef();
                $payment->location->internalId = self::mapLocation('catchall');
                $payment->class = new \RecordRef();
                $payment->class->internalId = 9;
                $payment->department = new \RecordRef();
                $payment->department->internalId = 18;

                //***END TODO
                $payment->arAcct= new \RecordRef();
                $payment->arAcct->internalId = \Config::get('app.emdtonetsuite.payment.araccount');
                $payment->customer= new \RecordRef();
                $payment->customer->internalId = $netsuite_customer_id;
                $dt = \DateTime::createFromFormat('m-d-Y', $new_payment->Payment_DatePosted);
                $payment->tranDate = $dt->format('c');

                if(\Config::get('app.emdtonetsuite.payment.undeposit'))
                {
                    $payment->undepFunds = true;
                    unset($payment->account);
                }
                  else
                {
                    $payment->undepFunds = false;
                    $payment->account= new \RecordRef();
                    $payment->account->internalId = \Config::get('app.emdtonetsuite.payment.depositaccount');
                    $payment->status = 'deposited';
                }

                $payment->externalId = $payment->memo;

                $payment->checkNum = $new_payment->Payment_CheckNo;

                $payment->applyList = new \CustomerPaymentApplyList();

                $customerPaymentApply = new \CustomerPaymentApply();

                $customerPaymentApply->apply = 1;
                $customerPaymentApply->doc = $netsuite_invoice_id;
                $customerPaymentApply->line = 0;
                $customerPaymentApply->applyDate = $payment->tranDate;
                $customerPaymentApply->type = 'invoice';
                $customerPaymentApply->refNum = $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix');

                $customerPaymentApply->discAmt = $new_payment->Payment_Adjustment;
                $customerPaymentApply->disc = $new_payment->Payment_Adjustment;
                $customerPaymentApply->discDate = $payment->tranDate;

                //TODO netsuite does not like discount only payments
                if($new_payment->Payment_Payment == 0)
                {
                    $new_payment->Payment_Payment = 0.01;
                    $new_payment->Payment_Adjustment = $new_payment->Payment_Adjustment - $new_payment->Payment_Payment;
                }

                $payment->applyList->apply[] = $customerPaymentApply;
                $payment->applyList->replaceAll = true;

     //SOME are READ ONLY
                $unset = array('unapplied', 'applied','balance','pending');

                foreach($unset as $i=>$k)
                {
                    unset($payment->$k);
                }

                print_r($payment);

                $service = new \NetSuiteService();
                $request = new \UpdateRequest();
                $request->record = $payment;
                $Response = $service->update($request);

                if ($Response->writeResponse->status->isSuccess)
                {
                    return true;
                }
                if(!self::checkPaymentResponse($Response, $netsuite_invoice_id, $emd_invoice_id, $payment))
                {
                    self::hardStop();
                }
                return false;
      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
  }

static public function addPayment( $netsuite_invoice_id, $emd_invoice_id, $netsuite_customer_id, $new_payment)
{
                if(($new_payment->Payment_Payment + $new_payment->Payment_Adjustment) < 0.01)
                {
                    return 1; //weird emd 0 deposits
                }

     try{
                $service = new \NetSuiteService();
                $payment = new \CustomerPayment();

                //IMPORTANT this s key
                $payment->memo = $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix') . ':' . $new_payment->Payment_ID;
                //TODO remove when decided
                //******************************
                $payment->subsidiary = new \RecordRef();
                $payment->subsidiary->internalId = \Config::get('app.emdtonetsuite.invoice.subsidiary');
                $payment->location = new \RecordRef();
                $payment->location->internalId = self::mapLocation('catchall');
                $payment->class = new \RecordRef();
                $payment->class->internalId = 9;
                $payment->department = new \RecordRef();
                $payment->department->internalId = 18;

                //***END TODO
                $payment->arAcct= new \RecordRef();
                $payment->arAcct->internalId = \Config::get('app.emdtonetsuite.payment.araccount');
                $payment->customer= new \RecordRef();
                $payment->customer->internalId = $netsuite_customer_id;
                $dt = \DateTime::createFromFormat('m-d-Y', $new_payment->Payment_DatePosted);
                $payment->tranDate = $dt->format('c');

                if(\Config::get('app.emdtonetsuite.payment.undeposit'))
                {
                    $payment->undepFunds = true;  //keep amount in Undeposited funds
                }
                  else
                {
                    $payment->undepFunds = false;
                    $payment->account= new \RecordRef();
                    $payment->account->internalId = \Config::get('app.emdtonetsuite.payment.depositaccount');
                    $payment->status = 'deposited';
                }

                $payment->externalId = $payment->memo;

                $payment->checkNum = $new_payment->Payment_CheckNo;

                $payment->applyList = new \CustomerPaymentApplyList();

                $customerPaymentApply = new \CustomerPaymentApply();

                $customerPaymentApply->apply = 1;
                $customerPaymentApply->doc = $netsuite_invoice_id;
                $customerPaymentApply->line = 0;
                $customerPaymentApply->applyDate = $payment->tranDate;
                $customerPaymentApply->type = 'invoice';
                $customerPaymentApply->refNum = $emd_invoice_id . \Config::get('app.emdtonetsuite.invoice.prefix');

                //TODO netsuite does not like discount only payments
                if($new_payment->Payment_Payment == 0)
                {
                    $new_payment->Payment_Payment = 0.01;
                    $new_payment->Payment_Adjustment = $new_payment->Payment_Adjustment - $new_payment->Payment_Payment;
                }


                $customerPaymentApply->discAmt = $new_payment->Payment_Adjustment;
                $customerPaymentApply->disc = $new_payment->Payment_Adjustment;
                $customerPaymentApply->discDate = $payment->tranDate;

                $customerPaymentApply->amount = $new_payment->Payment_Payment;


                $payment->applyList->apply[] = $customerPaymentApply;

                print_r($payment);

                $request = new \AddRequest();
                $request->record = $payment;

                $addResponse = $service->add($request);

                print_r($addResponse);

                if ($addResponse->writeResponse->status->isSuccess)
                {
                    //deposit it here
                    return $addResponse->writeResponse->baseRef->internalId;
                }
                if(!self::checkPaymentResponse($addResponse, $netsuite_invoice_id, $emd_invoice_id, $payment))
                {
                    self::hardStop();
                }
                return false;
      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
  }

  static public function hardStop()
  {
      if(\Config::get('app.emdtonetsuite.hardstoponerrors'))
      {
          die;
      }
  }

  /**
   *
   * @param $new_invoice object
   *
   * @throws Exception
   * @return boolean
   */
static public function addInvoice($new_invoice)
{

     try{
                $service = new \NetSuiteService();
                $invoice = new \Invoice();

                $invoice->entity = new \RecordRef();
                $invoice->entity->internalId = $new_invoice->customer_id;

                $invoice->subsidiary = new \RecordRef();
                $invoice->subsidiary->internalId = \Config::get('app.emdtonetsuite.invoice.subsidiary');

                $invoice->location = new \RecordRef();
                $invoice->location->internalId = self::mapLocation($new_invoice->MedicalFacility_Code);

                $invoice->account = new \RecordRef();
                $invoice->account->internalId = \Config::get('app.emdtonetsuite.invoice.account');

                $invoice->taxItem = new \RecordRef();
                $invoice->taxItem->internalId = \Config::get('app.emdtonetsuite.invoice.taxItem');

                $dt = \DateTime::createFromFormat('m-d-Y', $new_invoice->InvoiceCreatedAt);
                $invoice->tranDate = $dt->format('c');
                $invoice->dueDate =$dt->add(new \DateInterval('P1M'))->format('c');

                $invoice->externalId = $new_invoice->InvoiceNumber_EMD . \Config::get('app.emdtonetsuite.invoice.prefix');
                $invoice->tranId =$new_invoice->InvoiceNumber_EMD . \Config::get('app.emdtonetsuite.invoice.prefix');

                //Add cutom fields here
                $customFieldList = new \CustomFieldList();
                //add provider
                $provider = new \StringCustomFieldRef();
                $provider->internalId = \Config::get('app.emdtonetsuite.customfields.provider');
                $provider->value = $new_invoice->ProviderName;
                $customFieldList->customField[] = $provider;

                //TODO Add affiliate from somewhere some how...
                $affiliate = new \StringCustomFieldRef();
                $affiliate->internalId = \Config::get('app.emdtonetsuite.customfields.affiliate');
                $affiliate->value = $new_invoice->ProviderName;
                $customFieldList->customField[] = $affiliate;

                $invoice->customFieldList = $customFieldList;

                //create item list in netsuit object form
                $invoice->itemList = new \InvoiceItemList();

                $items = array();

                foreach( $new_invoice->charges as $i =>$line)
                {
                    $invoice_item = new \InvoiceItem();
                    $invoice_item->item = new \RecordRef();
                    $invoice_item->item->internalId = $line->netsuite_item_id;

                    $invoice_item->line = $i+1;
                    $invoice_item->amount = number_format($line->InvoiceCPT_UnitFee*$line->InvoiceCPT_Unit,2, '.', '');
                    $invoice_item->quantity = $line->InvoiceCPT_Unit;
                    $invoice_item->rate = $line->InvoiceCPT_UnitFee;

                    $invoice_item->location = new \RecordRef();
                    $invoice_item->location->internalId = self::mapLocation($new_invoice->MedicalFacility_Code);

                    $invoice_item->price = new \RecordRef();
                    $invoice_item->price->internalId = \Config::get('app.emdtonetsuite.invoice.price');
                    $items[] = $invoice_item;
                }

                $invoice->itemList->item = $items;

                $request = new \AddRequest();
                $request->record = $invoice;

                $addResponse = $service->add($request);

                if ($addResponse->writeResponse->status->isSuccess)
                {
                    return $addResponse->writeResponse->baseRef->internalId;
                }

                if(!self::checkInvoiceResponse($addResponse, $invoice))
                {
                    self::hardStop();
                }

                return false;
      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
  }

static public function updateInvoice($old_invoice, $new_invoice)
{

    /*
     * Old invoice updates values on new invoice. the new values should be NON EMPTY
     */


     try{

                $dt = \DateTime::createFromFormat('m-d-Y', $new_invoice->InvoiceCreatedAt);
                $old_invoice->tranDate = $dt->format('c');
                $old_invoice->dueDate =$dt->add(new \DateInterval('P1M'))->format('c');

                $old_invoice->externalId = $new_invoice->InvoiceNumber_EMD . \Config::get('app.emdtonetsuite.invoice.prefix');
                $old_invoice->tranId = $new_invoice->InvoiceNumber_EMD . \Config::get('app.emdtonetsuite.invoice.prefix');

                //Add cutom fields here
                $customFieldList = new \CustomFieldList();
                //add provider
                $provider = new \StringCustomFieldRef();
                $provider->internalId = \Config::get('app.emdtonetsuite.customfields.provider');
                $provider->value = $new_invoice->ProviderName;
                $customFieldList->customField[] = $provider;

                //TODO Add affiliate from somewhere some how...
                $affiliate = new \StringCustomFieldRef();
                $affiliate->internalId = \Config::get('app.emdtonetsuite.customfields.affiliate');
                $affiliate->value = 'TBD';
                $customFieldList->customField[] = $affiliate;
                $old_invoice->customFieldList = $customFieldList;
                //create item list in netsuit object form
                $old_invoice->itemList = new \InvoiceItemList();

                $items = array();

                foreach( $new_invoice->charges as $i =>$line)
                {
                    $invoice_item = new \InvoiceItem();
                    $invoice_item->item = new \RecordRef();
                    $invoice_item->item->internalId = $line->netsuite_item_id;

                    $invoice_item->line = $i+1;
                    $invoice_item->amount = number_format($line->InvoiceCPT_UnitFee*$line->InvoiceCPT_Unit,2, '.', '');
                    $invoice_item->quantity = $line->InvoiceCPT_Unit;
                    $invoice_item->rate = $line->InvoiceCPT_UnitFee;

                    $invoice_item->location = new \RecordRef();
                    $invoice_item->location->internalId = self::mapLocation($new_invoice->MedicalFacility_Code);

                    $invoice_item->price = new \RecordRef();
                    $invoice_item->price->internalId = \Config::get('app.emdtonetsuite.invoice.price');
                    $items[] = $invoice_item;
                }


                $old_invoice->itemList->item = $items;
                $old_invoice->itemList->replaceAll =1;

                //SOME are READ ONLY
                $unset = array('totalCostEstimate', 'estGrossProfit', 'estGrossProfitPercent','subTotal','total', 'taxRate','taxTotal','status','tranId');

                foreach($unset as $i=>$k)
                {
                    unset($old_invoice->$k);
                }

                $service = new \NetSuiteService();
                $request = new \UpdateRequest();
                $request->record = $old_invoice;
                $Response = $service->update($request);

                if ($Response->writeResponse->status->isSuccess)
                {
                    return $old_invoice->internalId;
                }

                if(!self::checkInvoiceResponse($Response, $old_invoice))
                {
                    self::hardStop();
                }
                return false;

      }
        catch(Exception $e)
      {
         throw new Exception($e->getMessage());
      }
  }

}