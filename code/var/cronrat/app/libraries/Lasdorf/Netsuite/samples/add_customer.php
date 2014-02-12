<?php namespace Lasdorf\Netsuite;

require_once __DIR__ . '/API/NetSuiteService.php';

Class AddCustomer
{

 static public function add($customer)
 {
     try{

                $service = new NetSuiteService();

                $customer = new Customer();
                $customer->companyName = $customer->name;
                $customer->subsidiary = new RecordRef();
                $customer->subsidiary->internalId = $customer->subsidiary;

                $request = new AddRequest();
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