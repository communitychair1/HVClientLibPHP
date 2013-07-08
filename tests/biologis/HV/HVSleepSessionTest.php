<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\HealthJournalEntry;
use biologis\HV\HVTestSleepSessionObjectCreation;

require_once("HVClientBaseTest.php");
require_once("HVTestSleepSessionObjectCreation.php");

class HVSleepSessionTest extends HVClientBaseTest
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
     * Try setting the descriptive date instead of an actual date.
     */
    public function testCreateSleepSession1()
    {
        /**
         * @var $item SleepSession
         */
        // Create an emotional state
        $item = HVTestSleepSessionObjectCreation::createSleepSession1();

        $this->assertNotEmpty($item);
        // Grab the XML
        $xml = $item->getItemXml();
        $this->assertNotEmpty($xml, "itemXml empty");

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
        // echo $this->hv->getConnector()->getRawResponse();
    }

    /**
     * Try setting the descriptive date instead of an actual date.
     */
    public function testCreateSleepSession2()
    {
        /**
         * @var $item SleepSession
         */

        // Create an emotional state
        $item = HVTestSleepSessionObjectCreation::createSleepSession2();

        $this->assertNotEmpty($item);
        // Grab the XML
        $xml = $item->getItemXml();
        $this->assertNotEmpty($xml, "itemXml empty");
        // print_r($xml);
        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
    }


}