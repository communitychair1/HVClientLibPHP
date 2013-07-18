<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\HealthJournalEntry;

require_once("HVClientBaseTest.php");

class HVHealthJournalEntryTest extends HVClientBaseTest
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
     * Function will add a sample Health Journal Entry to the user record
     */
    public function testCreateEmotionalState()
    {
        /**
         * @var $journalEntry HealthJournalEntry
         */

        // Create an emotional state
        $journalEntry = HealthJournalEntry::createFromData(time(),
            null, "Lorem ipsum journal entry text...", "madeupcategory");

        $this->assertNotEmpty($journalEntry, "Journal entry object empty.");
        // Grab the XML
        $xml = $journalEntry->getItemXml();
        $this->assertNotEmpty($xml, "itemXml empty");

        // echo "XML:: \n $xml \n";

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(),
            "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(),
            "Missing version identifier from response");
    }


    /**
     * Function will add a sample Health Journal Entry to the user record
     */
    public function testCreateEmotionalState2()
    {
        /**
         * @var $journalEntry HealthJournalEntry
         */

        // Create an emotional state
        $journalEntry = HealthJournalEntry::createFromData(time(), null, "Random chars < > ! &gte; break", "category");

        $this->assertNotEmpty($journalEntry, "Journal entry object empty.");
        // Grab the XML
        $xml = $journalEntry->getItemXml();
        $this->assertNotEmpty($xml, "itemXml empty");

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
    }

    /**
     * Try setting the descriptive date instead of an actual date.
     */
    public function testCreateEmotionalState3()
    {
        /**
         * @var $journalEntry HealthJournalEntry
         */

        // Create an emotional state
        $journalEntry = HealthJournalEntry::createFromData(null, "yesterday", "Journal entry from 'yesterday'. ", "category");

        $this->assertNotEmpty($journalEntry, "Journal entry object empty.");
        // Grab the XML
        $xml = $journalEntry->getItemXml();
        $this->assertNotEmpty($xml, "itemXml empty");

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
        // echo $this->hv->getConnector()->getRawResponse();

    }

}