<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;
use Exception;


class Comment extends HealthRecordItemData
{

    protected $when = null;
    protected $content = null;
    protected $category = null;

    public function __construct(Query $qp) {

        parent::__construct($qp);

        //Grab the data-xml section fo the record
        $recordQp = $qp->find('data-xml');

        //Check for a timestamp and set the when based on the timestamp
        $txt = $recordQp->find("comment when structured")->text();

        if ( !empty($txt) )
        {
            $this->when = $this->getTimestamp('comment when structured');
        }

        //Pull in the content from the Thing
        $txt = $recordQp->find("comment content")->text();

        if(!empty($txt))
        {
            $this->content = $txt;
        }

        //Create the question codeable object from the XML
        $this->category = CodableValue::createFromXML($recordQp->top()->branch('category'));
    }

    public static function createFromData(
        $when,
        $content,
        CodableValue $category = null,
        Common $common = null
    )
    {
        /**
         * @var $comment Comment
         */
        $comment = HealthRecordItemFactory::getThing('Comment');

        $comment->setCommon($common);

        $comment->when = $when;
        $comment->content = $content;
        $comment->category = $category;

        $comment->setTimestamp('structured', $when);
        $comment->removeOrUpdateIfEmpty( "content", $content);

        if(!is_null($category))
        {
            $comment->getQp()->top()->find('data-xml comment')
                ->append("<category>" . $category->getObjectXml() . "</category>");
        }

        return $comment;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "timestamp" => $this->when,
            "content" => $this->content,
            "category" => $this->category->getItemJSONArray()
        );

        return array_merge($myData, $parentData);
    }
}