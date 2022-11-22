<?php

spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});

class Pallets
{
    /**
     * @var Database
     */
    private Database $connection;

    /**
     * @var array
     */
    private array $customerPriceBook;

    /**
     * @var int
     */
    private int $palletQty;

    /**
     * @param Database $db
     */
    public function __construct(
        Database $db
    ) {
        $this->connection = $db;
    }

    /**
     * @return int
     */
    public function getRequiredPalletQty()
    {
        return $this->palletQty;
    }

    /**
     * @param $qty
     * @return void
     */
    public function setRequiredPalletQty($qty)
    {
        $this->palletQty = $qty;
    }

    /**
     * @param $customerData
     * @return void
     */
    public function setCustomerPriceBook($customerData)
    {
        $this->customerPriceBook = $customerData;
    }

    /**
     * @param $scenario
     * @return mixed
     */
    public function attemptToFillPallets($scenario)
    {
        if ($this->isMixedSkus($scenario)) {
            $mixedPalletAvailable = $this->doPriceOptionsAllowMixedPallets($scenario);
            $this->setRequiredPalletQty($this->findMaxPalletSize($scenario));
            $scenario = $this->removeOptions($scenario, $mixedPalletAvailable);
            $scenario = $this->sortSkusBasedOnPrice($scenario);
        }
        $scenario['pallet_data'] = $this->fillPallet($scenario);
        return $scenario;
    }

    /**
     * @param $scenario
     * @return bool
     */
    public function isMixedSkus($scenario): bool
    {
        if (count($scenario['products']) > 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $scenario
     * @return bool
     */
    public function doPriceOptionsAllowMixedPallets($scenario): bool
    {
        $allowMixedPallet = false;
        foreach($scenario['products'] as $sku => $data) {
            foreach($data['price_options'] as $key => $option) {
                if ($option['mixed_pallet'] === 1) {
                    $allowMixedPallet = true;
                }
            }
        }
        return $allowMixedPallet;
    }

    /**
     * @param $scenario
     * @return int
     */
    public function findMaxPalletSize($scenario): int
    {
        $palletQtyRequired = 0;
        foreach($scenario['products'] as $sku => $data) {
            $loadedProductData = new Product($this->connection, $scenario['customer'], $sku);
            $productData = $loadedProductData->getProductData();
            foreach($productData as $key => $option) {
                if ($option['pallet_qty'] !== null && $option['pallet_qty'] !== '') {
                    if ($option['pallet_qty'] > $palletQtyRequired) {
                        $palletQtyRequired = $option['pallet_qty'];
                    }
                }
            }
        }
        return $palletQtyRequired;
    }

    /**
     * @param $scenario
     * @param $mixedPalletAvailable
     * @return array
     */
    public function removeOptions($scenario, $mixedPalletAvailable): array
    {
        foreach($scenario['products'] as $sku => $data) {
            foreach($data['price_options'] as $key => $option) {
                if ($option['mixed_pallet'] === 0 && $mixedPalletAvailable) {
                    unset($scenario['products'][$sku]['price_options'][$key]);
                }
            }
        }
        return $scenario;
    }

    /**
     * Sort Skus, so we can fill the pallet with the most expensive item first
     * @param $scenario
     * @return mixed
     */
    public function sortSkusBasedOnPrice($scenario)
    {
        uasort($scenario['products'], function($left, $right){ return (int)$left['price_options'][0]['price'] <=> (int)$right['price_options'][0]['price']; });
        $scenario['products'] = array_reverse($scenario['products'], true);
        return $scenario;
    }

    /**
     * Fill the pallet and work out how many items spill over to non-pallet cost
     * @param $scenario
     * @return array
     */
    public function fillPallet($scenario): array
    {
        $totalProductQty = 0;
        foreach($scenario['products'] as $sku => $data) {
            $totalProductQty = $totalProductQty + $data['qty'];
        }
        return [
            "pallets" => floor($totalProductQty/$this->palletQty),
            "products_on_pallet" => $totalProductQty-($totalProductQty%$this->palletQty),
            "left_over_products" => $totalProductQty%$this->palletQty
        ];
    }

}