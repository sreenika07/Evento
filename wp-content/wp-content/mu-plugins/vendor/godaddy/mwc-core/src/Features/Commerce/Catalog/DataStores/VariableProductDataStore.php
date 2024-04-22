<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\DataStores;

use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\DataStores\Contracts\CommerceProductDataStoreContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\DataStores\Traits\HasProductPlatformDataStoreCrudTrait;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\Helpers\MapAssetsHelper;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\Services\BatchListProductsByLocalIdService;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\Services\Contracts\ProductsServiceContract;
use WC_Product_Variable_Data_Store_CPT;

/**
 * Commerce Catalog products data store for variable products (products that contain variants).
 *
 * A WooCommerce data store for variable products to replace the default data store to enable read and write operations with the Commerce API.
 */
class VariableProductDataStore extends WC_Product_Variable_Data_Store_CPT implements CommerceProductDataStoreContract
{
    use HasProductPlatformDataStoreCrudTrait;

    protected BatchListProductsByLocalIdService $batchListProductsByLocalIdService;

    /**
     * Constructs the data store.
     *
     * @param ProductsServiceContract $productsService
     * @param MapAssetsHelper $mapAssetsHelper
     * @param BatchListProductsByLocalIdService $batchListProductsByLocalIdService
     */
    public function __construct(ProductsServiceContract $productsService, MapAssetsHelper $mapAssetsHelper, BatchListProductsByLocalIdService $batchListProductsByLocalIdService)
    {
        $this->productsService = $productsService;
        $this->mapAssetsHelper = $mapAssetsHelper;
        $this->batchListProductsByLocalIdService = $batchListProductsByLocalIdService;
    }

    /**
     * {@inheritDoc}
     */
    public function read_children(&$product, $force_read = false) // @phpstan-ignore-line
    {
        $children = parent::read_children($product, $force_read); // @phpstan-ignore-line
        $childrenIds = TypeHelper::arrayOfIntegers(ArrayHelper::get($children, 'all', []));

        if (! empty($childrenIds)) {
            // pre-warm the cache for these products
            $this->batchListProductsByLocalIdService->batchListByLocalIds($childrenIds);
        }

        return $children;
    }
}
