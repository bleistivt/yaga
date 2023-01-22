<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013 Zachary Doll */

$ageArray = ageArray();

echo heading(
    $this->title(),
    Gdn::translate("Yaga.Rank.Add"),
    "rank/add",
    "btn btn-primary js-modal"
);

echo helpAsset(Gdn::translate("Yaga.Ranks"), Gdn::translate("Yaga.Ranks.Desc"));

echo $this->Form->open(["enctype" => "multipart/form-data", "class" => "Rank"]);
echo $this->Form->errors();
?>

<ul>
    <li class="form-group">
        <div class="label-wrap">
            <?php
            echo $this->Form->label("Photo", "PhotoUpload");
            echo wrap(Gdn::translate("Yaga.Rank.Photo.Desc"), "div", [
                "class" => "info",
            ]);
            $photo = Gdn::config("Yaga.Ranks.Photo", false);

            if ($photo) {
                echo "<br />";
                echo img($photo);
            }
            ?>
        </div>
        <div class="input-wrap">
            <?php
            echo $this->Form->input("PhotoUpload", "file");
            if ($photo) {
                echo "<br />" .
                    anchor(
                        Gdn::translate("Delete Photo"),
                        "rank/deletephoto",
                        "btn btn-primary js-modal-confirm",
                        [
                            "data-body" => sprintf(
                                Gdn::translate(
                                    "Are you sure you want to delete this %s?"
                                ),
                                Gdn::translate("Photo")
                            ),
                        ]
                    );
            }
            ?>
        </div>
    </li>
</ul>

<?php
echo $this->Form->close("Save");

echo wrap(Gdn::translate("Yaga.Ranks.Settings.Desc"), "div", [
    "class" => "padded",
]);
?>

<div class="table-wrap">
    <table id="Ranks" class="table-data Sortable">
        <thead>
            <tr>
                <th><?php echo Gdn::translate("Name"); ?></th>
                <th class="column-lg"><?php echo Gdn::translate(
                    "Description"
                ); ?></th>
                <th class="column-sm"><?php echo Gdn::translate(
                    "Yaga.Ranks.PointsReq"
                ); ?></th>
                <th class="column-sm"><?php echo Gdn::translate(
                    "Yaga.Ranks.PostsReq"
                ); ?></th>
                <th><?php echo Gdn::translate("Yaga.Ranks.AgeReq"); ?></th>
                <th class="column-sm"><?php echo Gdn::translate(
                    "Auto Award"
                ); ?></th>
                <th class="options column-sm"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->data("Ranks") as $rank) {
                $row =
                    '<tr id="RankID_' .
                    $rank->RankID .
                    '" data-rankid="' .
                    $rank->RankID .
                    '">';

                $row .= wrap(wrap($rank->Name, "strong"), "td");

                $row .= wrap($rank->Description, "td");

                $row .= wrap($rank->PointReq, "td");

                $row .= wrap($rank->PostReq, "td");

                $row .= wrap($ageArray[$rank->AgeReq], "td");

                $row .= wrap(
                    renderYagaToggle(
                        "rank/toggle/" . $rank->RankID,
                        $rank->Enabled,
                        $rank->RankID
                    ),
                    "td"
                );

                $row .= '<td class="options">';
                $row .= renderYagaOptionButtons(
                    "rank/edit/" . $rank->RankID,
                    "rank/delete/" . $rank->RankID
                );
                $row .= "</td>";

                $row .= "</tr>";

                echo $row;
            } ?>
        </tbody>
    </table>
</div>
