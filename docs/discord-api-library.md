# Discord API Library

A comprehensive Laravel library for making HTTP requests to the Discord API. This library integrates seamlessly with Laravel's HTTP client and provides a fluent interface for Discord API operations.

## Features

-   **Bot Token Authentication**: Automatic bot token authentication for all requests
-   **Retry Logic**: Built-in retry mechanism for failed requests and rate limits
-   **Rate Limit Handling**: Automatic rate limit detection and handling
-   **Fluent Interface**: Clean, intuitive fluent interface for Discord API operations
-   **Laravel Integration**: Full integration with Laravel's service container and facades
-   **Comprehensive Coverage**: Support for users, guilds, channels, messages, roles, and more
-   **Error Handling**: Proper exception handling and logging
-   **Testing Support**: Built-in testing utilities and mock support
-   **Global Guild ID**: Optional global guild ID configuration for simplified API calls

## Installation

The library is already integrated into the application. No additional installation is required.

## Configuration

Add the following environment variables to your `.env` file:

```env
# Discord API Configuration
DISCORD_API_BASE_URL=https://discord.com/api/v10
DISCORD_BOT_TOKEN=your_bot_token_here
DISCORD_GUILD_ID=your_guild_id_here  # Optional: Global guild ID for simplified API calls
DISCORD_API_TIMEOUT=30
DISCORD_API_RETRY_ATTEMPTS=3
DISCORD_API_RETRY_DELAY=1000
```

### Configuration Options

| Option                       | Default                       | Description                                |
| ---------------------------- | ----------------------------- | ------------------------------------------ |
| `DISCORD_API_BASE_URL`       | `https://discord.com/api/v10` | Discord API base URL                       |
| `DISCORD_BOT_TOKEN`          | `null`                        | Your Discord bot token                     |
| `DISCORD_GUILD_ID`           | `null`                        | Your Discord guild ID for simplified calls |
| `DISCORD_API_TIMEOUT`        | `30`                          | Request timeout in seconds                 |
| `DISCORD_API_RETRY_ATTEMPTS` | `3`                           | Number of retry attempts                   |
| `DISCORD_API_RETRY_DELAY`    | `1000`                        | Delay between retries in milliseconds      |

## Usage

### Using the Facade

The easiest way to use the Discord API library is through the facade:

```php
use App\Facades\DiscordApi;

// Get user information using fluent interface
$user = DiscordApi::user('123456789')->get();

// Send a message to a channel using fluent interface
$message = DiscordApi::channel('channel-id')->send([
    'content' => 'Hello, Discord!'
]);
```

### Using Dependency Injection

You can also inject the service directly:

```php
use App\Services\Discord\Api;

class DiscordController extends Controller
{
    public function __construct(private Api $discordApi)
    {
    }

    public function sendMessage(Request $request)
    {
        $response = $this->discordApi->channel('channel-id')->send([
            'content' => $request->input('message')
        ]);

        return response()->json($response->json());
    }
}
```

### Using the Service Container

```php
$discordApi = app('discord-api');
// or
$discordApi = app(Api::class);
```

## Fluent Interface

The library provides a comprehensive fluent interface for all Discord API operations. This approach offers better developer experience, cleaner code, and improved type safety.

### User Operations

All user operations are handled through the fluent interface:

```php
// Get a user-specific API instance
$user = DiscordApi::user('user-id');

// Get user information
$userInfo = $user->get();

// Get current bot user information
$bot = DiscordApi::user()->me();
```

### Invite Operations

All invite operations are handled through the fluent interface:

```php
// Get an invite-specific API instance
$invite = DiscordApi::invite('invite-code');

// Get invite information
$inviteInfo = $invite->get();

// Get invite with additional data
$inviteWithCounts = $invite->get(['with_counts' => true]);

// Delete an invite
$invite->delete('Invite expired');
```

### Application Commands Operations

All application command operations are handled through the fluent interface:

