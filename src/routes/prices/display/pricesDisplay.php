<?php

namespace Prices;

use PDO;

require "model/pricesDisplayModel.php";

class pricesDisplay
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get_prices(): array
    {
        return (new pricesDisplayModel($this->pdo))->get_prices();
    }
}