<?php

namespace biologis\HV;

use biologis\HV\HealthRecordItem\WeightMeasurement;
use biologis\HV\HealthRecordItem\PersonalImage;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;
use Symfony\Component\DependencyInjection\SimpleXMLElement;
use DateTime;
use biologis\HV\HVClientBaseTest;

require_once("HVClientBaseTest.php");

class HVClientTest extends HVClientBaseTest
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
     * Tests connecting to health vault
     */
    public function testConnect()
    {
        //Offline Only
        $this->hv->connect();
        $this->assertNotEmpty($this->hv->getConnector());
    }

    /**
     * Tests disconnecting from health vault
     */
    public function testDisconnect()
    {
        $this->hv->connect();
        $this->hv->disconnect();
        $this->assertNull($this->hv->getConnector());
    }

    /**
     * Tests getting authorization URL from HV
     */
    public function testGetAuthenticationURL()
    {
        $this->hv->connect();
        $url = $this->hv->getAuthenticationURL("hvauthenticate");
        $this->assertNotEmpty($url);
    }

    /**
     * Testing getting a person basic information and record.
     */
    public function getPersonInfo()
    {
        $this->hv->connect();
        $personInfo = $this->hv->getPersonInfo();
        $this->assertEquals($this->personId, $personInfo->person_id);
        $this->assertEquals($this->recordId, $personInfo->selected_record_id);
        $arr = $personInfo->getRecordList();
        foreach ($arr as $record) {
            $this->assertArrayHasKey('id', $record);
        }
    }

    /**
     * Test getting things from HV
     *      Personal Demographic Information
     *      Personal Image
     */
    public function testGetThings()
    {
        $this->hv->connect();

        $userData = $this->hv->getThings(
            array("Personal Demographic Information" => ''),
            $this->recordId,
            array(),
            false
        );

        $imgData = $this->hv->getThings(
            array("Personal Image" => ''),
            $this->recordId,
            array(),
            True
        );

        $regex = "^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{4}|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)^";
        $base64 = preg_match($regex, $imgData);

        $this->assertObjectHasAttribute('name', $userData[0]->personal);
        $this->assertObjectHasAttribute('birthdate', $userData[0]->personal);
        $this->assertEquals(1, $base64);

    }

    /**
     * Test Inserting data into health vault
     */
    public function testPutThings()
    {
        $this->hv->connect();

        $weight = $this->hv->getThings(
            array('3d34d87e-7fc1-4153-800f-f56592cb0d17' => ''),
            $this->recordId,
            array(),
            false
        );

        $weight = intval($weight[0]->{'weight'}->{'value'}->{'kg'}->__toString());

        $date = new DateTime();
        $timeStamp = $date->getTimestamp();

        $newWeight = WeightMeasurement::createFromData($timeStamp, $weight + 5);

        $sxml = new SimpleXMLElement($newWeight->getItemXml());

        $this->hv->putThings($this->hv->stripXMLHeader($sxml), $this->recordId);

        $checkWeight = $this->hv->getThings(
            array('3d34d87e-7fc1-4153-800f-f56592cb0d17' => ''),
            $this->recordId,
            array(),
            false
        );

        $checkWeight = intval($checkWeight[0]->{'weight'}->{'value'}->{'kg'}->__toString());

        $this->assertEquals($weight + 5, $checkWeight);
    }

    /**
     * Test that Online Mode not automatically set
     */
    public function testGetOnlineMode()
    {
        $this->hv->connect();
        $this->assertFalse($this->hv->getOnlineMode());
    }

    /**
     * Test setting a health vault authoriztion instance
     */
    public function testSetHealthVaultAuthInstance()
    {
        $this->hv->setHealthVaultAuthInstance('testing');
        $this->assertEquals('testing', $this->hv->getHealthVaultAuthInstance(), 'Setting the HVAuthInstance does not work');
    }

    /**
     * Test accessing the thing-id of an object type
     */
    public function testGetThingId()
    {
        $this->hv->connect();
        $thingId = $this->hv->getThingId($this->recordId, "92ba621e-66b3-4a01-bd73-74844aed4f5b");

        $this->assertEquals('46003451-a235-4ec1-8569-03e81001b675', $thingId[0]['version-stamp']);
        $this->assertEquals('46003451-a235-4ec1-8569-03e81001b675', $thingId[1]);
    }

    /**
     * Testing getting the health vault platform.
     */
    public function testSetHealthVaultPlatform()
    {
        $this->hv->setHealthVaultPlatform('testing');
        $this->assertEquals('testing', $this->hv->getHealthVaultPlatform(), 'Setting the HVPlatform does not work');
    }


    /**
     * Test stripping of xml headers to access data.
     */
    public function testStripXMLHeader()
    {
        $xml = simplexml_load_string('<music>\n<album>Beethoven</album>\n</music>');
        $xml = simplexml_load_string($this->hv->stripXMLHeader($xml));
        $this->assertObjectNotHasAttribute('music', $xml);
    }
}