<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013 Zachary Doll */

if (!function_exists("renderActionRow")) {
    /**
     * Renders an action row used to construct the action admin screen
     *
     * @since 1.0
     * @param stdClass $action
     * @return string
     */
    function renderActionRow($action)
    {
        $tr = '<tr id="ActionID_' . $action->ActionID . '">';

        $tr .= "<td><strong>" . $action->Name . "</strong></td>";

        $tr .= "<td>" . $action->Tooltip . "</td>";

        $tr .=
            "<td>" .
            plural($action->AwardValue, "%s Point", "%s Points") .
            "</td>";

        $tr .= "<td>";
        $tr .= wrap(
            renderYagaActionIcon($action, "React") .
                wrapIf(rand(1, 18), "span", ["class" => "Count"]) .
                wrap($action->Name, "span", ["class" => "ReactLabel"]),
            "div",
            ["class" => "Preview Reactions"]
        );
        $tr .= "</td>";

        $tr .= '<td class="options">';
        $tr .= renderYagaOptionButtons(
            "action/edit/" . $action->ActionID,
            "action/delete/" . $action->ActionID,
            false
        );
        $tr .= "</td>";

        $tr .= "</tr>";

        return $tr;
    }
}

echo heading(
    $this->title(),
    Gdn::translate("Yaga.Action.Add"),
    "action/add",
    "btn btn-primary"
);

echo helpAsset(
    Gdn::translate("Yaga.Action"),
    Gdn::translate("Yaga.Actions.Desc")
);

echo wrap(Gdn::translate("Yaga.Actions.Settings.Desc"), "div", [
    "class" => "padded",
]);
?>

<div class="table-wrap">
    <table id="Actions" class="table-data Sortable">
        <thead>
            <tr>
                <th><?php echo Gdn::translate("Name"); ?></th>
                <th class="column-lg"><?php echo Gdn::translate(
                    "Tooltip"
                ); ?></th>
                <th><?php echo Gdn::translate("Award Value"); ?></th>
                <th><?php echo Gdn::translate("Preview"); ?></th>
                <th class="options"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->data("Actions") as $action) {
                echo renderActionRow($action);
            } ?>
        </tbody>
    </table>
</div>
