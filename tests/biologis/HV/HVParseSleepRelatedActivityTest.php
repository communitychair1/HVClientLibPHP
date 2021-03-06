<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

require_once("HVClientBaseTest.php");

class HVParseSleepRelatedActivityTest extends HVClientBaseTest
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
    public function testHealthJournalParser()
    {

        //Init array's for request
        $option = array();
        $requestGroup = array();

        //Populate the Request group
        // Key = TypeName of Thing to request
        // Value = filter on that thing request
        $requestGroup["Sleep Related Activity"] = '';

        //Make the request to health vault.
        $hvThingArr = $this->hv->getThings(
            $requestGroup,
            $this->recordId,
            $option,
            false
        );

        /* @var $hvThing HealthRecordItemData */
        foreach ($hvThingArr as $hvThing) {
            $dataArr = $hvThing->getItemJSONArray();
            $this->assertArrayHasKey("when", $dataArr);
            $this->assertArrayHasKey("alcohol", $dataArr);
            $this->assertArrayHasKey("sleepiness", $dataArr);
            $this->assertArrayHasKey("nap", $dataArr);
            $this->assertArrayHasKey("exercise", $dataArr);
        }
    }


}