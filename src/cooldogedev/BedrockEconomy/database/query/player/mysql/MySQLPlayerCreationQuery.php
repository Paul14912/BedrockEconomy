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

namespace cooldogedev\BedrockEconomy\database\query\player\mysql;

use cooldogedev\libSQL\query\MySQLQuery;
use mysqli;

final class MySQLPlayerCreationQuery extends MySQLQuery
{
    public function __construct(
        protected string $searchValue,
        protected string $playerName,
        protected int    $balance
    )
    {
        parent::__construct();
    }

    public function handleIncomingConnection(mysqli $connection): bool
    {
        $xuid = $this->getXuid();
        $username = $this->getPlayerName();
        $balance = $this->getBalance();
        $statement = $connection->prepare($this->getQuery());
        $statement->bind_param("sss", $xuid, $username, $balance);
        $statement->execute();
        $statement->close();
        return true;
    }

    public function getXuid(): string
    {
        return $this->searchValue;
    }

    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getQuery(): string
    {
        return "INSERT IGNORE INTO " . $this->getTable() . " (xuid, username, balance) VALUES (?, ?, ?)";
    }
}
