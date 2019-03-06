<?php
/**
 * Holdings (ILS) tab
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
namespace VuFind\RecordTab;

use VuFind\ILS\Connection;

/**
 * Holdings (ILS) tab
 *
 * @category VuFind
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_tabs Wiki
 */
class HoldingsILS extends AbstractBase
{
    /**
     * ILS connection (or null if not applicable)
     *
     * @param Connection
     */
    protected $catalog;

    /**
     * Name of template to use for rendering holdings.
     *
     * @param string
     */
    protected $template;
    
    /**
     * Whether the holdings tab should be hidden when empty or not.
     * @var boolean
     */
    protected $hideHoldingsTabWhenEmpty;

    /**
     * Constructor
     *
     * @param \VuFind\ILS\Connection|bool $catalog  ILS connection to use to check
     * for holdings before displaying the tab; set to null if no check is needed
     * @param string                      $template Holdings template to use
     * @param boolean                     $hideHoldingsTabWhenEmpty Whether the
     * holdings tab should be hidden when empty or not
     */
    public function __construct(Connection $catalog = null, $template = null,
    		$hideHoldingsTabWhenEmpty = false)
    {
        $this->catalog = $catalog;
        $this->template = $template ?? 'standard';
        $this->hideHoldingsTabWhenEmpty = $hideHoldingsTabWhenEmpty;
    }

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Holdings';
    }

    /**
     * Support method used by template -- extract all unique call numbers from
     * an array of items.
     *
     * @param array $items Items to search through.
     *
     * @return array
     */
    public function getUniqueCallNumbers($items)
    {
        $callNos = [];
        foreach ($items as $item) {
            if (isset($item['callnumber']) && strlen($item['callnumber']) > 0) {
                $callNos[] = $item['callnumber'];
            }
        }
        sort($callNos);
        return array_unique($callNos);
    }

    /**
     * Is this tab active?
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->catalog && $this->hideHoldingsTabWhenEmpty) {
            return $this->catalog->hasHoldings($this->driver->getUniqueID());
        }
        return true;
    }

    /**
     * Get name of template for rendering holdings.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    
    /**
     * Getting a paginator for the items list.
     *
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator()
    {
        // The total number of items from the API call
        $totalItemCount = $this->catalog->getTotalItemCount();
        
        // The number of items that should be called with one single API call.
        $itemLimit = $this->catalog->getItemLimit();
        
        // Return if a paginator is not needed
        if ($totalItemCount < $itemLimit) {
            return;
        }
        
        // The currently selected page in the paginator
        $page = $this->getCurrentPage();
        
        // Create the paginator
        $nullAdapter = new \Zend\Paginator\Adapter\NullFill($totalItemCount);
        $paginator = new \Zend\Paginator\Paginator($nullAdapter);
        
        // Some settings for the paginator
        $paginator
            ->setCurrentPageNumber($page)
            ->setItemCountPerPage($itemLimit)
            ->setPageRange(10);
        
        return $paginator;
    }
    
    
    /**
     * Get the currently selected page in the items paginator.
     *
     * @return \Zend\Stdlib\ParametersInterface|mixed
     */
    public function getCurrentPage()
    {
        $page = $this->getRequest()->getQuery('page') ?? null;
        return $page;
    }

}
