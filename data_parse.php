<?php

function check_json_array($filename, $id)
{
    if (!file_exists($filename)) {
        $json_data = [];
        $json_data[$id] = [];
        file_put_contents(
            $filename,
            json_encode($json_data)
        );
    }
}

function is_supergroup($update, $MadelineProto)
{
    if ($update['update']['message']['to_id']['_'] == 'peerChannel') {
        return true;
    } else {
        return false;
    }
}

function is_peeruser($update, $MadelineProto)
{
    if ($update['update']['message']['to_id']['_'] == "peerUser") {
        return true;
    } else {
        return false;
    }
}

function parse_chat_data($update, $MadelineProto)
{
    if (is_supergroup($update, $MadelineProto)) {
        $info = cache_get_chat_info(
            $update,
            $MadelineProto,
            -100 . $update['update']['message']['to_id']['channel_id']
        );
        $peer = $info['id'];
        $title = $info['title'];
        $ch_id = $info['id'];
        $chat_array = array(
            'peer' => $peer,
            'title' => $title,
            'id' => $ch_id);
        return($chat_array);
    }
}