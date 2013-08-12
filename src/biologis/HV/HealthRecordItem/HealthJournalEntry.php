<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

use QueryPath\Query;

/**
 * Health Journal Entry
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.health-journal-entry.1.html
 */
class HealthJournalEntry extends HealthRecordItemData
{

    protected $when = null;
    protected $descriptiveWhen = null;
    protected $content = null;
    protected $category = null;
    protected $source = null;
    protected $relatedThingId = null;
    protected $relatedThingVersion = null;
    protected $relatedThingRealationship = null;

    public function __construct(Query $qp) {
        parent::__construct($qp);
        $recordQp = $qp->top()->find("data-xml");
        $commonQp = $qp->find('common');

        if ($recordQp) {
            $text = $qp->top()->find("data-xml when date y")->xml();
           if (!empty($text))
           {
               $this->when = $this->getTimestamp("data-xml when structured");
           }

           $this->descriptiveWhen = $qp->top()->find("data-xml descriptive")->text();
           $this->content = $qp->top()->find("data-xml content")->text();
           $this->category= $qp->top()->find("data-xml category text")->text();
        }

        if($recordQp->find("common related-thing thing-id")->text())
        {
            $this->relatedThingId = $commonQp->find("related-thing thing-id")->text();
        }
        if($recordQp->find("common related-thing version-stamp")->text())
        {
            $this->relatedThingVersion = $commonQp->find("related-thing version-stamp")->text();
        }
        if($recordQp->find("common related-thing relationship-type")->text())
        {
            $this->relatedThingRealationship = $commonQp->find("related-thing relationship-type")->text();
        }
        if($recordQp->find("common source")->text())
        {
            $this->source = $commonQp->find("source")->text();
        }

    }

    /**
     *
     * Creates a Health Journal Entry XML Item.
     * @return mixed
     *
     */
    public static function createFromData(
        $when = null,
        $descriptiveWhen = null,
        $content = null,
        $category = null,
        array $common = null
)
    {
        /**
         * @var $journalEntry HealthJournalEntry
         */
        $journalEntry = HealthRecordItemFactory::getThing('Health Journal Entry');
        $journalEntry = parent::createCommonFromData($common, $journalEntry);
        // Either $when or $descriptiveWhen needs to be set. We'll remove the node that we don't use.
        if ( !empty($when) )
        {
            $journalEntry->setTimestamp('when', $when);
            // Remove the descriptive node.
            $journalEntry->getQp()->find("descriptive")->remove();
        }
        else
        {
            $journalEntry->getQp()->find("descriptive")->text($descriptiveWhen);
            // Remove the when node.
            $journalEntry->getQp()->find("when structured")->remove();
        }

        // Save the content as well.
        $journalEntry->getQp()->top()->find('content')->text($content);
        $journalEntry->getQp()->top()->find('category text')->text($category);

        return $journalEntry;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "timestamp" => $this->when,
            "descriptive when" => $this->descriptiveWhen,
            "content" => $this->content,
            "category text" => $this->category,
            "relatedThingId" => $this->relatedThingId,
            "relatedThingVersion" => $this->relatedThingVersion,
            "relatedThingRelationship" => $this->relatedThingRealationship
        );
        if(isset($this->source))
        {
            $myData['source'] = $this->source;
        }
        return array_merge($myData, $parentData);
    }

    public function getContent()
    {
        return $this->content;
    }

}
