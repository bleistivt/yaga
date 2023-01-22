<?php if (!defined("APPLICATION")) {
    exit();
}

$actionName = $this->data("ActionName");
$otherActions = $this->data("OtherActions", null);

echo heading($this->data("Title"));

echo $this->Form->open(["id" => "DeleteAction"]);
echo $this->Form->errors();

echo wrap(
    sprintf(Gdn::translate("Yaga.Action.ConfirmDelete"), $actionName),
    "div",
    ["class" => "padded"]
);
?>

<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php echo $this->Form->checkbox(
                "Move",
                sprintf(Gdn::translate("Yaga.Action.Move"), $actionName),
                ["id" => "MoveAction"]
            ); ?>
        </div>
        <div class="input-wrap">
            <?php echo $this->Form->dropDown("ReplacementID", $otherActions, [
                "id" => "ReplacementAction",
            ]); ?>
        </div>
    </li>
</ul>

<?php
echo '<div class="js-modal-footer form-footer">';
echo $this->Form->button("Delete Action");
echo $this->Form->button("Cancel", [
    "type" => "button",
    "class" => "btn btn-secondary js-modal-close",
]);
echo "</div>";

echo $this->Form->close();

