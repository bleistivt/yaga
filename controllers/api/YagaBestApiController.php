<?php

use Vanilla\ApiUtils;

/**
 * API Controller for the `/yaga-best` resource.
 */
class YagaBestApiController extends AbstractYagaBestApiController {

    /** @var string pagination path */
    protected $controllerPath = '/api/v2/yaga-best/';

    /**
     * Get the best content of all time.
     *
     * @param array $query The request query.
     * @return Data
     */
    public function get_all(array $query) {
        $this->permission();

        $in = $this->inputSchema('Get the best content of all time.');
        $query = $in->validate($query);
        [$offset, $limit] = ApiUtils::offsetLimit($query);

        $data = $this->reactionModel->getBest(ReactionModel::ITEMS_BEST_ALL, $limit, $offset);

        return $this->calculateOutput($data, 'all', $query, $in);
    }

    /**
     * Get the best recent content.
     *
     * @param array $query The request query.
     * @return Data
     */
    public function get_recent(array $query) {
        $this->permission();

        $in = $this->inputSchema('Get the best recent content.');
        $query = $in->validate($query);
        [$offset, $limit] = ApiUtils::offsetLimit($query);

        $data = $this->reactionModel->getBest(ReactionModel::ITEMS_BEST_RECENT, $limit, $offset);

        return $this->calculateOutput($data, 'recent', $query, $in);
    }

    /**
     * Get the content which received most of a certain reaction.
     *
     * @param int $actionID The ID of the action.
     * @param array $query The request query.
     * @return Data
     */
    public function get_reactions(int $actionID, array $query) {
        $this->permission();

        $in = $this->inputSchema('Get the content which received most of a certain reaction.');
        $query = $in->validate($query);
        [$offset, $limit] = ApiUtils::offsetLimit($query);

        $data = $this->reactionModel->getBest(ReactionModel::ITEMS_BEST_REACTION, $limit, $offset, $actionID);

        return $this->calculateOutput($data, 'reactions/'.$actionID, $query, $in);
    }

}
