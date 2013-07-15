<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;
use biologis\HV\HealthRecordItem\SleepSession;

use QueryPath\Query;
use QueryPath;

require_once("HVClientBaseTest.php");
require_once("HVTestSleepSessionObjectCreation.php");

class HVSleepSessionParserTest extends HVClientBaseTest
{

    /**
     * Sets everything neccessary for health vault testing
     */
    protected function setUp()
    {
        parent::setUp();
        //$this->hv->connect($this->thumbPrint, $this->privateKey);
    }


    public function testCreateSleepSession2()
    {
        /**
         * @var $item SleepSession
         */

        // Create an emotional state
        $item = HVTestSleepSessionObjectCreation::createSleepSession3();

        //Check that the object has been initialized
        $this->assertNotEmpty($item);

        // Grab the XML
        $xml = $item->getItemXml();

        //assert xml object is created
        $this->assertNotEmpty($xml, "itemXml empty");

        // Now try creating from XML...
        $parsedItem = new SleepSession(QueryPath::withXML($xml));

        //get the json form of the object
        $jsonData = $parsedItem->getItemJSONArray();

        // Ensure we got the items parsed correctly.
        $this->assertArrayHasKey("when", $jsonData);
        $this->assertArrayHasKey("bedTime", $jsonData);
        $this->assertArrayHasKey("sleepMinutes", $jsonData);
        $this->assertArrayHasKey("settlingMinutes", $jsonData);
        $this->assertArrayHasKey("wakeState", $jsonData);
        $this->assertArrayHasKey("awakenings", $jsonData);
        $this->assertArrayHasKey("medications", $jsonData);


    }


}