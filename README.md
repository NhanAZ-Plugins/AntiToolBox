# AntiToolBox

Disconnect Android clients whose reported device model begins with a brand name that is not written in uppercase.

## How it works

AntiToolBox reads the `DeviceOS` and `DeviceModel` claims from the Bedrock login packet using Axolotl-PM's JWT parser. For Android, it compares the first word of the model with its uppercase form. A mismatch triggers the configured disconnect message.

This is a **casing heuristic on client-reported data**. Modified clients can change the claim, and legitimate clients can be disconnected. AntiToolBox does not authenticate the device model or provide reliable detection of Toolbox or other third-party clients. Test it with your own client mix before enabling it on a production server.

## Requirements and compatibility

- Axolotl-PM API `5.0.0` and PHP 8.1 or newer.
- No separate `libBedrock` runtime dependency.

The source passes PHPStan at maximum level against Axolotl-PM 5.0.0 and the server revision pinned in the [build workflow](.github/workflows/build.yml). The release PHAR has been smoke tested on that pinned server revision. These checks do not verify every 5.x release or the behavior of a connected Bedrock client.

## Installation and configuration

Download `AntiToolBox.phar` from the [GitHub releases](https://github.com/NhanAZ-Plugins/AntiToolBox/releases), place it in `plugins/`, and start the server. The plugin creates `plugin_data/AntiToolBox/config.yml`.

```yaml
kickMessage: "Please use original client to join!"
```

Change `kickMessage` to a string appropriate for your server. If the value is not a string, the plugin uses a built-in English fallback. Remove the PHAR and restart the server to roll back. Back up the configuration first if you want to retain your message.

## Building and testing

The [build workflow](.github/workflows/build.yml) uses [DevTools](https://github.com/NhanAZ/DevTools) to run PHPStan at maximum level, build the plugin PHAR, and validate its checksum and license. Run `php tests/login-model.php` for the login payload fixture. It checks malformed claims, non-Android clients, uppercase model names, case mismatches, and invalid message configuration. The fixture does not connect a real Bedrock client.

## Credits and license

AntiToolBox was created by [Ngọc Lam](https://github.com/NgLamVN). The original warning about false positives and incomplete Toolbox detection remains relevant. This repository retains the original [MIT license](LICENSE). For current support, use [NhanAZ Discord](https://discord.gg/j2X83ujT6c).
