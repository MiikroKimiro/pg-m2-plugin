<?php

namespace Paymentez\PaymentGateway\Gateway\Config\Handler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Paymentez\PaymentGateway\Gateway\Config\GatewayConfig;
use Paymentez\PaymentGateway\Helper\Logger;
use Paymentez\PaymentGateway\Helper\UtilManagement;

class CanVoidHandler implements ValueHandlerInterface
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * CanVoidHandler constructor.
     * @param GatewayConfig $config
     */
    public function __construct(GatewayConfig $config)
    {
        $this->logger = $config->logger;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function handle(array $subject, $storeId = null)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $subject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        UtilManagement::setStatusForReviewByKount($paymentDO, $this->logger);

        $is_authorized = $payment->getAmountAuthorized() > 0;
        $is_captured = $payment->getAmountPaid() > 0;
        $is_refunded = $payment->getAmountRefunded() > 0;
        $is_canceled = $payment->getAmountCanceled() > 0;
        $is_closed = $payment->getData('is_transaction_close');

        $can_void = $is_authorized && !$is_closed && !$is_captured && !$is_refunded && !$is_canceled;
        $this->logger->debug(sprintf('CanVoidHandler.handle $can_void %s .', $can_void));
        return $can_void;
    }
}
