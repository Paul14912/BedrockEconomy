<?php

/**
 *  Copyright (c) 2021 cooldogedev
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

declare(strict_types=1);

namespace cooldogedev\BedrockEconomy\command;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\constant\SearchConstants;
use cooldogedev\BedrockEconomy\constant\TableConstants;
use cooldogedev\BedrockEconomy\language\KnownTranslations;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\language\TranslationKeys;
use cooldogedev\BedrockEconomy\permission\BedrockEconomyPermissions;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use Exception;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

final class BalanceCommand extends BaseCommand
{
    protected const ARGUMENT_PLAYER = "player";

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $player = $args[BalanceCommand::ARGUMENT_PLAYER] ?? null;

        if (!$sender instanceof Player && !$player) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $session = $this->getOwningPlugin()->getAccountManager()->getAccount($player ?? $sender->getName(), SearchConstants::SEARCH_MODE_USERNAME);
        $isSelf = $player === null;

        if ($isSelf && !$session) {
            $sender->sendMessage(LanguageManager::getTranslation(KnownTranslations::NO_ACCOUNT));
            return;
        }

        $this->getOwningPlugin()->getDatabaseManager()->getConnector()->submitQuery(
            $this->getOwningPlugin()->getDatabaseManager()->getQueryManager()->getPlayerRetrievalQuery(
                $player ?? $sender->getName(),
                SearchConstants::SEARCH_MODE_USERNAME
            ),
            TableConstants::DATA_TABLE_PLAYERS,
            onSuccess: function (?array $data) use ($sender, $player, $isSelf): void {

                if (!$data) {
                    $sender->sendMessage(LanguageManager::getTranslation($isSelf ? KnownTranslations::NO_ACCOUNT : KnownTranslations::PLAYER_NOT_FOUND, [
                            TranslationKeys::PLAYER => $player
                        ]
                    ));
                    return;
                }

                $sender->sendMessage(LanguageManager::getTranslation($player ? KnownTranslations::BALANCE_INFO_OTHER : KnownTranslations::BALANCE_INFO, [
                        TranslationKeys::PLAYER => $player,
                        TranslationKeys::AMOUNT => $data["balance"],
                        TranslationKeys::CURRENCY_NAME => $this->getOwningPlugin()->getCurrencyManager()->getName(),
                        TranslationKeys::CURRENCY_SYMBOL => $this->getOwningPlugin()->getCurrencyManager()->getSymbol()
                    ]
                ));
            }
        );
    }

    /**
     * @return BedrockEconomy
     */
    public function getOwningPlugin(): Plugin
    {
        return parent::getOwningPlugin();
    }

    protected function prepare(): void
    {
        $this->setPermission(BedrockEconomyPermissions::COMMAND_BALANCE_PERMISSION);
        try {
            $this->registerArgument(0, new RawStringArgument(BalanceCommand::ARGUMENT_PLAYER, true));
        } catch (Exception) {
        }
    }
}
