<?php

namespace biologis\HV;

use Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\TestDatetimeFunction;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use biologis\HV\HVRawConnector;
use biologis\HV\HVClient;

require_once("HVClientBaseTest.php");

class HVTrackerTest extends HVClientBaseTest
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

    public function testGatherAllTheThings(){

        //Create a timestamp 14 days in the past
        $dateFilterStrMax = '0 days';
        $dateFilterStrMin = '-20 days';

        $timeMax = date(DATE_ATOM, mktime(0,0,0,
            date('m', strtotime($dateFilterStrMax)),
            date('d', strtotime($dateFilterStrMax)),
            date('Y', strtotime($dateFilterStrMax))));

        $timeMin = date(DATE_ATOM, mktime(0,0,0,
            date('m', strtotime($dateFilterStrMin)),
            date('d', strtotime($dateFilterStrMin)),
            date('Y', strtotime($dateFilterStrMin))));

        //Create an XML filter using timestamp
        $timeFilterMax = '<eff-date-max>'.$timeMax.'</eff-date-max>';
        $timeFilterMin = '<eff-date-min>'.$timeMin.'</eff-date-min>';

        //Init array's for request
        $option = array();
        $requestGroup = array();

        //Populate the Request group
        // Key = TypeName of Thing to request
        // Value = filter on that thing request
        $requestGroup["Sleep Related Activity"] = '';
        $requestGroup["Emotional State"]        = '';
        $requestGroup["Health Journal Entry"]   = '';
        $requestGroup["Allergy"]                = '';
        $requestGroup["Weight Measurement"]     = '';
        $requestGroup["Height Measurement"]     = '';
        $requestGroup["85a21ddb-db20-4c65-8d30-33c899ccf612"]               = '';
        $requestGroup["Dietary Intake"]         = '';
        $requestGroup["Condition"]              = '';
        $requestGroup["Sleep Session"]          = '';

        //Make the request to health vault.
        $response = $this->hv->getThings(
            $requestGroup,
            $this->recordId,
            $option,
            false
        );

        $sleep      = 0;
        $emotion    = 0;
        $journal    = 0;
        $allergy    = 0;
        $weight     = 0;
        $height     = 0;
        $exercise   = 0;
        $diet       = 0;
        $condition  = 0;
        $session    = 0;

        foreach($response as $thing){
            if($thing->{'emotion'})
                $emotion++;
            elseif($thing->{'sleep-pm'})
                $sleep++;
            elseif($thing->{'health-journal-entry'})
                $journal++;
            elseif($thing->{'allergy'})
                $allergy++;
            elseif($thing->{'weight'})
                $weight++;
            elseif($thing->{'height'})
                $height++;
            elseif($thing->{'exercise'})
                $exercise++;
            elseif($thing->{'dietary-intake'})
                $diet++;
            elseif($thing->{'condition'})
                $condition++;
            elseif($thing->{'sleep-am'})
                $session++;
            else
                echo "Warning Data Not Represented \n";
        }

        echo
            "Emotional state: "         . $emotion .    " \n " .
            "Sleep related activity: "  . $sleep .      " \n " .
            "Health journal entry: "    . $journal .    " \n " .
            "Allergy: "                 . $allergy .    " \n " .
            "Weight measurement: "      . $weight .     " \n " .
            "Height measurement: "      . $height .     " \n " .
            "Exercise: "                . $exercise .   " \n " .
            "Diet: "                    . $diet .       " \n " .
            "Condition: "               . $condition .  " \n " .
            "Sleep Session: "           . $session .    " \n " ;
    }


}