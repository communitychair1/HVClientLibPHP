<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

require_once("HVClientBaseTest.php");

class HVGroupTest extends HVClientBaseTest
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
     * ---------TEST ONE: SINGLE GROUP NO FILTERS---------
     *  This testes the case in which only a single type Id
     *  is passed in.
     */
    public function testSingleGroupNoFilters(){


        //---------TEST ONE: SINGLE GROUP NO FILTERS---------
        // This testes the case in which only a single type Id
        // is passed in.
        $groupAndFilter = array(
            'Weight Measurement' => '',
        );

        $options = array();
        $xmlResponse = $this->hv->getThings(
            $groupAndFilter,
            $this->recordId,
            $options,
            false
        );

        foreach($xmlResponse as $weights)
        {
            $this->assertNotNull($weights->{'weight'}, "Weights Have Been Returned");
        }

    }

    /**
     * ---------TEST Two: SINGLE GROUP SINGLE FILTERS---------
     *  This testes the case in which only a single type Id
     *  is passed in along with a filter.
     */

    public function testSingleGroupSingleFilter(){
        $groupAndFilter = array(
            'Weight Measurement' => '<xpath>/thing/data-xml/height/when/date[y &gt; 2012]</xpath>',
        );

        $options = array();
        $xmlResponse = $this->hv->getThings(
            $groupAndFilter,
            $this->recordId,
            $options,
            false
        );

        foreach($xmlResponse as $weights)
        {
            $this->assertNotNull($weights->{'weight'}, "Weights Have Been Returned");
            $this->assertGreaterThan(2012, $weights->{'weight'}->{'when'}->{'date'}->{'y'});
        }
    }

    /**
     * ---------TEST THREE: MULTIPLE GROUP NO FILTERS---------
     *  This testes the case in which multiple type Id's
     *  // are passed in along with no filter.
     */
    public function testMultipleGroupsNoFilters(){
        $groupAndFilter["Height Measurement"] = '';
        $groupAndFilter["Weight Measurement"] = '';
        $options = array();
        $xmlResponse = $this->hv->getThings(
            $groupAndFilter,
            $this->recordId,
            $options,
            false
        );

        foreach ($xmlResponse as $thing)
        {
            if($thing->{'weight'})
            {
                $this->assertNotEmpty($thing->{'weight'}, "Weights Have Been Returned");
            }
            elseif($thing->{'height'})
            {
                $this->assertNotEmpty($thing->{'height'}, "Heights Have Been Returned");
            }
        }
    }

    /**
     * ---------TEST THREE: MULTIPLE GROUP NO FILTERS---------
     * This testes the case in which multiple type Id's
     * are passed in along with no filter.
     */
    public function testMultipleGroupsMultipleFilters()
    {

        $groupAndFilter["Height Measurement"] = '<xpath>/thing/data-xml/height/when/date[y &gt; 2012]</xpath>';
        $groupAndFilter["Weight Measurement"] = '<xpath>/thing/data-xml/weight/value[kg &lt; 88]</xpath>';
        $options = array();
        $xmlResponse = $this->hv->getThings(
            $groupAndFilter,
            $this->recordId,
            $options,
            false
        );

        foreach ($xmlResponse as $thing)
        {
            if($thing->{'weight'})
            {
                $this->assertNotEmpty($thing->{'weight'}, "Weights Have Been Returned");
                $this->assertLessThan(88,$thing->{'weight'}->{'value'}->{'kg'});
            }
            elseif($thing->{'height'})
            {
                $this->assertNotEmpty($thing->{'height'}, "Heights Have Been Returned");
                $this->assertGreaterThan(2012, $thing->{'height'}->{'when'}->{'date'}->{'y'});
            }
        }
    }

    public function testProfileRequests(){

        $options = array();
        $userData = $this->hv->getThings(
            array("Personal Demographic Information" => ''),
            $this->recordId,
            $options,
            false
        );

        //foreach($userData as $data)
        print_r($userData[0]->personal);

    }
}