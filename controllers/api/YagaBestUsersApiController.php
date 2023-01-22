<?php

use Garden\Schema\Schema;
use Vanilla\ApiUtils;
use Yaga\Models\YagaActionFragmentSchema;

/**
 * API Controller for the `/yaga-best-users` resource.
 */
class YagaBestUsersApiController extends AbstractYagaBestApiController
{
    /** @var string pagination path */
    protected $controllerPath = "/api/v2/yaga-best-users/";

    /**
     * Get the content of a user which received a certain reaction.
     *
     * @param int $userID The ID of the user.
     * @param int $actionID The ID of the action.
     * @param array $query The request query.
     * @return Data
     */
    public function get_reactions(int $userID, int $actionID, array $query)
    {
        $this->permission("Garden.Profiles.View");

        $in = $this->inputSchema(
            "Get the content of a user which received a certain reaction."
        );
        $query = $in->validate($query);
        [$offset, $limit] = ApiUtils::offsetLimit($query);

        $data = $this->reactionModel->getBest(
            ReactionModel::ITEMS_PROFILE_REACTION,
            $limit,
            $offset,
            $actionID,
            $userID
        );

        return $this->calculateOutput(
            $data,
            $userID . "/reactions/" . $actionID,
            $query,
            $in
        );
    }

    /**
     * Get the best content of a user.
     *
     * @param int $userID The ID of the user.
     * @param array $query The request query.
     * @return Data
     */
    public function get_best(int $userID, array $query)
    {
        $this->permission("Garden.Profiles.View");

        $in = $this->inputSchema("Get the best content of a user.");
        $query = $in->validate($query);
        [$offset, $limit] = ApiUtils::offsetLimit($query);

        $data = $this->reactionModel->getBest(
            ReactionModel::ITEMS_PROFILE_BEST,
            $limit,
            $offset,
            false,
            $userID
        );

        return $this->calculateOutput($data, $userID . "/best/", $query, $in);
    }

    /**
     * Get all available reactions including totals (received) for user.
     *
     * @param int $userID The ID of the user.
     * @return array
     */
    public function get_totals(int $userID)
    {
        $this->permission("Garden.Profiles.View");

        $actions = $this->actionModel->get();
        $data = [];

        foreach ($actions as $action) {
            $action->Count = $this->reactionModel->getUserCount(
                $userID,
                $action->ActionID
            );
            $data[] = ApiUtils::convertOutputKeys((array) $action);
        }

        $out = $this->schema(
            [
                ":a" => YagaActionFragmentSchema::instance()->merge(
                    Schema::parse([
                        "permission:s" =>
                            "The permission required to use this reaction",
                        "count:i" =>
                            "The number received of this reaction (user profiles)",
                    ])
                ),
            ],
            "out"
        );

        return $out->validate($data);
    }
}
