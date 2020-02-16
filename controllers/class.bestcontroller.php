<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2014 Zachary Doll */

/**
 * This is all the frontend pages dealing with badges
 *
 * @since 1.0
 * @package Yaga
 */
class BestController extends Gdn_Controller {

    /**
     * The list of content the filters want to show
     * @var array
     */
    protected $_content = [];

    /**
     * @var array These objects will be created on instantiation and available via
     * $this->ObjectName
     */
    public $uses = ['ActedModel'];

    /**
     * Initializes a frontend controller with the Best Filter, New Discussion, and
     * Discussion Filter modules.
     */
    public function initialize() {
        parent::initialize();
        $this->Application = 'Yaga';
        $this->Head = new HeadModule($this);
        $this->Head->addTag('meta', ['name' => 'robots', 'content' => 'noindex,noarchive']);
        $this->addJsFile('jquery.js');
        $this->addJsFile('jquery-ui.js');
        $this->addJsFile('jquery.livequery.js');
        $this->addJsFile('jquery.popup.js');
        $this->addJsFile('global.js');
        $this->addCssFile('style.css');
        $this->addCssFile('reactions.css');
        $this->addModule('BestFilterModule');
        $this->addModule('NewDiscussionModule');
        $this->addModule('DiscussionFilterModule');
    }

    /**
     * Default to showing the best of all time
     *
     * @param int $page What page of content should be shown
     */
    public function index($page = 0) {
        list($offset, $limit) = $this->_TranslatePage($page);
        $this->title(Gdn::translate('Yaga.BestContent.Recent'));
        $this->_Content = $this->ActedModel->getRecent($limit, $offset);
        $this->_BuildPager($offset, $limit, '/best/%1$s/');
        $this->setData('ActiveFilter', 'Recent');
        $this->render('index');
    }

    /**
     * Get the highest scoring content from all time
     *
     * @param int $page What page of content should be shown
     */
    public function allTime($page = 0) {
        list($offset, $limit) = $this->_TranslatePage($page);
        $this->title(Gdn::translate('Yaga.BestContent.AllTime'));
        $this->_Content = $this->ActedModel->getBest(null, $limit, $offset);
        $this->_BuildPager($offset, $limit, '/best/alltime/%1$s/');
        $this->setData('ActiveFilter', 'AllTime');
        $this->render('index');
    }

    /**
     * Get the latest promoted content
     *
     * @param int $iD Filter on a specific action ID
     * @param int $page What page of content should be shown
     */
    public function action($iD = null, $page = 0) {
        if (is_null($iD) || !is_numeric($iD)) {
            $this->index($page);
            return;
        }
        $actionModel = Yaga::actionModel();
        $action = $actionModel->getByID($iD);
        if (!$action) {
            $this->index($page);
            return;
        }

        list($offset, $limit) = $this->_TranslatePage($page);
        $this->title(sprintf(Gdn::translate('Yaga.BestContent.Action'), $action->Name));
        $this->_Content = $this->ActedModel->getAction($iD, $limit, $offset);
        $this->_BuildPager($offset, $limit, '/best/action/'.$iD.'/%1$s/');
        $this->setData('ActiveFilter', $iD);
        $this->render('index');
    }

    /**
     * Converts a page number to an offset and limit useful for model queries.
     *
     * @param int $page What page of content should be shown
     * @return array An array containing the offset and limit
     */
    protected function _TranslatePage($page) {
        list($offset, $limit) = offsetLimit($page, Gdn::config('Yaga.BestContent.PerPage'));
        if (!is_numeric($offset) || $offset < 0) {
            $offset = 0;
        }
        return [$offset, $limit];
    }

    /**
     * Builds a simple more/less pager to be rendered on the page
     *
     * @param int $offset
     * @param int $limit
     * @param string $link
     */
    protected function _BuildPager($offset, $limit, $link) {
        $pagerFactory = new Gdn_PagerFactory();
        $this->Pager = $pagerFactory->getPager('MorePager', $this);
        $this->Pager->MoreCode = 'More';
        $this->Pager->LessCode = 'Newer Content';
        $this->Pager->ClientID = 'Pager';
        $this->Pager->configure(
            $offset,
            $limit,
            false,
            $link
        );
    }
}
