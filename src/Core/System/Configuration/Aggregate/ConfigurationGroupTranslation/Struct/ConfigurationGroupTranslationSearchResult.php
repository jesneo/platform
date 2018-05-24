<?php declare(strict_types=1);

namespace Shopware\System\Configuration\Aggregate\ConfigurationGroupTranslation\Struct;

use Shopware\System\Configuration\Aggregate\ConfigurationGroupTranslation\Collection\ConfigurationGroupTranslationBasicCollection;
use Shopware\Framework\ORM\Search\SearchResultInterface;
use Shopware\Framework\ORM\Search\SearchResultTrait;

class ConfigurationGroupTranslationSearchResult extends ConfigurationGroupTranslationBasicCollection implements SearchResultInterface
{
    use SearchResultTrait;
}