<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\EmotionalState;

require_once("HVClientBaseTest.php");

class HVEmotionalStateTest extends HVClientBaseTest
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
    public function testCreateEmotionalState()
    {
        /**
         * @var $emotState EmotionalState
         */


        // Create an emotional state
        $emotState = EmotionalState::createFromData( time(), 1, 2, 3);

        $this->assertNotEmpty($emotState, "Emotional State object empty.");
        // Grab the XML
        $xml = $emotState->getItemXml();
        $this->assertNotEmpty($xml, "Emotional State itemXml empty");

        $this->hv->putThings($xml, $this->recordId );
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(),"No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");

    }

    public function testCreateEmotionalWithJustMood()
    {
        /**
         * @var $emotState EmotionalState
         */
        // Create an emotional state
        $emotState = EmotionalState::createFromData( time(), 1, null, null);

        $this->assertNotEmpty($emotState, "Emotional State object empty.");
        // Grab the XML
        $xml = $emotState->getItemXml();
        $this->assertNotEmpty($xml, "Emotional State itemXml empty");

        $this->hv->putThings($xml, $this->recordId );
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(),"No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
    }

    public function testCreateEmotionalWithNoStateValues()
    {
        /**
         * @var $emotState EmotionalState
         */
        // Create an emotional state
        $emotState = EmotionalState::createFromData( time(), null, null, null);

        $this->assertNotEmpty($emotState, "Emotional State object empty.");
        // Grab the XML
        $xml = $emotState->getItemXml();
        $this->assertNotEmpty($xml, "Emotional State itemXml empty");

        try {
            $this->hv->putThings($xml, $this->recordId );
        } catch (HVRawConnectorWcRequestException $ex) {
            // Ignore
            echo "Got the exception";
        }

        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(),"No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
    }

}