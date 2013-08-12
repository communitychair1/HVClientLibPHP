<?php

namespace biologis\HV;

use biologis\HV\HealthRecordItem\HealthJournalEntry;
use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\SleepSession;
use biologis\HV\HealthRecordItem\GenericTypes\Common;
use biologis\HV\HealthRecordItem\GenericTypes\RelatedThing;

require_once("HVClientBaseTest.php");

class HVCommonElementTest extends HVClientBaseTest
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
    public function testCreateSleepSession()
    {
        /**
         * @var $item SleepSession
         */
        // Create an emotional state

        /**
         * SleepSession
         */
        $item = HealthJournalEntry::createFromData(time(),"today","My random journal entry","mentis journal entry category");

        //$item = SleepSession::createFromData(time(), time(), time(), 300, 25, 1);


        $common = new Common();
        $common->setClientThingId("my client id!!!");
        $common->setExtensionXML("<extension source='unit test extension entry'><tyler>here we go</tyler></extension>");
        $common->setNote("rtm");
        $common->setSource("PHP Unit Test");
        $common->setTags("sleep, journal entry, etc");
        $relatedThing = new RelatedThing();
        //$relatedThing->setClientThingId("rel client id");
        $relatedThing->setThingId("6553f5c8-c1da-4078-ab7f-01a978eaf11d");
        $relatedThing->setVersionStamp("162dd12d-9859-4a66-b75f-96760d612345");
        $relatedThing->setRelationshipType("journal entry");
        $relatedThing2 = new RelatedThing();
        //$relatedThing->setClientThingId("rel client id");
        $relatedThing2->setThingId("7c732edf-2ad7-471b-bf58-40f2af7a3f10");
        $relatedThing2->setVersionStamp("162dd12d-9859-4a66-b75f-96760d612345");
        $relatedThing2->setRelationshipType("journal entry");
        $common->setRelatedThings(array($relatedThing, $relatedThing2));
        $item->setCommon($common);


        //

        $this->assertNotEmpty($item);
        // Grab the XML
        $xml = $item->getItemXml();
        $this->assertNotEmpty($xml, "itemXml empty");

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
        // echo $this->hv->getConnector()->getRawResponse();
    }




}