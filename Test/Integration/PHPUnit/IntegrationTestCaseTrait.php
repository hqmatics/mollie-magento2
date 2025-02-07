<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mollie\Payment\Test\Integration\PHPUnit;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Mollie\Payment\Test\Fakes\FakeEncryptor;

trait IntegrationTestCaseTrait
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function loadOrder(string $incrementId): OrderInterface
    {
        return $this->loadOrderById($incrementId);
    }

    public function loadOrderById(string $orderId): OrderInterface
    {
        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $builder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $builder->addFilter('increment_id', $orderId, 'eq')->create();

        $orderList = $repository->getList($searchCriteria)->getItems();

        $order = array_shift($orderList);
        $order->setBaseCurrencyCode('EUR');
        $order->setOrderCurrencyCode('EUR');

        return $order;
    }

    /**
     * Load a custom fixture in the Test/Fixtures folder, and make it think it's inside the
     * `dev/test/integration/testsuite` folder so it can rely on other fixtures.
     *
     * @param $path
     * @throws \Exception
     */
    public function loadFixture($path)
    {
        $cwd = getcwd();

        $fullPath = __DIR__ . '/../../Fixtures/' . $path;
        if (!file_exists($fullPath)) {
            throw new \Exception('The path "' . $fullPath . '" does not exists');
        }

        chdir($this->getRootDirectory() . '/dev/tests/integration/testsuite/');
        require $fullPath;
        chdir($cwd);
    }

    public function getRootDirectory()
    {
        static $path;

        if (!$path) {
            $directoryList = $this->objectManager->get(DirectoryList::class);
            $path = $directoryList->getRoot();
        }

        return $path;
    }

    public function loadFakeEncryptor(): FakeEncryptor
    {
        $instance = $this->objectManager->get(FakeEncryptor::class);

        $this->objectManager->addSharedInstance($instance, Encryptor::class);

        return $instance;
    }
}
