<?php namespace Lasdorf\Netsuite;

require_once 'PHPToolkit/NetSuiteService.php';

Class FindCustomerByName extends NetsuiteBase{

    $service = new NetSuiteService();

    $service->setSearchPreferences(false, 20);

    $nameSearchField = new SearchStringField();
    $nameSearchField->operator = "startsWith";
    $nameSearchField->searchValue = "PMSI";

    $search = new CustomerSearchBasic();
    $search->companyName = $nameSearchField;

    $request = new SearchRequest();
    $request->searchRecord = $search;

    $searchResponse = $service->search($request);

    if (!$searchResponse->searchResult->status->isSuccess) {
        echo "SEARCH ERROR";
    } else {
        echo "SEARCH SUCCESS, records found: " . $searchResponse->searchResult->totalRecords;

        print_r($searchResponse->searchResult);
    }
}