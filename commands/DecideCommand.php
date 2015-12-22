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

use Telegram\Bot\Commands\Command;

class DecideCommand extends Command
{
    protected $name = "decide";
    protected $description = "Decides between a set of choices.";

    protected $choiceDelimiters = [
        ' or ', '|', ',', '/', '\\'
    ];

    protected $singleChoiceResults = [
        'No.', 'Probably not.', 'Maybe.',
        'Probably.', 'Undecided, ask me again later.', 'Yes.'
    ];

    public function handle($arguments)
    {
        $badArgsResponse = 'Please supply at least 1 choice.' . PHP_EOL;
        $badArgsResponse .= PHP_EOL;
        $badArgsResponse .= 'Example: /decide Eat cookies?' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Cookies | Cake' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Cookies or Cake' . PHP_EOL;
        $badArgsResponse .= 'Example: /decide Cookies, Cake, Pie';

        if (empty($arguments)) {
            $this->reply($badArgsResponse);

            return;
        }

        // Look for any delimiter from $choiceDelimiters and use that as the delimiter for the rest of string,
        // kind of like sed.
        $choiceDelimiter = null;
        foreach ($this->choiceDelimiters as $delimiter) {
            if (str_contains($arguments, $delimiter)) {
                $choiceDelimiter = $delimiter;
                break;
            }
        }

        $singleChoiceResponse = $this->singleChoiceResults[array_rand($this->singleChoiceResults)];

        // No delimiters found in string, assume it's a single choice message.
        if (is_null($choiceDelimiter)) {
            $this->reply($singleChoiceResponse);

            return;
        }

        // Remove the delimiters from the string and then check if it's empty.
        // This should indicate whether or not the string is purely comprised of delimiters and nothing else.
        if (empty(trim(str_replace($choiceDelimiter, '', $arguments)))) {
            $this->reply($badArgsResponse);

            return;
        }

        // Run trim() on all choices and then remove any empty elements from the resulting array.
        // Handles input like "| choice1 || choice2" correctly.
        $choices = array_filter(array_map('trim', explode($choiceDelimiter, $arguments)));
        if (count($choices) < 2) {
            $this->reply($singleChoiceResponse);

            return;
        }

        $this->reply($choices[array_rand($choices)]);
    }

    private function reply($response)
    {
        $this->replyWithMessage($response, true, $this->getUpdate()->getMessage()->getMessageId(), null);
    }
}
