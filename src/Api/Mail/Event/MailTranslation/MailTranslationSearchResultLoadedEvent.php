<?php declare(strict_types=1);

namespace Shopware\Api\Mail\Event\MailTranslation;

use Shopware\Api\Mail\Struct\MailTranslationSearchResult;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Event\NestedEvent;

class MailTranslationSearchResultLoadedEvent extends NestedEvent
{
    public const NAME = 'mail_translation.search.result.loaded';

    /**
     * @var MailTranslationSearchResult
     */
    protected $result;

    public function __construct(MailTranslationSearchResult $result)
    {
        $this->result = $result;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getContext(): TranslationContext
    {
        return $this->result->getContext();
    }
}