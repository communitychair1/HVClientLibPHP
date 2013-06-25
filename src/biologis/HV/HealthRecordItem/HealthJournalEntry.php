<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Health Journal Entry
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.health-journal-entry.1.html
 */
class HealthJournalEntry extends HealthRecordItemData
{

    /**
     *
     * Creates a Health Journal Entry XML Item.
     * @return mixed
     *
     */
    public static function createFromData($when, $content = null, $category = null)
    {
        /**
         * @var $journalEntry HealthJournalEntry
         */
        $journalEntry = HealthRecordItemFactory::getThing('Health Journal Entry');
        // Save the time
        $journalEntry->setTimestamp('when', $when);

        // Save the content as well.
        // TODO: Do we need to escape the XML elements?
        $journalEntry->getQp()->top()->find('content')->text($content);
        $journalEntry->getQp()->top()->find('category text')->text($category);

        return $journalEntry;
    }

}
