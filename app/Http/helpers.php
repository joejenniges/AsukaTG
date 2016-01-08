<?php
/*
 * This file is part of AsukaTG.
 *
 * AsukaTG is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AsukaTG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AsukaTG.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Asuka\Http;

use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class Helpers
{
    /**
     * @param $url
     * @param bool $dieOnError
     * @return mixed
     */
    public static function curl_get_contents($url, $dieOnError = true)
    {
        $curlOpts = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'AsukaTG (https://github.com/TheReverend403/AsukaTG)',
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_FAILONERROR    => true,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $curlOpts);
        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = app('telegram')->bot()->getWebhookUpdates()->getMessage();
            self::sendMessage(curl_error($ch), $message->getChat()->getId(), $message->getMessageId());
            if ($dieOnError) {
                curl_close($ch);
                app()->abort(200);
            }
        }

        curl_close($ch);

        return $output;
    }

    public static function sendMessage($response, $chatId, $params = [])
    {
        $params['chat_id'] = $chatId;
        $params['text'] = $response;

        app('telegram')->bot()->sendMessage($params);
    }

    public static function escapeMarkdown($string)
    {
        return $string;
        //return preg_replace('/([*_])/i', '\\\\$1', $string);
    }
}

class AsukaDB
{
    public static function createOrUpdateUser(User $user)
    {
        $db = app('db')->connection();
        $values = [
            'id'         => $user->getId(),
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName() ? $user->getLastName() : null,
            'username'   => $user->getUsername() ? $user->getUsername() : null,
        ];

        if (!$db->table('users')->where('id', $user->getId())->limit(1)->value('id')) {
            $db->table('users')->insert($values);
        } else {
            unset($values['id']);
            $db->table('users')->where('id', $user->getId())->update($values);
        }
    }

    public static function createQuote(Message $message)
    {
        $db = app('db')->connection();
        $quoteSource = $message->getReplyToMessage();
        $messageId = $message->getReplyToMessage()->getMessageId();
        $groupId = $quoteSource->getChat()->getId();
        self::createOrUpdateUser($quoteSource->getFrom());

        $values = [
            'added_by_id'       => $message->getFrom()->getId(),
            'user_id'           => $quoteSource->getFrom()->getId(),
            'group_id'          => $groupId,
            'message_id'        => $messageId,
            'message_timestamp' => $quoteSource->getDate(),
            'content'           => $quoteSource->getText()
        ];

        $existing = $db->table('quotes')->where('message_id', $messageId)->where('group_id', $groupId)->limit(1)->value('id');
        if (!$existing) {
            $quoteId = $db->table('quotes')->insertGetId($values);
            return $quoteId;
        } else {
            Helpers::sendMessage(sprintf('I already have that quote saved as #%s.', $existing), $groupId, ['reply_to_message_id' => $message->getMessageId()]);

            return null;
        }
    }

    public static function getQuote($id = null)
    {
        $db = app('db')->connection();

        if (!$id) {
            return $db->table('quotes')->limit(1)->orderByRaw('RAND()')->first();
        }
        return $db->table('quotes')->where('id', $id)->limit(1)->first();
    }


    public static function getUser($id)
    {
        $db = app('db')->connection();
        return $db->table('users')->where('id', $id)->limit(1)->first();
    }

    public static function createOrUpdateGroup(Chat $group)
    {
        $db = app('db')->connection();
        $values = [
            'id'    => $group->getId(),
            'title' => $group->getTitle(),
        ];

        if (!$db->table('groups')->where('id', $group->getId())->limit(1)->value('id')) {
            $db->table('groups')->insert($values);
        } else {
            self::updateGroup($group);
        }
    }

    public static function updateGroup(Chat $group) {
        $db = app('db')->connection();
        $values = [
            'title' => $group->getTitle(),
        ];

        $db->table('groups')->where('id', $group->getId())->update($values);
    }
}
