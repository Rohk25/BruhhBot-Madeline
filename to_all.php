<?php
/**
 * Copyright (C) 2016-2017 Hunter Ashton
 * This file is part of BruhhBot.
 * BruhhBot is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * BruhhBot is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with BruhhBot. If not, see <http://www.gnu.org/licenses/>.
 */



function send_to_moderated($MadelineProto, $msg, $except = [])
{
    check_json_array('chatlist.json', false, false);
    $file = file_get_contents('chatlist.json');
    $chatlist = json_decode($file, true);
    foreach ($chatlist as $peer) {
        if (!in_array($peer, $except)) {
            $default = [
                'peer'       => $peer,
                'message'    => $msg,
                'parse_mode' => 'html',
            ];
            try {
                $sentMessage = $MadelineProto->messages->sendMessage(
                $default
            );
                \danog\MadelineProto\Logger::log($sentMessage);
            } catch (Exception $e) {
                continue;
            }
        }
    }
}

function ban_from_moderated($MadelineProto, $userid, $except = [])
{
    check_json_array('chatlist.json', false, false);
    $file = file_get_contents('chatlist.json');
    $chatlist = json_decode($file, true);
    foreach ($chatlist as $peer) {
        if (!in_array($peer, $except)) {
            try {
                $channelBannedRights = ['_' => 'channelBannedRights', 'view_messages' => true, 'until_date' => 999999999];
                $kick = $MadelineProto->
                channels->editBanned(
                    ['channel' => $peer,
                    'user_id' => $userid,
                    'banned_rights' => $channelBannedRights ]
                );
                \danog\MadelineProto\Logger::log($kick);
            } catch (Exception $e) {
                continue;
            }
        }
    }
}

function unban_from_moderated($MadelineProto, $userid, $except = [])
{
    check_json_array('chatlist.json', false, false);
    $file = file_get_contents('chatlist.json');
    $chatlist = json_decode($file, true);
    foreach ($chatlist as $peer) {
        if (!in_array($peer, $except)) {
            try {
                $channelBannedRights = ['_' => 'channelBannedRights', 'view_messages' => false, 'send_messages' => false, 'send_media' => false, 'send_stickers' => false, 'send_gifs' => false, 'send_games' => false, 'send_inline' => false, 'embed_links' => false, 'until_date' => 999999999];
                $kick = $MadelineProto->
                channels->editBanned(
                    ['channel' => $peer,
                    'user_id' => $userid,
                    'banned_rights' => $channelBannedRights ]
                );
                \danog\MadelineProto\Logger::log($kick);
            } catch (Exception $e) {
                continue;
            }
        }
    }
}

function broadcast_to_all($update, $MadelineProto, $msg, $except = [])
{
    if (from_master($update, $MadelineProto)) {
        check_json_array('chatlist.json', false, false);
        $file = file_get_contents('chatlist.json');
        $chatlist = json_decode($file, true);
        foreach ($chatlist as $peer) {
            if (!in_array($peer, $except)) {
                $default = [
                    'peer'       => $peer,
                    'message'    => $msg,
                    'parse_mode' => 'html',
                ];
                try {
                    $sentMessage = $MadelineProto->messages->sendMessage(
                        $default
                    );
                    \danog\MadelineProto\Logger::log($sentMessage);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }
}

function alert_moderators($MadelineProto, $ch_id, $text)
{
    $default = [
        'message'    => $text,
        'parse_mode' => 'html',
    ];
    $users = [];
    $admins = cache_get_info(false, $MadelineProto, $ch_id, true);
    foreach ($admins['participants'] as $key) {
        if (array_key_exists('user', $key)) {
            $id = $key['user']['id'];
        } else {
            if (array_key_exists('bot', $key)) {
                $id = $key['bot']['id'];
            }
        }
        if (array_key_exists('role', $key)) {
            if ($key['role'] == 'moderator'
                or $key['role'] == 'creator'
                or $key['role'] == 'editor'
                or $key['role'] == 'admin'
            ) {
                $mod = true;
            } else {
                $mod = false;
            }
        } else {
            $mod = false;
        }
        if ($mod) {
            $users[] = $id;
        }
    }
    check_json_array('promoted.json', $ch_id);
    $file = file_get_contents('promoted.json');
    $promoted = json_decode($file, true);
    if (isset($promoted[$ch_id])) {
        foreach ($promoted[$ch_id] as $id) {
            if (in_array($id, $users)) {
                continue;
            }
            $users[] = $id;
        }
    }
    foreach ($users as $peer) {
        try {
            if (!alert_check($ch_id, $peer)) {
                continue;
            }
            $default['peer'] = $peer;
            $sentMessage = $MadelineProto->messages->sendMessage(
                    $default
                );
            \danog\MadelineProto\Logger::log($sentMessage);
        } catch (Exception $e) {
            continue;
        }
    }
}

function alert_moderators_forward($MadelineProto, $ch_id, $msg_id)
{
    $users = [];
    $admins = cache_get_info(false, $MadelineProto, $ch_id, true);
    foreach ($admins['participants'] as $key) {
        if (array_key_exists('user', $key)) {
            $id = $key['user']['id'];
        } else {
            if (array_key_exists('bot', $key)) {
                $id = $key['bot']['id'];
            }
        }
        if (array_key_exists('role', $key)) {
            if ($key['role'] == 'moderator'
                or $key['role'] == 'creator'
                or $key['role'] == 'editor'
                or $key['role'] == 'admin'
            ) {
                $mod = true;
            } else {
                $mod = false;
            }
        } else {
            $mod = false;
        }
        if ($mod) {
            $users[] = $id;
        }
    }
    check_json_array('promoted.json', $ch_id);
    $file = file_get_contents('promoted.json');
    $promoted = json_decode($file, true);
    if (isset($promoted[$ch_id])) {
        foreach ($promoted[$ch_id] as $id) {
            if (in_array($id, $users)) {
                continue;
            }
            $users[] = $id;
        }
    }
    foreach ($users as $peer) {
        try {
            if (alert_check($ch_id, $peer)) {
                $forwardMessage = $MadelineProto->messages->forwardMessages([
                    'silent'    => false,
                    'from_peer' => $ch_id,
                    'id'        => [$msg_id],
                    'to_peer'   => $peer, ]
                );
                \danog\MadelineProto\Logger::log($forwardMessage);
            }
        } catch (Exception $e) {
            continue;
        }
    }
}
