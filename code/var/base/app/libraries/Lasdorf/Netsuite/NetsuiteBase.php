<?php namespace Lasdorf\Netsuite;

require_once __DIR__ . '/API/NetSuiteService.php';

Class NetsuiteBase
{
    /**
     * [searchResult] => SearchResult Object
        (
            [status] => Status Object
                (
                    [statusDetail] =>
                    [isSuccess] => 1
                )

            [totalRecords] => 1
            [pageSize] => 1000
            [totalPages] => 1
            [pageIndex] => 1
            [searchId] => WEBSERVICES_3627273_10152013110551209374931256_754a49c67d3c7
            [recordList] => RecordList Object
                (
                    [record] => Array
                        (
     * @param unknown $response
     */
    static function returnSearchResponse($searchResponse)
    {
        if(!$searchResponse)
        {
            return false;
        }

        if((int)$searchResponse->searchResult->totalRecords==0)
        {
            return false;
        }
        return $searchResponse->searchResult->recordList->record;
    }

    static function returnSearchResponseInternalId($searchResponse)
    {
        if(!$searchResponse)
        {
            return false;
        }


        if((int)$searchResponse->searchResult->totalRecords==0)
        {
            return false;
        }
        foreach($searchResponse->searchResult->recordList->record as $line)
        {
            return $line->internalId ;
        }
    }

    static function returnReadResponseInternalId($readResponse)
    {
        if(!$readResponse)
        {
            return false;
        }


        if((int)$readResponse->readResponse->status->isSuccess==1 && count($readResponse->readResponse->record))
        {
            return $readResponse->readResponse->record->internalId;
        }

        return false;
    }


    static function returnReadResponse($readResponse)
    {
        if(!$readResponse)
        {
            return false;
        }

        return $readResponse->readResponse->record;
    }
}