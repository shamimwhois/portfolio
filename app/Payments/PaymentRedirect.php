<?php

namespace App\Payments;

class PaymentRedirect
{
    public function __construct(
        public string $url,
        public ?string $reference = null,
    ) {}
}
