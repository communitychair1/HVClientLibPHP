<?php

namespace biologis\HV;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

class HVClientTest extends \PHPUnit_Framework_TestCase
{
    private $appId;
    private $session;
    private $personId;
    private $thumbPrint;
    private $privateKey;
    private $logger = null;

    protected function setUp()
    {
        $baseConfigPath = realpath("../app/Resources/HealthVault/dev");
        $this->appId = file_get_contents($baseConfigPath . '/app.id');
        $this->thumbPrint = file_get_contents($baseConfigPath . '/app.fp');
        $this->privateKey = file_get_contents($baseConfigPath . '/app.pem');
        $this->session = & $_SESSION;
        $this->personId = '3933614a-92bc-4da5-95c0-6085f7aef4aa';
        $this->recordId = '97cb6d50-8c8e-4aff-8818-483efdfed7d5';
        $this->hv = new HVClient($this->appId, $this->session, $this->personId, false);
        //print_r($this->thumbPrint);
    }

    public function testConnect()
    {
        //Offline Only
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $this->assertNotEmpty($this->session['healthVault']['authToken']);
    }

    public function testDisconnect()
    {
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $this->hv->disconnect();
        $this->assertNull($this->hv->getConnector());
        $this->assertNull($this->session['healthVault']);
    }

    public function testGetAuthenticationURL()
    {
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $url = $this->hv->getAuthenticationURL("hvauthenticate");
        $this->assertNotEmpty($url);
    }

    public function getPersonInfo()
    {
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $personInfo = $this->hv->getPersonInfo();
        $this->assertEquals($this->personId, $personInfo->person_id);
        $this->assertEquals($this->recordId, $personInfo->selected_record_id);
    }

    public function testGetThings()
    {
        $this->hv->connect($this->thumbPrint, $this->privateKey);

        $userData = $this->hv->getThings(
            "Personal Demographic Information",
            $this->recordId,
            array()
        );

        $imgData = $this->hv->getThings(
            "Personal Image",
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

    public function testPutThings()
    {

    }

    public function testGetOnlineMode()
    {
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $this->assertFalse($this->hv->getOnlineMode());
    }

    public function testOfflineMode()
    {
        $this->hv->offlineMode();
        $this->assertFalse($this->hv->getOnlineMode());
    }

    public function testOnlineMode()
    {
        //$this->hv->onlineMode();
        //$this->assertTrue($this->hv->getOnlineMode());
    }

    public function testGetTypeId()
    {
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $thingId = $this->hv->getThingId($this->recordId, "92ba621e-66b3-4a01-bd73-74844aed4f5b");
        $this->assertEquals('6de9dbe7-17f2-4016-b372-b6e9bd610554', $thingId[0]);
        $this->assertEquals('3f11ce6c-1f5b-47ab-8550-3ecc55393b46', $thingId[1]);
    }
}