<?php

declare(strict_types=1);

namespace App\Chron\Model\Cart;

final readonly class CartItem
{
    private function __construct(
        public CartItemId $id,
        public CartItemSku $sku,
        public CartItemQuantity $quantity,
        public CartItemPrice $price
    ) {
    }

    public static function fromArray(array $data): self
    {
        // fixMe
        $cartItemId = $data['cart_item_id'] ?? CartItemId::create();

        return new self(
            $cartItemId,
            CartItemSku::fromString($data['cart_item_sku']),
            CartItemQuantity::from($data['cart_item_quantity']),
            CartItemPrice::fromString($data['cart_item_price'])
        );
    }

    public function sameValueAs(self $other): bool
    {
        return $this->id->equalsTo($other->id)
            && $this->sku->equalsTo($other->sku)
            && $this->quantity->sameValueAs($other->quantity)
            && $this->price->sameValueAs($other->price);
    }

    public function toArray(): array
    {
        return [
            'cart_item_id' => $this->id->toString(),
            'cart_item_sku' => $this->sku->toString(),
            'cart_item_quantity' => $this->quantity->value,
            'cart_item_price' => $this->price->value,
        ];
    }
}
