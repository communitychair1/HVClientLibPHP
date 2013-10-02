<?php

namespace biologis\HV\HealthRecordItem\GenericTypes;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItem\GenericTypes\CodedValue;
use QueryPath\Query;
use QueryPath;

/**
 * Created by JetBrains PhpStorm.
 * User: ofields
 * Date: 6/25/13
 * Time: 10:30 AM
 * To change this template use File | Settings | File Templates.
 */
class CodableValue extends HealthRecordItemData
{
    public $text = null;
    /**
     * @var array of CodedValue objects
     */
    public $codes = null;


    public static function createFromXML(Query $qp)
    {
        $cv = new CodableValue($qp);
        $cv->text = $qp->find("text")->text();

        $codeQPArray = $qp->find('code');

        foreach($codeQPArray as $codeQP)
        {
            $cv->codes[] = CodedValue::createFromXML($codeQP);
        }

        return $cv;
    }

    public static function createFromData($text,  array $codes = null)
    {
        $item = new CodableValue(QueryPath::withXML());

        $item->text = $text;
        $item->codes = $codes;

        $item->getQp()->top()->append("<container/>");
        $item->getQp()->top()->append("<text/>")
            ->top()->find("text")
            ->text($text);

        if ( !empty($codes))
        {
            foreach( $codes as $codedVal )
            {
                /**
                 * @var $codedVal CodedValue
                 */

                $item->getQp()->top()->append( $codedVal->getQp()->top()->xml(true) );
            }
        }
        return $item;
    }


    public function getObjectXml()
    {
        return $this->getQp()->top()->innerXML();
    }

    public function getItemJSONArray()
    {

        $myData = array(
            "text" => $this->text
        );

        if ( !empty($this->codes))
        {
            foreach ($this->codes as $key=>$code)
            {
                $myData["codes"][] = $code->getItemJSONArray();
            }
        }

        return $myData;
    }

}
