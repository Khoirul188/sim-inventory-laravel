<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requestedQty,
    ) {
        parent::__construct(
            "Stok {$product->nama} tidak cukup. Tersedia {$product->stok}, diminta {$requestedQty}."
        );
    }
}

