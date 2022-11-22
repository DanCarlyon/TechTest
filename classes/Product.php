<?php


class Product
{
    /**
     * @var Database
     */
    private $connection;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var array
     */
    private $productData;

    /**
     * @param Database $db
     * @param int $customerId
     * @param string $sku
     */
    public function __construct(
        Database $db,
        int $customerId,
        string $sku
    ) {
        $this->connection = $db;
        $this->customerId = $customerId;
        $this->sku = $sku;

        $this->getProductInformation();
    }

    /**
     * @return void
     */
    private function getProductInformation()
    {
        $this->productData = $this->connection->query(
            'SELECT * FROM customer_price WHERE customer_id = ? AND sku = ?',
            $this->customerId,
            $this->sku
        )->fetchAll();
    }

    /**
     * @return array
     */
    public function getProductData()
    {
        return $this->productData;
    }
}