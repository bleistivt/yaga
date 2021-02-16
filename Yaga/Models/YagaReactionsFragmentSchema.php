<?php

namespace Yaga\Models;

use Garden\Schema\Schema;
use Vanilla\SchemaFactory;
use Vanilla\Models\UserFragmentSchema;
use Yaga\Models\YagaActionFragmentSchema;

/**
 * Schema to validate the shape of YAGA reactions records.
 */
class YagaReactionsFragmentSchema extends Schema {

    /**
     * Override constructor to initialize schema.
     */
    public function __construct() {
        parent::__construct($this->parseInternal([
            ':a' => YagaActionFragmentSchema::instance()->merge(Schema::parse([
                'insertUserID:i' => 'The user giving the reaction',
                'insertUser' => UserFragmentSchema::instance(),
                'dateInserted:dt' => 'The date the reaction was given'
            ]))
        ]));
    }

    /** @var YagaReactionsFragmentSchema */
    private static $cache = null;

    /**
     * @return YagaReactionsFragmentSchema
     */
    public static function instance(): YagaReactionsFragmentSchema {
        if (self::$cache === null) {
            self::$cache = SchemaFactory::get(self::class, 'YagaReactionsFragment');
        }

        return self::$cache;
    }

}