```php
// Get an application-specific API instance
$app = DiscordApi::application('application-id');

// Get global application commands
$commands = $app->commands();

// Create a global application command
$command = $app->createCommand([
    'name' => 'ping',
    'description' => 'Ping command',
    'type' => 1
]);

// Get a specific application command
$specificCommand = $app->command('command-id');

// Update an application command
$app->updateCommand('command-id', [
    'description' => 'Updated ping command'
]);

// Delete an application command
$app->deleteCommand('command-id');

// Bulk overwrite application commands
$app->bulkOverwriteCommands([
    ['name' => 'ping', 'description' => 'Ping command', 'type' => 1],
    ['name' => 'echo', 'description' => 'Echo command', 'type' => 1]
]);

// Get guild-specific application commands
$guildCommands = $app->guildCommands('guild-id');

// Create a guild-specific application command
$guildCommand = $app->createGuildCommand('guild-id', [
    'name' => 'ping',
    'description' => 'Ping command',
    'type' => 1
]);

// Get a specific guild application command
$specificGuildCommand = $app->guildCommand('guild-id', 'command-id');

// Update a guild application command
$app->updateGuildCommand('guild-id', 'command-id', [
    'description' => 'Updated ping command'
]);

// Delete a guild application command
$app->deleteGuildCommand('guild-id', 'command-id');

// Bulk overwrite guild application commands
$app->bulkOverwriteGuildCommands('guild-id', [
    ['name' => 'ping', 'description' => 'Ping command', 'type' => 1],
    ['name' => 'echo', 'description' => 'Echo command', 'type' => 1]
]);

// Get application command permissions for a guild
$permissions = $app->guildCommandPermissions('guild-id');

// Get application command permissions for a specific command
$commandPermissions = $app->guildCommandPermission('guild-id', 'command-id');

// Edit application command permissions
$app->editGuildCommandPermissions('guild-id', 'command-id', [
    'permissions' => [
        ['id' => 'role-id', 'type' => 1, 'permission' => true]
    ]
]);

// Batch edit application command permissions
$app->batchEditGuildCommandPermissions('guild-id', [
    [
        'id' => 'command-id',
        'permissions' => [
            ['id' => 'role-id', 'type' => 1, 'permission' => true]
        ]
    ]
]);
```

### Fluent API Benefits

The fluent interface provides several advantages for all operations:

-   **Cleaner syntax**: No repetitive ID parameters
-   **Better IDE support**: Autocomplete works better with method chaining
-   **More intuitive**: Context is established once, then all operations are specific to that context
-   **Less repetition**: IDs are handled automatically
-   **Type safety**: Each instance ensures operations are valid for that type
-   **Logical grouping**: Related operations are grouped together

### Practical Examples

Here's how you might use the fluent interfaces in a real application:

```php
// User management service
class DiscordUserService
{
    public function getUserInfo(string $userId)
    {
        return DiscordApi::user($userId)->get()->json();
    }

    public function getBotInfo()
    {
        return DiscordApi::user()->me()->json();
    }

    public function validateUser(string $userId)
    {
        try {
            $user = DiscordApi::user($userId)->get();
            return $user->successful();
        } catch (RequestException $e) {
            return false;
        }
    }
}

// Invite management service
class DiscordInviteService
{
    public function getInviteDetails(string $inviteCode)
    {
        return DiscordApi::invite($inviteCode)->get(['with_counts' => true])->json();
    }

    public function cleanupExpiredInvite(string $inviteCode)
    {
        return DiscordApi::invite($inviteCode)->delete('Invite expired');
    }

    public function validateInvite(string $inviteCode)
    {
        try {
            $invite = DiscordApi::invite($inviteCode)->get();
            return $invite->successful();
        } catch (RequestException $e) {
            return false;
        }
    }
}

// Application command management service
class DiscordApplicationService
{
    public function setupCommands(string $applicationId, string $guildId)
    {
        $app = DiscordApi::application($applicationId);

        // Create guild-specific commands
        $commands = [
            ['name' => 'ping', 'description' => 'Ping command', 'type' => 1],
            ['name' => 'echo', 'description' => 'Echo command', 'type' => 1],
            ['name' => 'help', 'description' => 'Help command', 'type' => 1]
        ];

        return $app->bulkOverwriteGuildCommands($guildId, $commands);
    }

    public function updateCommandPermissions(string $applicationId, string $guildId, string $commandId, array $roleIds)
    {
        $app = DiscordApi::application($applicationId);

        $permissions = array_map(function ($roleId) {
            return ['id' => $roleId, 'type' => 1, 'permission' => true];
        }, $roleIds);

        return $app->editGuildCommandPermissions($guildId, $commandId, [
            'permissions' => $permissions
        ]);
    }

    public function getCommandList(string $applicationId, ?string $guildId = null)
    {
        $app = DiscordApi::application($applicationId);

        if ($guildId) {
            return $app->guildCommands($guildId)->json();
        }

        return $app->commands()->json();
    }
}
```

### Guild (Server) Operations

