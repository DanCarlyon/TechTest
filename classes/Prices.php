<?php

class Prices
{
    /**
     * @var Database
     */
    private $connection;

    const BASE_PRICES = [
        "A" => 5,
        "B" => 10
    ];

    const SALE_PRICE = [
        "A" => 5,
        "B" => 6
    ];

    /**
     * @var array
     */
    private $customerPriceBook;

    /**
     * This class was originally designed to handle just pricing
     * @param Database $db
     */
    public function __construct(
        Database $db
    ) {
        $this->connection = $db;
    }

    public function getTotalForOrder($scenario)
    {
        $totals = ["grandTotal" => 0];
        $skuPrices = [];
        foreach($scenario['products'] as $sku => $qty) {
            $skuData = $this->getSkuDataForCustomerAndQuantity($scenario['customer'], $sku, $qty);
            if (isset($scenario['sale']) && $scenario['sale']) {
                $skuPrices[$sku] = self::SALE_PRICE[$sku];
            } else {
                $skuPrices[$sku] = self::BASE_PRICES[$sku];
            }
            if (count($skuData) > 0) {
                foreach($skuData as $key => $data) {
                    if ($data['price'] < $skuPrices[$sku]) {
                        $skuPrices[$sku] = $data['price'];
                    }
                }
            }
        }

        foreach($skuPrices as $sku => $cheapestPricePerItem) {
            $skuLineTotal = (isset($totals[$sku]) ?? 0) + ($scenario['products'][$sku] * $cheapestPricePerItem);
            $totals[$sku] = $skuLineTotal;
            $totals['grandTotal'] = $totals['grandTotal'] + $skuLineTotal;
        }
        return $totals;
    }

    /**
     * @param int $customerId
     * @param string $sku
     * @param $quantity
     * @return array
     */
    public function getSkuDataForCustomerAndQuantity(int $customerId, string $sku, $quantity): array
    {
        return $this->connection->query(
            'SELECT * FROM customer_price WHERE customer_id = ? AND sku = ? AND (min_qty < ? OR min_qty IS NULL)',
            $customerId,
            $sku,
            $quantity
        )->fetchAll();
    }

}