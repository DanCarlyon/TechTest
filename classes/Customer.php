<?php

class Customer
{
    /**
     * @var Database
     */
    private $connection;

    /**
     * This class was originally designed to handle just pricing
     * @param Database $db
     */
    public function __construct(
        Database $db
    ) {
        $this->connection = $db;
    }

    /**
     * @param int $customerId
     * @return array
     */
    public function getCustomerPriceBook(int $customerId): array
    {
        return $this->connection->query(
            'SELECT * FROM customer_price WHERE customer_id = ?',
            $customerId
        )->fetchAll();
    }

    /**
     * @param int $customerId
     * @param string $sku
     * @return array
     */
    public function getCustomerPriceBookForSku(int $customerId, string $sku): array
    {
        return $this->connection->query(
            'SELECT * FROM customer_price WHERE customer_id = ? AND sku = ?',
            $customerId,
            $sku
        )->fetchAll();
    }

}