<?php

namespace biologis\HV;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

class HVClientTest2 extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HVRawConnector
     */
    private $connector;
    /**
     * @var HVClient
     */
    private $hv = null;
    private $appId;
    private $session;
    private $personId;
    private $thumbPrint;
    private $privateKey;
    private $recordId;
    private $personInfo;

    /**
     * Sets everything neccessary for health vault testing
     */
    protected function setUp()
    {
        $baseConfigPath = realpath("/Applications/MAMP/htdocs/mentis/portal-web/trunk/app/Resources/HealthVault/dev");
        $this->appId = file_get_contents($baseConfigPath . '/app.id');
        $this->thumbPrint = file_get_contents($baseConfigPath . '/app.fp');
        $this->privateKey = file_get_contents($baseConfigPath . '/app.pem');
        $this->session = & $_SESSION;
        $this->personId = 'ff4a3ed4-eb68-439a-ba96-3a2fdae2dd1c';
        $this->recordId = '0f7430d2-0c24-4f00-a100-28dae9eb8ec8';
        $config = array();
        $this->hv = new HVClient($this->thumbPrint, $this->privateKey, $this->appId, $this->personId, $config );
        $this->hv->connect($this->thumbPrint, $this->privateKey);
        $this->connector = $this->hv->getConnector();
    }


    /**
     * Test:  Gather Authorized Records
     *      test gathering all records assocaited with an account
     */
    public function testGatherAuthorizedRecords()
    {
        echo "-----Sanity Check: Gather Authorized Records-----\n";

        //Make request to retreive person info;
        $this->personInfo = $this->hv->getPersonInfo();

        //Assert it's existance
        $this->assertNotEmpty($this->personInfo->record);

        //Create array for all records tied to account
        $allRecords = array();

        //loop through all records and add them to the array
        for($i = 0; $i < count($this->personInfo->record); $i++)
            $allRecords[$i] = $this->personInfo->record[$i];

        //loop through all records and make assertions
        foreach($allRecords as $record)
        {
            //assert that the id of each array exist
            $this->assertNotEmpty($record['id'], 'Id exists in this record');

            //assert rel-type and rel-name exist for building relationships
            // It is important we make sure all relevant relationships are correct
            switch($record['rel-type'])
            {
                case 1:
                    $this->assertEquals($record['rel-name'], 'Self');
                    $this->assertEquals($record['id'], $this->recordId);
                    break;
                case 2:
                    $this->assertEquals($record['rel-name'], 'Other');
                    break;
                case 3:
                    $this->assertEquals($record['rel-name'], 'Spouse');
                    break;
                case 5:
                    $this->assertEquals($record['rel-name'], 'Child');
                    break;
                case 6:
                    $this->assertEquals($record['rel-name'], 'Guardian');
                    break;
                case 7:
                    $this->assertEquals($record['rel-name'], 'Patient');
                    break;
                case 8:
                    $this->assertEquals($record['rel-name'], 'Parent');
                    break;
                case 10:
                    $this->assertEquals($record['rel-name'], 'Relative');
                    break;
                default:
                    $this->assertNotEmpty($record['rel-name']);
            }
        }
    }
}