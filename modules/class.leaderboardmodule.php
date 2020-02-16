<?php if (!defined('APPLICATION')) exit();

use Yaga;

/* Copyright 2013 Zachary Doll */

/**
 * Renders a leaderboard in the panel detailing points earned of all time
 * 
 * @package Yaga
 * @since 1.0
 */
class LeaderBoardModule extends Gdn_Module {

    /**
     * Holds the title of the module.
     * 
     * @var string
     */
    public $title = false;

    /**
     * Holds the slot type of the module.
     * 
     * @var string Valid options are 'a': All Time, 'w': Weekly, 'm':
     * Monthly, 'y': Yearly 
     */
    public $slotType = 'a';


    /**
     * Set the application folder on construct.
     */
    public function __construct($sender = '') {
        parent::__construct($sender, 'yaga');
    }

    /**
     * Specifies the asset this module should be rendered to.
     * 
     * @return string
     */
    public function assetTarget() {
        return 'Panel';
    }

    /**
     * Set the slot type of the leaderboard. Defaults to 'a' for all time.
     * 
     * @param string $slotType Valid options are 'a': All Time, 'w': Weekly, 'm':
     * Monthly, 'y': Yearly
     */
    public function getData() {
        switch(strtolower($this->SlotType)) {
            case 'w':
                $this->Title = Gdn::translate('Yaga.LeaderBoard.Week');
                $slot = 'w';
                break;
            case 'm':
                $this->Title = Gdn::translate('Yaga.LeaderBoard.Month');
                $slot = 'm';
                break;
            case 'y':
                $this->Title = Gdn::translate('Yaga.LeaderBoard.Year');
                $slot = 'y';
                break;
            default:
            case 'a':
                $this->Title = Gdn::translate('Yaga.LeaderBoard.AllTime');
                $slot = 'a';
                break;
        }

        // Get the leaderboard data
        $leaders = Gdn::sql()
            ->select('up.Points as YagaPoints, u.*')
            ->from('User u')
            ->join('UserPoints up', 'u.UserID = up.UserID')
            ->where('u.Banned', 0)
            ->where('u.Deleted', 0)
            ->where('up.SlotType', $slot)
            ->where('up.TimeSlot', gmdate('Y-m-d', Gdn_Statistics::timeSlotStamp($slot)))
            ->where('up.Source', 'Total')
            ->orderBy('up.Points', 'desc')
            ->limit(Gdn::config('Yaga.LeaderBoard.Limit', 10), 0)
            ->get()
            ->result();

        $this->Data = $leaders;
    }

    /**
     * Renders the leaderboard.
     * 
     * @return string
     */
    public function toString() {
        $this->getData();

        if (count($this->Data)) {
            return parent::toString();
        }
        return '';
    }

}
