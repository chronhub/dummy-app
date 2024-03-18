<?php

declare(strict_types=1);

namespace App\Chron\Model\Product;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Str;

use function explode;
use function preg_replace;
use function substr;

final class Sku
{
    public function __construct(
        public ProductId $productId,
        public ProductInfo $productInfo
    ) {
    }

    public function generateSku(): string
    {
        $code = 'CT_'.$this->formatCategory($this->productInfo->category).'-';
        $code .= 'BR_'.$this->format($this->productInfo->brand).'-';
        $code .= 'MD_'.$this->format($this->productInfo->model).'-';
        $code .= 'DT_'.$this->now();

        return $code;
    }

    private function now(): string
    {
        $time = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return $time->format('Ymd');
    }

    private function format(string $value): string
    {
        $value = $this->toLowerAlphaNumeric($value);

        return substr($value, 0, 3);
    }

    private function formatCategory(string $category): string
    {
        [$value, $number] = explode(' ', $category);

        return substr($this->toLowerAlphaNumeric($value), 0, 1).$number;
    }

    private function toLowerAlphaNumeric(string $value): string
    {
        $value = Str::squish($value);

        return Str::lower(preg_replace('/[^A-Za-z0-9\-]/', '', $value));
    }
}
