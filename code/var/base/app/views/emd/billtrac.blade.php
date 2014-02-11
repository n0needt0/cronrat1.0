@extends('layouts.blank')
@section('content')

<style type="text/css">
.wrap {
   width:700px;
   margin:0 auto;
}

.invoicedetails{ background-color:#eaeaea;
                    }

.invoiceheader{ background-color:#b0c4de;
                    }

</style>

<div id='invoicecontainer'>
<div id='invoiceheader' class='wrap invoiceheader'>
<table class="properties">
    <tbody>
        <tr>
            <th><label>Invoice#:</label></th><td> {{ $header['InvoiceNumber_EMD']}}</td>
            <th><label>Carrier:</label></th><td>{{ $header['InsuranceCompName']}}</td>
        </tr>
        <tr>
            <th><label>InvoiceTotal$:</label></th><td>{{ number_format($header['InvoiceTotal'])}}</td>
            <th><label>Case:</label></th><td>{{ $header['Case_Description']}}</td>
        </tr>
        <tr>
            <th><label>Service:</label></th><td>{{ $header['Invoice_Comment']}}</td>
            <th><label>AllowedTotal$:</label></th><td>{{ number_format($header['AllowedTotal'],2)}}</td>
        </tr>
        <tr>
            <th><label>Patient File:</label></th><td>{{ $header['Patient_FileId']}}</td>
            <th><label>PaymentTota$:</label></th><td>{{ number_format($header['PaymentTotal'],2)}}</td>
        </tr>
        <tr>
            <th><label>PatientDOB:</label></th><td>{{ $header['PatientDob']}}</td>
            <th><label>InsurancePaid$:</label></th><td>{{ number_format($header['InsurancePaid'],2)}}</td>
        </tr>
        <tr>
            <th><label>Injury Date:</label></th><td>{{ $header['SymptomDate']}}</td>
            <th><label>***</label></th><td>***</td>
        </tr>
        <tr>
            <th><label>Provider:</label></th><td>{{ $header['ProviderName']}}</td>
            <th><label>InsuranceAdjust$:</label></th><td>{{ number_format($header['InsuranceAdjustment'],2)}}</td>
        </tr>
        <tr>
            <th><label>CasePolicy#:</label></th><td>{{ $header['Case_PrimaryPolicyNum']}}</td>
            <th><label>CaseFile#:</label></th><td>{{ $header['Case_FileNumber']}}</td>
        </tr>
        <tr>
            <th><label>Status_Code:</label></th><td>{{ $header['InvoiceStatus_Code']}}</td>
            <th><label>Status_Description#:</label></th><td>{{ $header['InvoiceStatus_Description']}}</td>
        </tr>
    </tbody>
</table>
<hr/>
</div>

<div id='invocedetail' class='wrap invoicedetails'>
<table class="properties">
    <tbody>
    <tr>
    <th>CPT</th>
    <th>DOS</th>
    <th>eDOS</th>
    <th>Description</th>
    <th>Ea$</th>
    <th>Units</th>
    <th>Total$</th>
    </tr>

    @foreach ($charges as $charge)
      <tr>
          <td>{{$charge['InvoiceCPT_Code']}}</td>
          <td>{{$charge['sdos']}}</td>
          <td>{{$charge['edos']}}</td>
          <td>{{$charge['InvoiceCPT_CodeBillDescription']}}</td>
          <td>{{number_format($charge['InvoiceCPT_UnitFee'],2)}}</td>
          <td>{{number_format($charge['InvoiceCPT_Unit'],2)}}</td>
          <td>{{number_format($charge['InvoiceCPT_FeeAmount'],2)}}</td>
      </tr>
    @endforeach
    </tbody>
</table>
<hr/>

</div>
<div id='invocepayments' class='wrap invoicedetails'>
<table class="properties">
    <tbody>
        <tr>
        <th>Date</th>
        <th>Check</th>
        <th>Payment$</th>
        <th>Adjustment$</th>
        <th>Comments</th>
        </tr>

        @foreach ($payments as $payment)
            <tr>
                <td>{{$payment['Payment_DatePosted']}}</td>
                <td>{{$payment['Payment_CheckNo']}}</td>
                <td>{{number_format($payment['Payment_Payment'],2)}}</td>
                <td>{{number_format($payment['Payment_Adjustment'],2)}}</td>
                <td>{{$payment['Payment_PaymentComment']}}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</div>
@stop