<?php

class BaseClass
{

    /**
     * @var Database
     */
    private $connection;

    CONST BASE_PRICE_A = 5;
    CONST BASE_PRICE_B = 10;
    CONST SALE_PRICE_B = 6;

    public function __construct(
        Database $db
    ) {
        $this->connection = $db;
    }

    public function getBasePriceA()
    {
        return self::BASE_PRICE_A;
    }

    public function getBasePriceB()
    {
        return self::BASE_PRICE_B;
    }

    public function getSalePriceB()
    {
        return self::SALE_PRICE_B;
    }

    public function getPricesForOrder($products)
    {

    }
}