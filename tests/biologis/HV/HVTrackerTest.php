<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

require_once("HVClientBaseTest.php");

class HVTrackerTest extends HVClientBaseTest
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
     * Test Tracker Request By Date 14
     *
     *  Tests the return of all data related to sleep and emotional state
     *  Filtered by a 14 day time stamp
     */
    public function testTrackerRequestByDate14(){

        //Create a timestamp 14 days in the past
        $dateFilterStr = '-14 days';
        $time14 = date(DATE_ATOM, mktime(0,0,0,
            date('m', strtotime($dateFilterStr)),
            date('d', strtotime($dateFilterStr)),
            date('Y', strtotime($dateFilterStr))));

        //Create an XML filter using timestamp
        $timeFilter = '<eff-date-min>'.$time14.'</eff-date-min>';

        //Init array's for request
        $option = array();
        $requestGroup = array();

        //Populate the Request group
        // Key = TypeName of Thing to request
        // Value = filter on that thing request
        $requestGroup["Sleep Related Activity"] = $timeFilter;
        $requestGroup["Emotional State"] = $timeFilter;

        //Make the request to health vault.
        $response = $this->hv->getThings(
            $requestGroup,
            $this->recordId,
            $option,
            false
        );


        //For all the "things" returned
        //Make sure the dates are the correct length apart.
        foreach($response as $thing)
        {
            if($thing->{'emotion'})
            {
                if($thing->{'emotion'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStr)))
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStr)),
                        $thing->{'emotion'}->{'when'}->{'date'}->{'d'});
                else
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStr)),
                        $thing->{'emotion'}->{'when'}->{'date'}->{'d'} + date('d', strtotime($dateFilterStr)));
            }
            elseif($thing->{'sleep-pm'})
            {
                if($thing->{'sleep-pm'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStr)))
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStr)),
                        $thing->{'sleep-pm'}->{'when'}->{'date'}->{'d'});
                else
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStr)),
                        $thing->{'sleep-pm'}->{'when'}->{'date'}->{'d'} + date('d', strtotime($dateFilterStr)));
            }
        }
    }
    /**
     * Test Tracker Request By Date 30
     *
     *  Tests the return of all data related to sleep and emotional state
     *  Filtered by a 30 day time stamp
     */
    public function testTrackerRequestByDate30(){

        //Create a timestamp 14 days in the past
        $dateFilterStr = '-30 days';
        $time14 = date(DATE_ATOM, mktime(0,0,0,
            date('m', strtotime($dateFilterStr)),
            date('d', strtotime($dateFilterStr)),
            date('Y', strtotime($dateFilterStr))));

        //Create an XML filter using timestamp
        $timeFilter = '<eff-date-min>'.$time14.'</eff-date-min>';

        //Init array's for request
        $option = array();
        $requestGroup = array();

        //Populate the Request group
        // Key = TypeName of Thing to request
        // Value = filter on that thing request
        $requestGroup["Sleep Related Activity"] = $timeFilter;
        $requestGroup["Emotional State"] = $timeFilter;

        //Make the request to health vault.
        $response = $this->hv->getThings(
            $requestGroup,
            $this->recordId,
            $option,
            false
        );


        //For all the "things" returned
        //Make sure the dates are the correct length apart.
        foreach($response as $thing)
        {
            if($thing->{'emotion'})
            {
                if($thing->{'emotion'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStr)))
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStr)),
                        $thing->{'emotion'}->{'when'}->{'date'}->{'d'});
                else
                    $this->assertGreaterThan(date('m', strtotime($dateFilterStr)),
                        $thing->{'emotion'}->{'when'}->{'date'}->{'m'});
            }
            elseif($thing->{'sleep-pm'})
            {
                if($thing->{'sleep-pm'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStr)))
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStr)),
                        $thing->{'sleep-pm'}->{'when'}->{'date'}->{'d'});
                else
                    $this->assertGreaterThan(date('m', strtotime($dateFilterStr)),
                        $thing->{'sleep-pm'}->{'when'}->{'date'}->{'m'});
            }
        }
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
        $requestGroup["Sleep Related Activity"] = $timeFilterMin.$timeFilterMax;
        $requestGroup["Emotional State"] = $timeFilterMin.$timeFilterMax;

        //Make the request to health vault.
        $response = $this->hv->getThings(
            $requestGroup,
            $this->recordId,
            $option,
            false
        );


        foreach($response as $thing)
        {
            if($thing->{'emotion'})
            {
                if($thing->{'emotion'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStrMax)))
                    $this->assertLessThanOrEqual(date('d', strtotime($dateFilterStrMax)),
                        $thing->{'emotion'}->{'when'}->{'date'}->{'d'});
                if($thing->{'emotion'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStrMin)))
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStrMin)),
                        $thing->{'emotion'}->{'when'}->{'date'}->{'d'});
            }
            elseif($thing->{'sleep-pm'})
            {
                if($thing->{'sleep-pm'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStrMax)))
                    $this->assertLessThanOrEqual(date('d', strtotime($dateFilterStrMax)),
                        $thing->{'sleep-pm'}->{'when'}->{'date'}->{'d'});
                if($thing->{'emotion'}->{'when'}->{'date'}->{'m'} == date('m', strtotime($dateFilterStrMin)))
                    $this->assertGreaterThanOrEqual(date('d', strtotime($dateFilterStrMin)),
                        $thing->{'sleep-pm'}->{'when'}->{'date'}->{'d'});
            }
        }




    }


}