<?php

class Pallets
{
    /**
     * @var Database
     */
    private $connection;

    /**
     * Ended up being unused due to confusion however a class to handle functionality specific to pallets
     * Keeping to the single responsibility from SOLID
     * @param Database $db
     */
    public function __construct(
        Database $db
    ) {
        $this->connection = $db;
    }

    public function isPalletFull($customerId, $sku, $quantity)
    {
        //return ($quantity === $palletQuantity);
    }
}