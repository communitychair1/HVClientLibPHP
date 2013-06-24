<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ofields
 * Date: 6/24/13
 * Time: 3:34 PM
 * To change this template use File | Settings | File Templates.
 */

namespace biologis\HV\HealthRecordItem\SleepRelatedActivity;


/**
 * Class SRA_Activity
 * @package biologis\HV\HealthRecordItem
 *
 * Simple struct to save the activity data.
 */
class Activity {

    // Timestamp
    public $when;
    // Number of minutes for the activity
    public $minutes;

    public function __construct ($when, $minutes) {
        $this->when = $when;
        $this->minutes = $minutes;
    }

}