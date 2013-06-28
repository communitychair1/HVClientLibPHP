<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

require_once("HVClientBaseTest.php");

class HVHealthJournalParserTest extends HVClientBaseTest
{

    /**
     * Sets everything neccessary for health vault testing
     */
    protected function setUp()
    {
        parent::setUp();
        $this->hv->connect($this->thumbPrint, $this->privateKey);
    }

    /**
     * Tests the set up configuration
     */
    public function testSetUp()
    {
        $this->assertNotNull($this->hv);
    }


    /**
     * Test Tracker Request Max Min Date
     *
     *  Tests retrieving tracker data within a range of dates
     */
    public function testTrackerRequestMaxMinDate(){

        //Create a timestamp 14 days in the past
        $dateFilterStrMax = '-2 days';
        $dateFilterStrMin = '-5 days';

        $timeMax = date(DATE_ATOM, mktime(0,0,0,
            date('m', strtotime($dateFilterStrMax)),
            date('d', strtotime($dateFilterStrMax)),
            date('Y', strtotime($dateFilterStrMax))));

        $timeMin = date(DATE_ATOM, mktime(0,0,0,
            date('m', strtotime($dateFilterStrMin)),
            date('d', strtotime($dateFilterStrMin)),
            date('Y', strtotime($dateFilterStrMin))));

        //Create an XML filter using timestamp
        $timeFilterMax = '<eff-date-max>'.$timeMax.'</eff-date-max>';
        $timeFilterMin = '<eff-date-min>'.$timeMin.'</eff-date-min>';

        //Init array's for request
        $option = array();
        $requestGroup = array();

        //Populate the Request group
        // Key = TypeName of Thing to request
        // Value = filter on that thing request
        $requestGroup["Health Journal Entry"] = "";
        $requestGroup["Emotional State"] = "";
        //$requestGroup["Emotional State"] = $timeFilterMin.$timeFilterMax;

        //Make the request to health vault.
        $response = $this->hv->getThings(
            $requestGroup,
            $this->recordId,
            $option,
            false
        );

        /* @var $dataItem HealthRecordItemData */
        foreach ($response as $dataItem)
        {
            print_r($dataItem->getItemJSONArray());
            // echo json_encode($dataItem->getItemJSONArray());
        }

        //print_r($response);


    }


}