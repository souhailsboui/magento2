<?php

// app/code/GlobalColours/WpAuth/Plugin/AuthPlugin.php

namespace GlobalColours\WpAuth\Plugin;

use GlobalColours\WpAuth\Model\PasswordHashFactory;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class AuthPlugin
{
    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PasswordHashFactory
     */
    protected $passwordHashFactory;

    /**
     * Constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager,
     * @param CustomerRegistry $customerRegistry
     * @param PasswordHashFactory $passwordHashFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        CustomerRegistry $customerRegistry,
        PasswordHashFactory $passwordHashFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerRegistry = $customerRegistry;
        $this->passwordHashFactory = $passwordHashFactory;
    }

    public function afterResetPassword(AccountManagement $accountManagement, bool $result, $email, $resetToken, $newPassword)
    {
        if (!$result) {
            return false;
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        try {
            $customer = $this->customerRepository->get($email, $websiteId);
        } catch (NoSuchEntityException $e) {
            return true;
        }

        $isWpAuth = $customer->getCustomAttribute("is_wp_auth")->getValue();

        if ((bool) $isWpAuth) {
            $customer->setCustomAttribute("is_wp_auth", false);
            $this->customerRepository->save($customer);
        }

        return true;
    }

    public function beforeAuthenticate(AccountManagement $accountManagement, $username, $password)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        try {
            $customer = $this->customerRepository->get($username, $websiteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $isWpAuth = $customer->getCustomAttribute("is_wp_auth")->getValue();

        if ((bool) $isWpAuth) {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());

            $passwordHash = $customerSecure->getPasswordHash();
            if ($this->verifyWpPassword($password, $passwordHash)) {
                $newHash = $accountManagement->getPasswordHash($password);

                // Update is_wp_auth
                $customer->setCustomAttribute("is_wp_auth", false);

                // Update customer data
                $customerSecure->setPasswordHash($newHash);

                $this->customerRepository->save($customer);
            }
        }

        return null;
    }


    private function verifyWpPassword($password, $hash)
    {
        $wp_hasher = $this->passwordHashFactory->create();
        return $wp_hasher->CheckPassword($password, $hash);
    }
}
