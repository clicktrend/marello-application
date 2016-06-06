<?php

namespace Marello\Bundle\ShippingBundle\Tests\Functional\Integration\UPS;

use Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOrderData;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSApi;
use Marello\Bundle\ShippingBundle\Integration\UPS\UPSApiException;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class UPSApiTest extends WebTestCase
{
    /** @var UPSApi */
    protected $api;

    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadOrderData::class]);

        $this->api = $this->client->getContainer()->get('marello_shipping.integration.ups.api');
    }

    /**
     * @test
     * @covers UPSApi::post
     */
    public function apiShouldThrowExceptionWhenEmptyRequestIsSent()
    {
//        $this->setExpectedException(
//            UPSApiException::class,
//            'The request is not well-formed or the operation is not defined. Review for errors before re-submitting.'
//        );

        $this->api->post('ShipConfirm', '');
    }

    /**
     * @test
     * @covers UPSApi::post
     */
    public function apiShouldReturnValidResponse()
    {
        $dataFactory = $this->client->getContainer()->get('marello_shipping.integration.ups.service_data_factory');

        /** @var Order $order */
        $order = $this->getReference('marello_order_1');

        $data = $dataFactory->createData($order);

        $requestBuilder = $this->client
            ->getContainer()
            ->get('marello_shipping.integration.ups.request_builder.shipment_confirm');

        $request = $requestBuilder->build($data);

        dump($request);

        $result = $this->api->post('ShipConfirm', $request);

        dump($result);
    }
}
