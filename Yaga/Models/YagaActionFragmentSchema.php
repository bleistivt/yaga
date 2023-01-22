<?php

namespace Yaga\Models;

use Garden\Schema\Schema;
use Vanilla\SchemaFactory;

/**
 * Schema to validate the shape of YAGA action (reaction type) records.
 */
class YagaActionFragmentSchema extends Schema
{
    /**
     * Override constructor to initialize schema.
     */
    public function __construct()
    {
        parent::__construct(
            $this->parseInternal([
                "actionID:i" => "The ID of the action (reaction type)",
                "name:s" => "The reactions display name",
                "photo:s?" => "An image for the reaction (optional)",
                "emoji:s?" => "An emoji for the reaction (optional)",
                "tooltip:s" => "A tootip describing the reaction",
                "cssClass:s?" =>
                    "Additional CSS class for styling the reaction (optional)",
                "awardValue:i" => "Positive or negative point value",
            ])
        );
    }

    /** @var YagaActionFragmentSchema */
    private static $cache = null;

    /**
     * @return YagaActionFragmentSchema
     */
    public static function instance(): YagaActionFragmentSchema
    {
        if (self::$cache === null) {
            self::$cache = SchemaFactory::get(
                self::class,
                "YagaActionFragment"
            );
        }

        return self::$cache;
    }
}