All guild operations are handled through the fluent interface:

```php
// Get a guild-specific API instance (uses global guild ID if configured)
$guild = DiscordApi::guild();

// Or specify a guild ID explicitly
$guild = DiscordApi::guild('123456789');

// Get guild information
$guildInfo = $guild->get();

// Get guild members
$members = $guild->members();

// Get a specific guild member
$member = $guild->member('user-id');

// Get guild roles
$roles = $guild->roles();

// Create a guild role
$role = $guild->createRole([
    'name' => 'Moderator',
    'color' => 16776960,
    'permissions' => '2048'
], 'New moderator role for server management');

// Update a guild role
$updatedRole = $guild->updateRole('role-id', [
    'name' => 'Updated Role'
], 'Role renamed for clarity');

// Delete a guild role
$guild->deleteRole('role-id', 'Role no longer needed');

// Add role to member
$guild->addMemberRole('user-id', 'role-id', 'Promoted for excellent contributions');

// Remove role from member
$guild->removeMemberRole('user-id', 'role-id', 'Role removed due to inactivity');

// Ban a guild member
$guild->banMember('user-id', [
    'delete_message_days' => 7,
    'reason' => 'Violation of community guidelines'
], 'Repeated violations of server rules');

// Unban a guild member
$guild->unbanMember('user-id', 'Appeal approved');

// Kick a guild member
$guild->kickMember('user-id', 'Temporary removal for rule violation');

// Update a guild member
$guild->updateMember('user-id', [
    'nick' => 'New Nickname'
], 'Nickname updated by admin');
```

### Global Guild ID Support

When a global guild ID is configured via `DISCORD_GUILD_ID`, you can use the fluent interface without specifying a guild ID:

```php
// With global guild ID configured, you can call guild() without parameters
$guild = DiscordApi::guild(); // Uses global guild ID
$guildInfo = $guild->get();
$members = $guild->members();
$roles = $guild->roles();

// You can still override the global guild ID by passing it explicitly
$otherGuild = DiscordApi::guild('different-guild-id'); // Uses provided guild ID

// Check if global guild ID is configured
if (DiscordApi::hasGlobalGuildId()) {
    $globalGuildId = DiscordApi::getGlobalGuildId();
    // Use global guild ID functionality
}
```

**Note**: If no global guild ID is configured and you don't provide a guild ID parameter to `guild()`, the library will throw an `InvalidArgumentException`.

### Fluent Guild API Benefits

The fluent interface provides several advantages:

-   **Cleaner syntax**: No awkward optional parameters before required ones
-   **Better IDE support**: Autocomplete works better with method chaining
-   **More intuitive**: Guild context is established once, then all operations are guild-specific
-   **Less repetition**: Guild ID is handled automatically
-   **Type safety**: GuildApi instance ensures guild operations

### Practical Example

Here's how you might use the fluent interface in a real application:

```php
// In your .env file:
// DISCORD_GUILD_ID=123456789012345678

// In your controller or service:
class DiscordService
{
    public function syncUserRoles(string $userId, array $roleIds)
    {
        $guild = DiscordApi::guild();

        // Remove all existing roles
        $existingRoles = $guild->member($userId)->json();
        foreach ($existingRoles['roles'] ?? [] as $roleId) {
            $guild->removeMemberRole($userId, $roleId);
        }

        // Add new roles
        foreach ($roleIds as $roleId) {
            $guild->addMemberRole($userId, $roleId);
        }
    }

    public function getServerInfo()
    {
        return DiscordApi::guild()->get()->json();
    }

    public function moderateUser(string $userId, string $action, ?string $reason = null)
    {
        $guild = DiscordApi::guild();

        return match ($action) {
            'ban' => $guild->banMember($userId, ['delete_message_days' => 7], $reason),
            'kick' => $guild->kickMember($userId, $reason),
            'unban' => $guild->unbanMember($userId, $reason),
            default => throw new InvalidArgumentException("Unknown action: {$action}"),
        };
    }
}
```

### Channel Operations

All channel operations are handled through the fluent interface:

