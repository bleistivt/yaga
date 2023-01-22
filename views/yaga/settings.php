<?php if (!defined("APPLICATION")) {
    exit();
}

/* Copyright 2013-2014 Zachary Doll */

echo heading($this->title());

echo $this->ConfigurationModule->toString();

echo subheading(
    Gdn::translate("Yaga.Transport"),
    Gdn::translate("Yaga.Transport.Desc")
);

echo wrap(
    anchor(Gdn::translate("Import"), "yaga/import", ["class" => "Button"]) .
        anchor(Gdn::translate("Export"), "yaga/export", ["class" => "Button"]),
    "div",
    ["class" => "form-footer"]
);

?>
