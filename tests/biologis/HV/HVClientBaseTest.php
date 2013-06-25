<?php

namespace biologis\HV;

use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;
use Symfony\Component\DependencyInjection\SimpleXMLElement;
use DateTime;

class HVClientBaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HVRawConnector
     */
    protected $connector;
    /**
     * @var HVClient
     */
    protected $hv;
    protected $appId;
    protected $session;
    protected $personId;
    protected $recordId;
    protected $thumbPrint;
    protected $privateKey;
    protected $elementPath;
    protected $updateValue;
    protected $sxml;

    protected function setUp()
    {
        $baseConfigPath = realpath("/Applications/MAMP/htdocs/mentis/portal-web/trunk/app/Resources/HealthVault/dev");
        $this->appId = file_get_contents($baseConfigPath . '/app.id');
        $this->thumbPrint = file_get_contents($baseConfigPath . '/app.fp');
        $this->privateKey = file_get_contents($baseConfigPath . '/app.pem');
        $this->session = array();
        $this->personId = 'fe7e6e83-e8a1-433c-b72d-c52837eb1abd';
        $this->recordId = '3630afcd-169b-4e79-8b85-c4d0cfee9cd9';
        $this->hv = new HVClient($this->thumbPrint, $this->privateKey, $this->appId, $this->personId, false);
        // echo("AppID: $this->appId Thumb: $this->thumbPrint Private Key: $this->privateKey.");
    }

    /**
     * Ensure the ApplicationID, Thumbprint and Private key successfully loaded.
     */
    public function testSetUp()
    {
        $this->assertNotEmpty($this->appId, "Application ID is empty.");
        $this->assertNotEmpty($this->thumbPrint, "Thumbprint is empty.");
        $this->assertNotEmpty($this->privateKey, "Private key is empty");
    }

}