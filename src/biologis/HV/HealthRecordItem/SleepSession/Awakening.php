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


    public static function createFromXML(Query $qp, $baseDate = null )
    {
        $item = new Awakening($qp);

        if ( !empty($baseDate) )
        {
            // TODO Change this to a timestamp - should expect one in the constructor
            $item->when = $qp->find("when")->text();
        }
        else
        {
            $item->when = $qp->find("when")->text();
        }

        $item->minutes = $qp->find("minutes")->text();
        return $item;
    }


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

    public function getObjectXml()
    {
        return $this->getQp()->top()->innerXML();
    }

    public function getItemJSONArray()
    {
        $myData = array(
            "when" => $this->when,
            "minutes" => $this->minutes
        );

        return $myData;
    }
}
