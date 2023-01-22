<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2014 Zachary Doll */

$transportType = $this->data("TransportType");
$filename = $this->data("TransportPath");
$actionCount = $this->data("ActionCount", null);
$badgeCount = $this->data("BadgeCount", null);
$rankCount = $this->data("RankCount", null);
$imageCount = $this->data("ImageCount", null);

echo heading($this->title());
echo wrap(
    sprintf(Gdn::translate("Yaga.$transportType.Success"), $filename),
    "div",
    ["class" => "padded"]
);

$string = "";
if ($actionCount) {
    $string .= wrap(
        Gdn::translate("Yaga.Reactions") . ": " . $actionCount,
        "li"
    );
}
if ($badgeCount) {
    $string .= wrap(Gdn::translate("Yaga.Badges") . ": " . $badgeCount, "li");
}
if ($rankCount) {
    $string .= wrap(Gdn::translate("Yaga.Ranks") . ": " . $rankCount, "li");
}
if ($imageCount) {
    $string .= wrap(Gdn::translate("Image Files") . ": " . $imageCount, "li");
}

echo wrapIf($string, "ul");

echo wrap(
    anchor(Gdn::translate("Yaga.Transport.Return"), "yaga/settings"),
    "div",
    ["class" => "padded"]
);
