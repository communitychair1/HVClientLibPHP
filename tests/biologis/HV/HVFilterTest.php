<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
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
     * Tests the set up configuration
     */
    public function testSetUp()
    {
        $this->assertNotNull($this->hv);
        $this->assertNotNull($this->connector);
    }


    public function testFilters()
    {
        //init local variables
        $queryData = '';

        //Test filtered query one:
        //      Return all weight created after 2012
        $XpathQuery1 = "/thing/data-xml/weight/when/date[y &gt; 2012]";

        //Test filter query two:
        //          Return all weights less than 88KG
        $XpathQuery2 = "/thing/data-xml/weight/value[kg &lt; 88]";

        //Init filters for query 1
        $filters = array(
            'filters' => '<thing-state>Active</thing-state><xpath>'. $XpathQuery1 .'</xpath>'
        );

        //Make request to retrieve person info;
        $queryData = $this->hv->getThings(
            "Weight Measurement",
            $this->recordId,
            $filters,
            false
        );

        // Loop through the first query and assert conditions
        foreach($queryData as $weight)
        {
            $year = $weight->{'weight'}->{'when'}->{'date'}->{'y'};

            //Assert the year of each thing returned is greater than 2012
            $this->assertGreaterThan(2012,$year);

            //Assert the value of the weight element exists.
            $this->assertNotNull($weight->{'weight'}->{'value'});
        }


        //Init filters for query2
        $filters = array(
            'filters' => '<thing-state>Active</thing-state><xpath>'. $XpathQuery2 .'</xpath>'
        );

        //Run query 2
        $queryData = $this->hv->getThings(
            "Weight Measurement",
            $this->recordId,
            $filters,
            false
        );

        // Loop through the first query and assert conditions
        foreach($queryData as $weight)
        {
            $kg = $weight->{'weight'}->{'value'}->{'kg'};

            //Assert the year of each thing returned is greater than 2012
            $this->assertLessThan(88,$kg);

            //Assert the date of measurement exists
            $this->assertNotNull($weight->{'weight'}->{'when'}->{'date'});
        }

    }


}