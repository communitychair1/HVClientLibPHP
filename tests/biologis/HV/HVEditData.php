<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Syntinel
 * Date: 7/22/13
 * Time: 1:55 PM
 * To change this template use File | Settings | File Templates.
 */

namespace biologis\HV;
use biologis\HV\HVClientBaseTest;

require_once("HVClientBaseTest.php");


class HVEditData extends HVClientBaseTest{

    /** Set Up
     * Sets everything necessary for health vault testing
     */
    protected function setUp()
    {
        parent::setUp();
        $this->hv->connect($this->thumbPrint, $this->privateKey);
    }

    public function testEditWeight(){


        //Init filters for query 1
        $groupReq = array(
            "Weight Measurement" => ''
        );

        $options = array('group max' => 1);

        //Make request to retrieve person info;
        $queryData = $this->hv->getThings(
            $groupReq,
            $this->recordId,
            $options,
            false
        );

        $con = $this->hv->getConnector();
        $raw = $con->getRawResponse();

        print_r($raw);
    }

}