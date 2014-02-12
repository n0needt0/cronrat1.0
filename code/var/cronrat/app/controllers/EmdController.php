<?php


class EmdController extends BaseController {

    public function __construct()
    {
//        $this->beforeFilter('auth');
    }

	public function getIndex()
	{
		return 'emds';
	}

	/**
	*
	*/

	public function getInvoice($invoice_id,$format=false)
	{
        $invoice = array('header'=>array(), 'charges'=>array(), 'payments'=>array());
        $header = $charges = $payments = array();

	    $res = DB::connection('emds')->table("VIEW_API_InvoiceIndex")->where('InvoiceNumber_EMD', $invoice_id)->get();
        foreach($res as $r)
        {
            $header = (array)$r;
            //unset secure data here
        }

        if(empty($header))
        {
            return 'invalid emd id';
        }

        $emd_invoice_id = $header['Invoice_ID'];

        $invoice['header'] = $header;

	    $res = DB::connection('emds')->table("VIEW_API_InvoiceCPT")->where('Invoice_ID', $emd_invoice_id)->get();
        foreach($res as $r)
        {
            $charges[] = (array)$r;
            //unset secure data here
        }

        $invoice['charges'] = $charges;

         $res = DB::connection('emds')->table("VIEW_API_PaymentIndex")->where('Invoice_ID', $emd_invoice_id)->get();
        foreach($res as $r)
        {
            $payments[] = (array)$r;
            //unset secure data here
        }

        $invoice['payments'] = $payments;

        if('json' == $format)
        {
            return $this->json_out($invoice);
        }
        elseif('htmljson' == $format)
        {
            $content = View::make('emd.billtrac')->with($invoice)->render();
            return $this->json_out(array('contents'=>$content));
        }
          else
        {
            //just output regular div
            return View::make('emd.billtrac')->with($invoice);
        }

	}
}