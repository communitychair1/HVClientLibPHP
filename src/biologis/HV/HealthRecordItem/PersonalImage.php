<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

class PersonalImage extends HealthRecordItemData {

    public static function createFromData($image, $thingId = null, $thingVersion = null) {
        $personalImage = HealthRecordItemFactory::getThing('Personal Image');
        $personalImage->getQp()->find('data-other')->text($image);
        if($thingId != null)
            $personalImage->getQp()->find('thing-id')->text($thingId);
        if($thingVersion != null)
            $personalImage->getQp()->find('thing-id')->attr("version-stamp", $thingVersion);

        return $personalImage;
    }
}
