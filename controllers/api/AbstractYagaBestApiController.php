<?php

use Garden\Web\Data;
use Vanilla\ApiUtils;
use Yaga\Models\YagaReactionsFragmentSchema;

/**
 * API Controller to be used as a base class for the `/yaga-best` and `/yaga-best-users` resource.
 */
abstract class AbstractYagaBestApiController extends AbstractApiController {

    /** @var string pagination path */
    protected $controllerPath;

    /** @var ReactionModel */
    protected $reactionModel;

    /** @var ActionModel */
    protected $actionModel;

    /** @var UserModel */
    protected $userModel;

    /**
     * AbstractYagaBestApiController constructor.
     *
     * @param ReactionModel $reactionModel
     * @param ActionModel $actionModel
     * @param UserModel $userModel
     */
    public function __construct(ReactionModel $reactionModel, ActionModel $actionModel, UserModel $userModel) {
        $this->reactionModel = $reactionModel;
        $this->actionModel = $actionModel;
        $this->userModel = $userModel;
    }

    /**
     * Get "best" content by page schema.
     *
     * @param string $description The schema description.
     * @return Schema
     */
    protected function inputSchema($description) {
        return $this->schema([
            'limit:i?' => [
                'description' => 'Desired number of items per page.',
                'default' => Gdn::config('Yaga.BestContent.PerPage'),
                'minimum' => 1,
                // APIv2.MaxLimit is 500 by default, which would be too expensive here.
                'maximum' => 50
            ],
            'page:i?' => [
                'description' => 'Page number. See [Pagination](https://docs.vanillaforums.com/apiv2/#pagination).',
                'default' => 1,
                'minimum' => 1
            ],
            'expand?' => ApiUtils::getExpandDefinition(['insertUser', 'yagaReactions'])
        ], 'in')->setDescription($description);
    }

    /**
     * Normalize a database record to match the Schema definition.
     *
     * @param array $dbRecord Database record.
     * @return array
     */
    private function normalizeOutput(array $dbRecord) {
        $this->formatField($dbRecord, 'Body', $dbRecord['Format']);
        $dbRecord['recordType'] = $dbRecord['ItemType'];
        $dbRecord['recordID'] = $dbRecord['ContentID'];

        return ApiUtils::convertOutputKeys($dbRecord);
    }

    /**
     * Form an API response out of the object created by ReactionModel::getBest including paging information.
     *
     * @param object $data ReactionModel::getBest record.
     * @param string $url The path to the resource.
     * @param array $query The request query.
     * @param Schema $in The input schema.
     * @return Data
     */
    protected function calculateOutput($data, $url, $query, $in) {
        $meta = [];
        if ($data->TotalRecords !== false) {
            $meta['paging'] = ApiUtils::numberedPagerInfo(
                $data->TotalRecords,
                $this->controllerPath.$url,
                $query,
                $in
            );
        } else {
            $meta['paging'] = ApiUtils::morePagerInfo(
                $data->Content,
                $this->controllerPath.$url,
                $query,
                $in
            );
        }

        $this->userModel->expandUsers(
            $data->Content,
            $this->resolveExpandFields($query, ['insertUser' => 'InsertUserID'])
        );

        $expandReactions = $this->resolveExpandFields($query, ['yagaReactions' => 'ContentID']);
        if (!empty($expandReactions)) {
            $this->reactionModel->expandYagaReactions($data->Content, ['ContentID', 'ItemType']);
        }

        $rows = array_map([$this, 'normalizeOutput'], $data->Content);

        $out = $this->schema([':a' => [
            'url:s' => 'The URL to this item',
            'recordType:s' => 'The item type',
            'recordID:i' => 'The unique ID of this item',
            'name:s' => 'The title of this item',
            'dateInserted:dt' => 'The date of this item',
            'insertUserID:i' => 'The ID of the user who created this item',
            'insertUser?' => $this->getUserFragmentSchema(),
            'body:s' => 'The content of this item',
            'format:s' => 'The formatter to use for the "Body" field',
            'score:f' => 'The current score of this item (see "yaga_setUserScore" event)',
            'yagaReactions?' => YagaReactionsFragmentSchema::instance()
        ]], 'out');

        return new Data($out->validate($rows), $meta);
    }

}