```php
// Get a channel-specific API instance
$channel = DiscordApi::channel('channel-id');

// Get channel information
$channelInfo = $channel->get();

// Update a channel
$updatedChannel = $channel->update([
    'name' => 'updated-channel-name'
], 'Channel renamed for better organization');

// Delete a channel
$channel->delete('Channel cleanup - no longer needed');

// Send a message to this channel
$message = $channel->send([
    'content' => 'Hello, Discord!',
    'tts' => false
]);

// Get channel messages
$messages = $channel->getMessages(['limit' => 50]);

// Create a webhook for this channel
$webhook = $channel->createWebhook([
    'name' => 'My Webhook'
]);

// Get channel invites
$invites = $channel->getInvites();

// Create a channel invite
$invite = $channel->createInvite([
    'max_age' => 3600,
    'max_uses' => 10,
    'temporary' => false
], 'Temporary invite for event');
```

### Message Operations (via Fluent Interface)

Message operations within channels:

```php
// Get a message-specific API instance
$message = DiscordApi::channel('channel-id')->message('message-id');

// Get message information
$messageInfo = $message->get();

// Update a message
$updatedMessage = $message->update([
    'content' => 'Updated message'
]);

// Delete a message
$message->delete('Message contained inappropriate content');

// Add a reaction
$message->addReaction('😀');

// Remove a reaction
$message->removeReaction('😀');

// Get reactions for a message
$reactions = $message->getReactions('😀', ['limit' => 10]);

// Delete all reactions from a message
$message->deleteAllReactions();

// Delete all reactions of a specific emoji
$message->deleteAllReactionsForEmoji('😀');
```

### Messages Collection Operations (via Fluent Interface)

```php
// Get a messages API instance
$messages = DiscordApi::channel('channel-id')->messages();

// Get channel messages
$channelMessages = $messages->get(['limit' => 50]);

// Create a message in this channel
$newMessage = $messages->create([
    'content' => 'New message'
]);

// Get a specific message
$specificMessage = $messages->getMessage('message-id');

// Update a specific message
$updatedMessage = $messages->update('message-id', [
    'content' => 'Updated content'
]);

// Delete a specific message
$messages->delete('message-id', 'Message cleanup');

// Get a message-specific API instance
$messageApi = $messages->message('message-id');
```

### Webhook Operations (via Fluent Interface)

```php
// Get a webhooks API instance
$webhooks = DiscordApi::channel('channel-id')->webhooks();

// Get channel webhooks
$channelWebhooks = $webhooks->get();

// Create a webhook for this channel
$webhook = $webhooks->create([
    'name' => 'My Webhook'
]);

// Get a webhook-specific API instance
$webhookApi = DiscordApi::webhook('webhook-id');

// Get webhook information
$webhookInfo = $webhookApi->get();

// Update a webhook
$webhookApi->update([
    'name' => 'Updated Webhook'
], 'Webhook renamed for clarity');

// Delete a webhook
$webhookApi->delete('Webhook no longer needed');

// Execute a webhook with token
$webhookApi->execute('webhook-token', [
    'content' => 'Webhook message',
    'username' => 'Custom Username'
]);

// Update a webhook with token
$webhookApi->updateWithToken('webhook-token', [
    'name' => 'Updated Webhook'
], 'Webhook renamed');

// Delete a webhook with token
$webhookApi->deleteWithToken('webhook-token', 'Webhook cleanup');
```

### Invite Operations (via Fluent Interface)

```php
// Get an invites API instance
$invites = DiscordApi::channel('channel-id')->invites();

// Get channel invites
$channelInvites = $invites->get();

// Create a channel invite
$invite = $invites->create([
    'max_age' => 3600,
    'max_uses' => 10,
    'temporary' => false
], 'Temporary invite for event');
```

### Reactions Operations (via Fluent Interface)

```php
// Get a reactions API instance
$reactions = DiscordApi::channel('channel-id')->message('message-id')->reactions();

// Add a reaction to this message
$reactions->add('😀');

// Remove a reaction from this message
$reactions->remove('😀');

// Get reactions for this message
$messageReactions = $reactions->get('😀', ['limit' => 10]);

// Delete all reactions from this message
$reactions->deleteAll();

// Delete all reactions of a specific emoji from this message
$reactions->deleteAllForEmoji('😀');
```

### Fluent Channel API Benefits

The fluent interface provides several advantages for channel operations:

-   **Cleaner syntax**: No repetitive channel ID parameters
-   **Better IDE support**: Autocomplete works better with method chaining
-   **More intuitive**: Channel context is established once, then all operations are channel-specific
-   **Less repetition**: Channel ID is handled automatically
-   **Type safety**: Channel instance ensures channel operations
-   **Logical grouping**: Related operations are grouped together (messages, webhooks, invites)

### Practical Channel Examples

Here's how you might use the fluent interface in a real application:

