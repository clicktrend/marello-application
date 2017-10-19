<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Marello\Bundle\ProductBundle\Entity\Product;
use Marello\Bundle\PricingBundle\Entity\ProductPrice;
use Marello\Bundle\ProductBundle\Entity\ProductSupplierRelation;

class LoadProductData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var \Oro\Bundle\OrganizationBundle\Entity\Organization $defaultOrganization  */
    protected $defaultOrganization;

    /** @var ObjectManager $manager */
    protected $manager;

    public function getDependencies()
    {
        return [
            LoadSalesData::class,
            LoadTaxCodeData::class
        ];
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $organizations = $this->manager
            ->getRepository('OroOrganizationBundle:Organization')
            ->findAll();

        if (is_array($organizations) && count($organizations) > 0) {
            $this->defaultOrganization = array_shift($organizations);
        }

        $this->loadProducts();
    }

    /**
     * load products
     */
    public function loadProducts()
    {
        $handle = fopen($this->getDictionary('products.csv'), "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));

                $product = $this->createProduct($data);
                $this->setReference('marello-product-' . $i, $product);
                $i++;
            }
            fclose($handle);
        }
        $this->manager->flush();
    }

    /**
     * create new products
     * @param array $data
     * @return Product $product
     */
    private function createProduct(array $data)
    {
        $product = new Product();
        $product->setSku($data['sku']);
        $product->setName($data['name']);
        $product->setOrganization($this->defaultOrganization);
        $product->setWeight(mt_rand(50, 300) / 100);

        $status = $this->manager
            ->getRepository('MarelloProductBundle:ProductStatus')
            ->findOneByName($data['status']);

        $product->setStatus($status);
        $channels = explode(';', $data['channel']);
        $currencies = [];
        foreach ($channels as $channelId) {
            $channel = $this->getReference('marello_sales_channel_'. (int)$channelId);
            $product->addChannel($channel);
            $currencies[] = $channel->getCurrency();
        }

        $currencies = array_unique($currencies);
        /**
         * add default prices for all currencies
         */
        foreach ($currencies as $currency) {
            // add prices
            $price = new ProductPrice();
            $price->setCurrency($currency);
            $priceValue = $this->getValuePerCurrency($data, $currency);
            $price->setValue($priceValue);
            $product->addPrice($price);
        }

        /**
        * set default taxCode
        */
        $product->setTaxCode($this->getReference('marello_taxcode_0'));
//        foreach ($channels as $channelId) {
//            $channel = $this->getReference('marello_sales_channel_'. (int)$channelId);
//
//            $productSalesChannelRelation = new ProductChannelTaxRelation();
//            $productSalesChannelRelation
//                ->setProduct($product)
//                ->setSalesChannel($channel)
//                ->setTaxCode($this->getReference('marello_taxcode_'. rand(0, 2)))
//            ;
//            $this->manager->persist($productSalesChannelRelation);
//            $product->addSalesChannelTaxCode($productSalesChannelRelation);
//        }

        /**
         * add suppliers per product
         */
        $this->addProductSuppliers($product);

        $this->manager->persist($product);

        return $product;
    }

    /**
     * @param array $data
     * @param string $currency
     * @return float
     */
    protected function getValuePerCurrency(array $data, $currency = 'EUR')
    {
        $currencyPriceIdentifier = sprintf('price_%s', $currency);
        if (isset($data[$currencyPriceIdentifier]) && !empty($currencyPriceIdentifier)) {
            return $data[$currencyPriceIdentifier];
        }

        return $data['default_price'];
    }

    /**
     * Add product suppliers to product
     * @param Product $product
     */
    protected function addProductSuppliers(Product $product)
    {
        $suppliers = $this->manager
            ->getRepository('MarelloSupplierBundle:Supplier')
            ->findAll();

        foreach ($suppliers as $supplier) {
            $productSupplierRelation = new ProductSupplierRelation();
            $productSupplierRelation
                ->setProduct($product)
                ->setSupplier($supplier)
                ->setQuantityOfUnit(100)
                ->setCanDropship(true)
                ->setPriority(rand(1, 6))
                ->setCost($this->getRandomFloat(25, 30))
            ;
            $this->manager->persist($productSupplierRelation);
            $product->addSupplier($productSupplierRelation);
        }

        $preferredSupplier = null;
        $preferredPriority = 0;
        foreach ($product->getSuppliers() as $productSupplierRelation) {
            if (null == $preferredSupplier) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
                continue;
            }
            if ($productSupplierRelation->getPriority() < $preferredPriority) {
                $preferredSupplier = $productSupplierRelation->getSupplier();
                $preferredPriority = $productSupplierRelation->getPriority();
            }
        }

        if ($preferredSupplier) {
            $product->setPreferredSupplier($preferredSupplier);
        }
    }

    /**
     * Get random float
     * @param $min
     * @param $max
     * @return mixed
     */
    private function getRandomFloat($min, $max)
    {
        return ($min + lcg_value()*(abs($max - $min)));
    }

    /**
     * Get dictionary file by name
     * @param $name
     * @return string
     */
    protected function getDictionary($name)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
    }
}
