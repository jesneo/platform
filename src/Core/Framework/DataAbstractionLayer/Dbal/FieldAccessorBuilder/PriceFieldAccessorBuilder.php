<?php declare(strict_types=1);

namespace Shopware\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;

class PriceFieldAccessorBuilder implements FieldAccessorBuilderInterface
{
    public function buildAccessor(string $root, Field $field, Context $context, string $accessor): ?string
    {
        if (!$field instanceof PriceField) {
            return null;
        }

        return sprintf('(CAST(JSON_UNQUOTE(JSON_EXTRACT(`%s`.`%s`, "$.gross")) AS DECIMAL))', $root, $field->getStorageName());
    }
}