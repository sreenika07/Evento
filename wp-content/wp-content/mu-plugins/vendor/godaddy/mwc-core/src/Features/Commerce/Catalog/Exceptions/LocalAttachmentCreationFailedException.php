<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\Exceptions;

use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Catalog\Services\InsertMissingAttachmentsService;

/**
 * Exception thrown when we fail to insert a local attachment record for a remote asset.
 * {@see InsertMissingAttachmentsService}.
 */
class LocalAttachmentCreationFailedException extends BaseException
{
}
