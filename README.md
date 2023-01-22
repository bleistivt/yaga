# YAGA for Vanilla 3.3 and up

This is a fork of the great YAGA (Yet Another Gamification Application) for Vanilla forums by @hgtonight.
All of the original credit goes to him.

https://github.com/hgtonight/Application-Yaga

## Installation

This is a **plugin**.
Please install it to `plugins/yaga` and enable it through the Vanilla dashboard.

_If you have the original YAGA application installed:_

- Disable the old application first.
- Make sure you remove the folder `applications/yaga` **entirely** before enabling the new plugin.
  If the old folder still exists, Vanilla will autoload wrong classes and the plugin will not work.
- Delete the addon cache `cache/addon.php`
- Unpack this plugin to `plugins/yaga` and enable it in the dashboard.

Upon installation, all tables and data will be converted automatically.
You may need to run `utility/structure` twice.

If you are upgrading from YAGA 2.0.x or earlier, also run `dba/counts` once (tick "Recalculate Reaction.Latest").
