<?php

namespace Marello\Bundle\MagentoBundle\Provider\Transport;

/**
 * @deprecated since 2.2. Use {@see Marello\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface} instead
 */
interface MagentoSoapTransportInterface extends MagentoTransportInterface
{
    /**
     * @param string $action
     * @param array  $params
     *
     * @return mixed
     */
    public function call($action, $params = []);
}
