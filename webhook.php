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

require 'vendor/autoload.php';

use Asuka\Commands;
use Telegram\Bot\Api;

$config = parse_ini_file('config.ini', true);
$telegramConfig = $config['telegram'];
$apiKey = $telegramConfig['api_key'];
$async = $telegramConfig['async_requests'];

$commands = array_map(function ($s) {
    return 'Asuka\\Commands\\' . str_replace('.php', '', basename($s));
}, glob('commands/*.php'));

$telegram = new Api($apiKey, $async);
$telegram->addCommands($commands);
$telegram->commandsHandler(true);
