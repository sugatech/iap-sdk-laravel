<?php

namespace IAP\SDK\Enums;

use BenSampo\Enum\Enum;

class PurchaseStatus extends Enum
{
    const Created = 'created';
    const PaymentReview = 'payment_review';
    const Completed = 'completed';
    const Refunded = 'refunded';
    const Closed = 'closed';
}
