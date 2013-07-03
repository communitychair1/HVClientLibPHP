<?php
namespace biologis\HV\HealthRecordItem\GenericTypes;

use biologis\HV\HealthRecordItemData;
use QueryPath\Query;
use QueryPath;

/**
 * Created by JetBrains PhpStorm.
 * User: ofields
 * Date: 6/25/13
 * Time: 10:38 AM
 * To change this template use File | Settings | File Templates.
 */
class CodedValue extends HealthRecordItemData
{
    public $value = null;
    public $type = null;
    public $family = null;
    public $version = null;

    public static function createFromXML(Query $qp)
    {
        $cv = new CodedValue($qp);
        $cv->value = $qp->find("value")->text();
        $cv->type = $qp->find("type")->text();
        $cv->family = $qp->find("family")->text();
        $cv->version = $qp->find("version")->text();
        return $cv;
    }

    public static function createFromData($value, $type, $family = null, $version = null)
    {
        /**
         * @var $parent QueryPath
         */
        $item = new CodedValue(QueryPath::withXML());
        $item->getQp()->top()->append("<code/>");
        $parent = $item->getQp()->top();


        // First 2 items are required.
        $parent->top()
                ->append("<value/>")
                ->find("value")
                ->text($value);

        // Following 2 items are optional.
        if ( !empty($family)) {
            $parent->top()
                    ->append("<family/>")
                    ->find("family")
                    ->text($family);
        }

        $parent->top()
                ->append("<type/>")
                ->find("type")
                ->text($type);

        if ( !empty($version) )
        {
            $parent->top()
                    ->append("<version/>")
                    ->find("version")
                    ->text($version);
        }
        // print_r($parent->top()->innerXML() );
        return $item;
    }

    public function getObjectXml()
    {
        return $this->qp->top()->innerXML();
    }

    public function getItemJSONArray()
    {
        $myData = array(
            "value" => $this->value,
            "type" => $this->type,
            "famiily" => $this->family,
            "version" => $this->version
        );

        return $myData;
    }


}
