<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

class PersonalImage extends HealthRecordItemData {

    public static function createFromData($image, $thingId, $thingVersion) {
        $personalImage = HealthRecordItemFactory::getThing('Personal Image');
        $personalImage->getQp()->find('data-other')->text($image);
        $personalImage->getQp()->find('thing-id')->text($thingId);
        $personalImage->getQp()->find('thing-id')->attr("version-stamp", $thingVersion);

        return $personalImage;
    }
}
