<?php declare(strict_types=1);

namespace Shopware\Storefront\Test\Pagelet\AccountLogin;

use Shopware\Storefront\Pagelet\AccountLogin\AccountLoginPageletRequest;
use Shopware\Storefront\Pagelet\AccountLogin\AccountLoginPageletRequestResolver;
use Shopware\Storefront\Test\Page\PageRequestTestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class AccountLoginPageletRequestTest extends PageRequestTestCase
{
    /**
     * @var AccountLoginPageletRequestResolver
     */
    private $requestResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->requestResolver = new AccountLoginPageletRequestResolver(
            $this->getContainer()->get('event_dispatcher'),
            $this->getContainer()->get('request_stack')
        );
    }

    public function testResolveArgument()
    {
        $httpRequest = $this->buildRequest();

        $request = $this->requestResolver->resolve(
            $httpRequest,
            new ArgumentMetadata('foo', self::class, false, false, null)
        );

        $request = iterator_to_array($request);
        $request = array_pop($request);

        static::assertInstanceOf(AccountLoginPageletRequest::class, $request);
    }
}