<?php

namespace App\Enums;

enum DiscordSettings: string
{
    case GRANT_ROLE_WHEN_LINKED = 'grant_role_when_linked';

    // 362927443868385280
    case TOMATO_GUILD_ID = 'tomato_guild_id';

    // 898402624716546048
    case TOMATO_SUBSCRIBER_ROLE_ID = 'tomato_subscriber_role_id';
}