```php
// In your controller or service:
class DiscordChannelService
{
    public function moderateChannel(string $channelId, string $messageId, string $action, ?string $reason = null)
    {
        $channel = DiscordApi::channel($channelId);
        $message = $channel->message($messageId);

        return match ($action) {
            'delete' => $message->delete($reason),
            'edit' => $message->update(['content' => 'Message edited by moderator'], $reason),
            'react' => $message->addReaction('👮'),
            default => throw new InvalidArgumentException("Unknown action: {$action}"),
        };
    }

    public function getChannelStats(string $channelId)
    {
        $channel = DiscordApi::channel($channelId);

        return [
            'info' => $channel->get()->json(),
            'message_count' => count($channel->getMessages(['limit' => 100])->json()),
            'webhook_count' => count($channel->webhooks()->get()->json()),
            'invite_count' => count($channel->invites()->get()->json()),
        ];
    }

    public function cleanupChannel(string $channelId, array $messageIds, ?string $reason = null)
    {
        $channel = DiscordApi::channel($channelId);

        foreach ($messageIds as $messageId) {
            $channel->message($messageId)->delete($reason);
        }
    }
}
```

### Guild Management Operations (via Fluent Interface)

```php
// Get guild emojis
$emojis = $guild->emojis();

// Get a specific emoji
$emoji = $guild->emoji('emoji-id');

// Create a guild emoji
$emoji = $guild->createEmoji([
    'name' => 'custom_emoji',
    'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
], 'New custom emoji for server');

// Update a guild emoji
$guild->updateEmoji('emoji-id', [
    'name' => 'updated_emoji'
], 'Emoji renamed');

// Delete a guild emoji
$guild->deleteEmoji('emoji-id', 'Emoji no longer needed');

// Get guild invites
$invites = $guild->invites();

// Get guild voice regions
$regions = $guild->voiceRegions();

// Get guild integrations
$integrations = $guild->integrations();

// Delete a guild integration
$guild->deleteIntegration('integration-id', 'Integration removed');

// Get guild widget
$widget = $guild->widget();

// Update guild widget
$guild->updateWidget([
    'enabled' => true,
    'channel_id' => 'channel-id'
]);

// Get guild vanity URL
$vanityUrl = $guild->vanityUrl();

// Get guild welcome screen
$welcomeScreen = $guild->welcomeScreen();

// Update guild welcome screen
$guild->updateWelcomeScreen([
    'enabled' => true,
    'welcome_channels' => [
        [
            'channel_id' => 'channel-id',
            'description' => 'Welcome to our server!',
            'emoji_id' => 'emoji-id'
        ]
    ]
]);

// Get guild audit log
$auditLog = $guild->auditLog([
    'limit' => 50,
    'action_type' => 20 // Member kick
]);

// Get guild preview
$preview = $guild->preview();
```

### Guild Templates (via Fluent Interface)

```php
// Get guild templates
$templates = $guild->templates();

// Create a guild template
$template = $guild->createTemplate([
    'name' => 'My Template',
    'description' => 'A template for new servers'
]);

// Sync a guild template
$guild->syncTemplate('template-code');

// Update a guild template
$guild->updateTemplate('template-code', [
    'name' => 'Updated Template',
    'description' => 'Updated description'
]);

// Delete a guild template
$guild->deleteTemplate('template-code');
```

### Scheduled Events (via Fluent Interface)

```php
// Get guild scheduled events
$events = $guild->scheduledEvents();

// Create a guild scheduled event
$event = $guild->createScheduledEvent([
    'name' => 'Community Meeting',
    'description' => 'Monthly community meeting',
    'scheduled_start_time' => '2023-12-25T20:00:00.000Z',
    'entity_type' => 2, // Voice channel
    'channel_id' => 'channel-id'
], 'New community event');

// Get a specific scheduled event
$event = $guild->scheduledEvent('event-id');

// Update a scheduled event
$guild->updateScheduledEvent('event-id', [
    'name' => 'Updated Event Name'
], 'Event renamed');

// Delete a scheduled event
$guild->deleteScheduledEvent('event-id', 'Event cancelled');

// Get scheduled event users
$users = $guild->scheduledEventUsers('event-id');
```

### Stickers (via Fluent Interface)

