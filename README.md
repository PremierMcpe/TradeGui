
## General

**TradeGui** is a PocketMine-MP plugin that allows players to perform trades with an interactive graphical user
interface (GUI). This plugin provides a smooth and customizable trading experience, including features like localization
for multiple languages and asynchronous trade management.

## Features

- Interactive trade GUI with inventory management.
- Command-based trade system with `/trade` command.
- Supports multiple languages through `.ini` files for localization.
- Custom event handling for trade actions (start, complete).

## Installation

1. Download the latest release of the plugin.
2. Place the `TradeGui` plugin file into the `plugins` directory of your PocketMine-MP server.
3. Start the server.

## Commands

### `/trade`

This is the main command for starting trades.

- **Usage**: `/trade [player_name]`
  - Initiates a trade with the specified player.

## Localization

The plugin supports multiple languages through `.ini` files located in the `plugins_data/TradeGui/languages/` directory.
To add a new language:

1. Create a new `.ini` file in the `plugins_data/TradeGui/languages/` directory (e.g., `es_ES.ini` for Spanish).
2. Define your key-value pairs in the file:
    ```ini
    command.usage = /{command} <player>
    command.description = Allows you to trade with a player
    ```
3. The plugin will automatically load available languages and use them based on player preferences or settings.

### Default Language

The fallback language is `en_US` (defined in `Localization.php`). If a translation for a specific locale is not found,
it defaults to English.

## Events

The plugin uses custom events to handle various trade-related actions:

- **TradeSendEvent**: Triggered when a trade request is sent.
- **TradeCompleteEvent**: Triggered upon successful completion of a trade.

These events are defined in the `Events/` directory and can be listened to by other plugins or extended for custom
logic.