<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

class PersonalImage extends HealthRecordItemData {

    public static function createFromData($image) {
        $personalImage = HealthRecordItemFactory::getThing('Personal Image');
        $personalImage->getQp()->find('data-other')->text($image);
        return $personalImage;
    }
}