```php
// Get guild sticker packs
$stickers = $guild->stickers();

// Get a specific sticker
$sticker = $guild->sticker('sticker-id');

// Create a guild sticker
$sticker = $guild->createSticker([
    'name' => 'custom_sticker',
    'description' => 'A custom sticker',
    'tags' => 'custom,sticker',
    'file' => 'sticker_file_data'
], 'New custom sticker');

// Update a guild sticker
$guild->updateSticker('sticker-id', [
    'name' => 'updated_sticker'
], 'Sticker renamed');

// Delete a guild sticker
$guild->deleteSticker('sticker-id', 'Sticker removed');
```

### Guild Pruning (via Fluent Interface)

```php
// Get guild prune count
$pruneCount = $guild->pruneCount([
    'days' => 7
]);

// Begin guild prune
$pruneResult = $guild->beginPrune([
    'days' => 7,
    'compute_prune_count' => true
], 'Pruning inactive members');
```

### Voice States (via Fluent Interface)

```php
// Get guild voice states
$voiceStates = $guild->voiceStates();

// Get a specific voice state
$voiceState = $guild->voiceState('user-id');

// Update user voice state
$guild->updateUserVoiceState('user-id', [
    'channel_id' => 'voice-channel-id',
    'suppress' => false
], 'Moving user to different voice channel');
```

### Application Commands (via Fluent Interface)

```php
// Get guild application commands
$commands = $guild->applicationCommands('application-id');

// Create a guild application command
$command = $guild->createApplicationCommand('application-id', [
    'name' => 'ping',
    'description' => 'Ping command',
    'type' => 1
]);

// Get a specific application command
$command = $guild->applicationCommand('application-id', 'command-id');

// Update an application command
$guild->updateApplicationCommand('application-id', 'command-id', [
    'description' => 'Updated ping command'
]);

// Delete an application command
$guild->deleteApplicationCommand('application-id', 'command-id');

// Bulk overwrite application commands
$guild->bulkOverwriteApplicationCommands('application-id', [
    [
        'name' => 'ping',
        'description' => 'Ping command',
        'type' => 1
    ],
    [
        'name' => 'echo',
        'description' => 'Echo command',
        'type' => 1
    ]
]);

// Get application command permissions
$permissions = $guild->applicationCommandPermissions('application-id');

// Get specific command permissions
$permissions = $guild->applicationCommandPermission('application-id', 'command-id');

// Edit application command permissions
$guild->editApplicationCommandPermissions('application-id', 'command-id', [
    'permissions' => [
        [
            'id' => 'role-id',
            'type' => 1,
            'permission' => true
        ]
    ]
]);

// Batch edit application command permissions
$guild->batchEditApplicationCommandPermissions('application-id', [
    [
        'id' => 'command-id',
        'permissions' => [
            [
                'id' => 'role-id',
                'type' => 1,
                'permission' => true
            ]
        ]
    ]
]);
```

## Audit Log Reasons

Many Discord API endpoints support audit log reasons via the `X-Audit-Log-Reason` header. This allows you to provide a reason for administrative actions that will appear in Discord's audit logs.

### Supported Endpoints

The following methods support audit log reasons:

-   **Role Management**: `createRole()`, `updateRole()`, `deleteRole()`
-   **Member Role Management**: `addMemberRole()`, `removeMemberRole()`
-   **Member Management**: `banMember()`, `unbanMember()`, `kickMember()`, `updateMember()`
-   **Channel Management**: `createChannel()`, `updateChannel()`, `deleteChannel()`
-   **Message Management**: `deleteMessage()`
-   **Webhook Management**: `updateWebhook()`, `deleteWebhook()`, `updateWebhookWithToken()`, `deleteWebhookWithToken()`

### Usage

Audit log reasons are passed as the last parameter to supported methods:

```php
// Delete a message with reason
DiscordApi::channel('channel-id')->message('message-id')->delete('Message violated community guidelines');

// Ban a member with reason (using fluent interface)
$guild = DiscordApi::guild();
$guild->banMember('user-id', [
    'delete_message_days' => 7
], 'Repeated violations of server rules');

// Create a role with reason (using fluent interface)
$guild->createRole([
    'name' => 'Moderator',
    'color' => 16776960
], 'New moderator role for server management');
```

### Requirements

-   **Length**: 1-512 characters (automatically truncated if longer)
-   **Encoding**: Automatically URL-encoded by the library
-   **Format**: UTF-8 characters

### Examples

