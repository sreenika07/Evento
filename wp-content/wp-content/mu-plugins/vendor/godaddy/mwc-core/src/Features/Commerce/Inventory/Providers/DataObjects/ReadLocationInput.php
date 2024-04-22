<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Inventory\Providers\DataObjects;

use GoDaddy\WordPress\MWC\Core\Features\Commerce\Providers\DataObjects\AbstractDataObject;

class ReadLocationInput extends AbstractDataObject
{
    public string $storeId;
    public string $locationId;

    /**
     * Creates a new data object.
     *
     * @param array{
     *     storeId: string,
     *     locationId: string,
     * } $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
