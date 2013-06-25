<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ofields
 * Date: 6/25/13
 * Time: 10:31 AM
 * To change this template use File | Settings | File Templates.
 */
namespace biologis\HV\HealthRecordItem\SleepSession;

use biologis\HV\HealthRecordItemData;
use QueryPath\Query;
use QueryPath;


class Awakening extends HealthRecordItemData
{
    public $when = null;
    public $minutes = null;


    public static function createFromData($when, $minutes)
    {
        $item = new Awakening(QueryPath::withXML());
        $item->getQp()->top()->append("<container/>");

        $item->getQp()->top()->append("<when/>");

        $item->setTime($item->getQp()->top()->find("when"), $when );

        $item->getQp()->top()->append("<minutes/>")
            ->top()->find("minutes")
            ->text($minutes);

        return $item;
    }

    public function getItemXml()
    {
        return $this->getQp()->top()->innerXML();
    }
}