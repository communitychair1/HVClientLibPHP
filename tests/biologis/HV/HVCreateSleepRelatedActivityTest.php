<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\SleepRelatedActivity\Activity;
use biologis\HV\HealthRecordItem\SleepRelatedActivity;

require_once("HVClientBaseTest.php");

class HVSleepRelatedActivityTest extends HVClientBaseTest
{

    /**
     * Sets everything necessary for health vault testing
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
     * Function will add a sample emotional state to the user record
     */
    public function testCreateSleepRelatedActivity1()
    {
        /**
         * @var $sleepRelatedActivity SleepRelatedActivity
         */
        $sleepRelatedActivity = SleepRelatedActivity::createFromData( time(), 1);

        $this->assertNotEmpty($sleepRelatedActivity, "Sleep Related State object empty.");
        // Grab the XML
        $xml = $sleepRelatedActivity->getItemXml();
        // echo "XML: $xml \n";
        $this->assertNotEmpty($xml, "itemXml empty");

        $this->hv->putThings($xml, $this->recordId );
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(),"No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");

    }

    /**
     * Add a sample record along with some activity data
     */
    public function testCreateSleepRelatedActivity2()
    {
        $activity = new Activity(time(), 1);

        /**
         * @var $sleepRelatedActivity SleepRelatedActivity
         */
        // Create an emotional state
        $sleepRelatedActivity = SleepRelatedActivity::createFromData( time(), 1,
                                    array(time()), array(time()), array($activity), array($activity) );

        $this->assertNotEmpty($sleepRelatedActivity, "Sleep Related State object empty.");
        // Grab the XML
        $xml = $sleepRelatedActivity->getItemXml();
        // echo "XML: $xml \n";
        $this->assertNotEmpty($xml, "itemXml empty");

        $this->hv->putThings($xml, $this->recordId );
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(),"No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");

    }


}