```php
// Simple reason
DiscordApi::channel('channel-id')->message('message-id')->delete('Spam');

// Detailed reason
$guild = DiscordApi::guild();
$guild->banMember('user-id', [], 'Repeated harassment and violation of community guidelines');

// Reason with special characters
$guild->createRole(['name' => 'Event Organizer'], 'Role for managing events & announcements!');

// Long reason (will be truncated to 512 characters)
$longReason = 'This is a very long reason that explains in detail why this action was taken...';
$guild->kickMember('user-id', $longReason);
```

### Best Practices

When writing audit log reasons, consider the following:

1. **Be Clear and Concise**: Explain the action and why it was taken
2. **Be Professional**: Use appropriate language for server logs
3. **Include Context**: Mention relevant rules, policies, or circumstances
4. **Be Specific**: Avoid vague reasons like "rule violation"
5. **Consider Length**: Keep reasons under 200 characters for readability

**Good Examples:**

```php
'Spam in #general channel'
'Repeated harassment of other members'
'Promoted for excellent community contributions'
'Role created for event management team'
'Channel renamed for better organization'
```

**Poor Examples:**

```php
'Bad behavior' // Too vague
'Rule violation' // Not specific enough
'Because I said so' // Unprofessional
'This is a very long reason that goes into unnecessary detail about every single thing that happened...' // Too long
```

### Generic HTTP Methods

For endpoints not covered by the fluent interface, you can use the generic HTTP methods:

```php
// GET request
$response = DiscordApi::get('custom-endpoint', ['param' => 'value']);

// POST request
$response = DiscordApi::post('custom-endpoint', ['data' => 'value']);

// PUT request
$response = DiscordApi::put('custom-endpoint', ['data' => 'value']);

// PATCH request
$response = DiscordApi::patch('custom-endpoint', ['data' => 'value']);

// DELETE request
$response = DiscordApi::delete('custom-endpoint');
```

## Error Handling

The library automatically handles common error scenarios:

### Rate Limiting

The library automatically detects rate limits (HTTP 429) and retries the request after the specified delay.

### Connection Errors

Connection errors are automatically retried up to the configured number of attempts.

### Client Errors

Client errors (4xx status codes) are not retried, except for rate limits (429).

### Example Error Handling

```php
try {
    $user = DiscordApi::user('invalid-user-id')->get();
} catch (RequestException $e) {
    if ($e->response->status() === 404) {
        // User not found
        Log::warning('User not found: invalid-user-id');
    } elseif ($e->response->status() === 401) {
        // Unauthorized - check bot token
        Log::error('Discord API unauthorized - check bot token');
    } else {
        // Other client error
        Log::error('Discord API client error: ' . $e->getMessage());
    }
} catch (ConnectionException $e) {
    // Network/connection error
    Log::error('Discord API connection error: ' . $e->getMessage());
}
```

## Rate Limit Monitoring

The library automatically logs warnings when approaching rate limits:

```php
// The library will log a warning when remaining requests < 10%
Log::warning('Discord API rate limit approaching', [
    'remaining' => 3,
    'limit' => 50,
    'reset' => 1640995200
]);
```

### Cache-Based Rate Limit Tracking

The library includes a cache-based rate limit tracking system that's safe for Laravel Octane and can be shared across multiple server instances:

```php
// Get rate limit status for all tracked buckets
$status = DiscordApi::getRateLimitStatus();

// Example output:
[
    'guilds/123/roles' => [
        'remaining' => 45,
        'limit' => 50,
        'percentage' => 90.0,
        'reset_time' => '2023-12-25 20:00:00',
        'time_until_reset' => 300,
        'last_updated' => '2023-12-25 19:55:00',
        'is_critical' => false,
        'is_low' => false,
        'global' => false,
    ],
    'channels/456/messages' => [
        'remaining' => 2,
        'limit' => 5,
        'percentage' => 40.0,
        'reset_time' => '2023-12-25 20:00:00',
        'time_until_reset' => 180,
        'last_updated' => '2023-12-25 19:57:00',
        'is_critical' => true,
        'is_low' => true,
        'global' => false,
    ]
]

// Get rate limit status for a specific bucket
$bucketStatus = DiscordApi::getBucketRateLimitStatus('guilds/123/roles');

// Clear all rate limit tracking data
DiscordApi::clearRateLimitTracking();
```

### Rate Limit Tracking Features

-   **Octane-Safe**: Uses Laravel's cache system instead of static properties
-   **Cluster-Safe**: Can be shared across multiple server instances
-   **Automatic Cleanup**: Cache entries expire based on Discord's reset times
-   **Bucket-Specific**: Tracks rate limits per Discord API endpoint bucket
-   **Global Rate Limit Support**: Distinguishes between global and route-specific rate limits

