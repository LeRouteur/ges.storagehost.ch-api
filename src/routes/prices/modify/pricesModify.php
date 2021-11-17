<?php

namespace Prices;

use PDO;

require_once "model/pricesModifyModel.php";

class pricesModify
{
    private PDO $pdo;
    private array $data;

    public function __construct(PDO $pdo, array $data)
    {
        $this->pdo = $pdo;
        $this->data = $data;
    }

    public function update_prices(): array
    {
        $this->data['category'] = ucwords($this->data['category']);
        return (new pricesModifyModel($this->pdo))->update_prices($this->data);
    }
}