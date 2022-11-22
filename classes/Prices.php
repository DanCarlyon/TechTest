<?php

spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});

class Prices
{

    /**
     * @var Database
     */
    private Database $connection;

    /**
     * @var array
     */
    const BASE_PRICES = [
        "A" => 5,
        "B" => 10
    ];

    /**
     * @var array
     */
    const SALE_PRICE = [
        "A" => 5,
        "B" => 6
    ];

    /**
     * @var array
     */
    private array $customerPriceBook;

    /**
     * @var Customer
     */
    private Customer $customer;

    /**
     * @var array
     */
    private array $priceBook;

    /**
     * @var Pallets
     */
    private Pallets $pallets;

    /**
     * This class was originally designed to handle just pricing
     * @param Database $db
     * @param Customer $customer
     */
    public function __construct(
        Database $db,
        Customer $customer
    ) {
        $this->connection = $db;
        $this->customer = $customer;
        $this->pallets = new Pallets($this->connection);
    }

    /**
     * I'm not really happy with this method and how big it is
     * There's probably room to condense and extract code here
     * @param $scenario
     * @return array
     */
    public function getTotalForOrder($scenario): array
    {
        $this->priceBook = $this->customer->getCustomerPriceBook($scenario['customer']);
        $this->pallets->setCustomerPriceBook($this->priceBook);

        foreach ($scenario['products'] as $sku => $qty) {
            $loadedProductData = new Product($this->connection, $scenario['customer'], $sku);
            $scenario['products'][$sku] = [
                "qty" => $qty,
                "base_price" => $this->getBasePriceForProduct($sku),
                "sale_price" => $this->getSalePriceForProduct($sku),
                "price_options" => $loadedProductData->getProductData(),
            ];
        }

        if ($this->doesRequirePallet($scenario)) {
            $scenario = $this->pallets->attemptToFillPallets($scenario);
        } else {
            $scenario = $this->generatePricing($scenario);
        }

        $totals = [
            "grand_total" => 0
        ];
        foreach ($scenario['products'] as $sku => $data) {
            $totals[$sku] = 0;
            if ($this->doesRequirePallet($scenario)) {
                if ($scenario['pallet_data']['products_on_pallet'] >= $data['qty']) {
                    $totals['grand_total'] = $totals['grand_total'] + ($data['qty'] * $data['price_options'][0]['price']);
                    $totals[$sku] = $totals[$sku] + ($data['qty'] * $data['price_options'][0]['price']);
                    $scenario['pallet_data']['products_on_pallet'] = $scenario['pallet_data']['products_on_pallet'] - $data['qty'];
                } elseif ($scenario['pallet_data']['products_on_pallet'] > 0) {
                    $palletPricedSkus = $data['qty'] - $scenario['pallet_data']['left_over_products'];
                    $totals['grand_total'] = $totals['grand_total'] + ($palletPricedSkus * $data['price_options'][0]['price']);
                    $totals['grand_total'] = $totals['grand_total'] + ($scenario['pallet_data']['left_over_products'] * $data['base_price']);

                    $totals[$sku] = $totals[$sku] + ($palletPricedSkus * $data['price_options'][0]['price']);
                    if (isset($scenario['sale']) && $scenario['sale']) {
                        $totals[$sku] = $totals[$sku] + ($scenario['pallet_data']['left_over_products'] * $data['sale_price']);
                    } else {
                        $totals[$sku] = $totals[$sku] + ($scenario['pallet_data']['left_over_products'] * $data['base_price']);
                    }
                }
            } else {
                if (count($data['price_options']) === 0) {
                    if (isset($scenario['sale']) && $scenario['sale']) {
                        $totals['grand_total'] = $totals[$sku] + ($data['qty'] * $data['sale_price']);
                        $totals[$sku] = $totals[$sku] + ($data['qty'] * $data['sale_price']);
                    } else {
                        $totals['grand_total'] = $totals[$sku] + ($data['qty'] * $data['base_price']);
                        $totals[$sku] = $totals[$sku] + ($data['qty'] * $data['base_price']);
                    }
                } else {
                    $totals['grand_total'] = $totals[$sku] + ($data['qty'] * $data['price_options'][0]['price']);
                    $totals[$sku] = $totals[$sku] + ($data['qty'] * $data['price_options'][0]['price']);
                }
            }
        }
        return $totals;
    }

    /**
     * @param $scenario
     * @return mixed
     */
    public function generatePricing($scenario)
    {
        foreach($scenario['products'] as $sku => $data) {
            foreach($data['price_options'] as $key => $option) {
                if ($option['min_qty'] > $data['qty']) {
                    unset($scenario['products'][$sku]['price_options'][$key]);
                }
            }
        }

        return $scenario;
    }

    /**
     * @param $sku
     * @return int
     */
    public function getBasePriceForProduct($sku): int
    {
        return self::BASE_PRICES[$sku];
    }

    /**
     * @param $sku
     * @return int
     */
    public function getSalePriceForProduct($sku): int
    {
        return self::SALE_PRICE[$sku];
    }

    /**
     * @param $scenario
     * @return bool
     */
    public function doesRequirePallet($scenario): bool
    {
        $totalProductQty = 0;
        foreach($scenario['products'] as $sku => $data) {
            $totalProductQty = $totalProductQty + $data['qty'];
        }

        foreach($scenario['products'] as $sku => $data) {
            foreach($data['price_options'] as $key => $options) {
                if ($totalProductQty > $options['min_qty']) {
                    // Qualifies for min_qty $options['min_qty'] so can get price if no pallet_qty
                    if ($options['pallet_qty'] !== null && $totalProductQty >= $options['pallet_qty']) {
                        // Qualifies for pallet_qty so can get price and should be on a pallet
                        return true;
                    }
                    return false;
                }
            }
        }
        return false;
    }

}
