# Rocket.Chat to Space

[![Packagist Downloads](https://img.shields.io/packagist/dt/swe/rocketchat-to-space)](https://packagist.org/packages/swe/rocketchat-to-space)
[![Packagist Version](https://img.shields.io/packagist/v/swe/rocketchat-to-space)](https://packagist.org/packages/swe/rocketchat-to-space)
[![License](https://img.shields.io/packagist/l/swe/rocketchat-to-space)](https://packagist.org/packages/swe/rocketchat-to-space)
[![PHP Version](https://img.shields.io/packagist/php-v/swe/rocketchat-to-space)](https://packagist.org/packages/swe/rocketchat-to-space)

## Import your channels with its messages from Rocket.Chat to JetBrains Space
Edit the files `.env` and `user-mapping.json` to your needs.

Then call
```shell
php import.php
```

### user-mapping.json
In the `user-mapping.json` you have to write all the Rocket.Chat and Space usernames. The key is the Rocket.Chat
username, value the Space username.

```json
{
  "john-doe": "John.Doe",
  "jane.doe": "Jane"
}
```

### .env
You have to write the credentials into the .env file so the collector/importer can connect to Rocket.Chat/Space.

```dotenv
ROCKET_CHAT_URL=https://chat.example.com
ROCKET_CHAT_USER=user@example.com
ROCKET_CHAT_PASSWORD=mySecretPassword

SPACE_URL=https://example.jetbrains.space
SPACE_CLIENT_ID=46511156-4651
SPACE_CLIENT_SECRET=561fsef156ht651cbf
```

## Troubleshooting
Sometimes space seems to be too slow for this import to grant permissions after creating channels so maybe this import
will be stopped. In the terminal you can see the channel name and maybe the current import index. So just restart the
import with parameters:

```shell
php import.php the-shown-channel-name the-maybe-shown-index
```