<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

require_once("HVClientBaseTest.php");

class HVTestGetSpecificItemTest extends HVClientBaseTest
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
     * Ensure we're setup here
     */
    public function testSingleGroupNoFilters()
    {
        // Sleep Related Activity
        $options = array(
            'thing-ids' => array(
                'a62529e9-ea25-40a2-b72e-a9a11a06d506'),
        );

        $xmlResponse = $this->hv->getThings(
            array(),
            $this->recordId,
            $options,
            false
        );

        foreach ($xmlResponse as $response) {
            $this->assertNotNull($response);
        }

    }

}