### Rate Limit Headers Handled

The library properly handles all Discord rate limit headers:

-   `X-RateLimit-Limit`: Maximum requests per window
-   `X-RateLimit-Remaining`: Remaining requests in current window
-   `X-RateLimit-Reset`: Timestamp when the rate limit resets
-   `X-RateLimit-Bucket`: Unique identifier for the rate limit bucket
-   `X-RateLimit-Global`: Whether this is a global rate limit
-   `Retry-After`: Seconds to wait before retrying (global rate limits)

### Smart Retry Logic

The library implements intelligent retry logic based on Discord's rate limit documentation:

-   **Global Rate Limits**: Uses `Retry-After` header for delay calculation
-   **Route-Specific Rate Limits**: Uses `X-RateLimit-Reset` timestamp
-   **Automatic Backoff**: Adds buffer time to ensure rate limits are respected
-   **Selective Retry**: Only retries on rate limits (429) and connection errors

## Testing

The library includes comprehensive test coverage. You can run the tests with:

```bash
# Run unit tests
php artisan test tests/Unit/Services/DiscordApiTest.php

# Run feature tests
php artisan test tests/Feature/DiscordApiFeatureTest.php
```

### Testing with HTTP Fakes

The library works seamlessly with Laravel's HTTP fakes for testing:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'discord.com/api/v10/users/123*' => Http::response([
        'id' => '123',
        'username' => 'testuser'
    ], 200),
]);

$user = DiscordApi::user('123')->get();
$this->assertEquals('testuser', $user->json('username'));
```

## Utility Methods

```php
// Check if bot token is configured
if (DiscordApi::hasBotToken()) {
    // Bot token is available
}

// Get the configured base URL
$baseUrl = DiscordApi::getBaseUrl();

// Check if global guild ID is configured
if (DiscordApi::hasGlobalGuildId()) {
    $globalGuildId = DiscordApi::getGlobalGuildId();
    // Use global guild ID functionality
}
```

## Best Practices

### 1. Error Handling

Always wrap Discord API calls in try-catch blocks:

```php
try {
    $response = DiscordApi::channel('channel-id')->send($messageData);
    return $response->json();
} catch (RequestException $e) {
    Log::error('Failed to send Discord message', [
        'channel_id' => $channelId,
        'error' => $e->getMessage(),
        'status' => $e->response->status()
    ]);
    throw $e;
}
```

### 2. Rate Limit Awareness

Monitor rate limit headers in your application:

```php
$response = DiscordApi::user('user-id')->get();
$remaining = $response->header('X-RateLimit-Remaining');

if ((int) $remaining < 10) {
    Log::warning('Discord API rate limit low', ['remaining' => $remaining]);
}
```

### 3. Configuration Validation

Validate your configuration on application startup:

```php
if (!DiscordApi::hasBotToken()) {
    Log::warning('Discord bot token not configured');
}
```

### 4. Response Validation

Always validate API responses:

```php
$response = DiscordApi::user('user-id')->get();

if ($response->successful()) {
    $userData = $response->json();
    // Process user data
} else {
    Log::error('Failed to get user', [
        'status' => $response->status(),
        'body' => $response->body()
    ]);
}
```

## Troubleshooting

### Common Issues

1. **401 Unauthorized**: Check that your bot token is correct and has the necessary permissions
2. **403 Forbidden**: Ensure your bot has the required permissions for the operation
3. **404 Not Found**: Verify that the resource ID (user, channel, guild) exists
4. **429 Rate Limited**: The library handles this automatically, but you may need to reduce request frequency

### Debug Mode

Enable debug logging to see detailed request/response information:

```php
// In your .env file
LOG_LEVEL=debug
```

### Checking Bot Permissions

Use the `me()` method to verify your bot token is working:

```php
try {
    $bot = DiscordApi::user()->me();
    Log::info('Bot authenticated successfully', [
        'username' => $bot->json('username'),
        'id' => $bot->json('id')
    ]);
} catch (RequestException $e) {
    Log::error('Bot authentication failed', [
        'status' => $e->response->status(),
        'error' => $e->getMessage()
    ]);
}
```

## Contributing

When contributing to the Discord API library:

1. Follow Laravel coding standards
2. Add tests for new functionality
3. Update documentation for new methods
4. Ensure backward compatibility
5. Test with real Discord API endpoints

## License

This library is part of the Goonhub project and follows the same license terms.
