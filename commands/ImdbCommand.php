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

namespace Asuka\Commands;

use Imdb\TitleSearch;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ImdbCommand extends Command
{
    protected $name = "imdb";
    protected $description = "Returns the first IMDb result for a set of search terms.";

    public function handle($arguments)
    {
        if (empty($arguments)) {
            $badArgsResponse = 'Please supply some search terms.' . PHP_EOL;
            $badArgsResponse .= PHP_EOL;
            $badArgsResponse .= 'Example: /imdb Hitchhikers Guide to the Galaxy';
            $this->reply($badArgsResponse);

            return;
        }

        $search = new TitleSearch();
        $results = $search->search($arguments, [TitleSearch::MOVIE, TitleSearch::TV_SERIES]);

        $result = $results[0];
        if (is_null($result)) {
            $this->reply('No results found!');

            return;
        }

        $response = sprintf("URL: %s" . PHP_EOL, $result->main_url());
        $response .= sprintf("Title: %s" . PHP_EOL, $result->title());
        $response .= sprintf("Year: %s" . PHP_EOL, $result->year());
        $response .= sprintf("Rating: %s/10" . PHP_EOL, $result->rating());
        $response .= PHP_EOL . trim($result->plotoutline(true));
        $this->reply($response);
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
