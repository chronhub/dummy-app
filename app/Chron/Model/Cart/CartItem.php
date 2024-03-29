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

    /**
     * @param array{cart_item_sku: string, cart_item_quantity: positive-int, cart_item_price: string} $data}
     */
    public static function make(CartItemId $cartItemId, array $data): self
    {
        return self::fromArray($data + ['cart_item_id' => $cartItemId->toString()]);
    }

    /**
     * @param array{cart_item_id: string, cart_item_sku: string, cart_item_quantity: positive-int, cart_item_price: string} $data}
     */
    public static function fromArray(array $data): self
    {
        return new self(
            CartItemId::fromString($data['cart_item_id']),
            CartItemSku::fromString($data['cart_item_sku']),
            CartItemQuantity::fromInteger($data['cart_item_quantity']),
            CartItemPrice::fromString($data['cart_item_price'])
        );
    }

    public static function fromValues(string $cartItemId, string $sku, int $quantity, string $price): self
    {
        return new self(
            CartItemId::fromString($cartItemId),
            CartItemSku::fromString($sku),
            CartItemQuantity::fromInteger($quantity),
            CartItemPrice::fromString($price)
        );
    }

    public function withAdjustedQuantity(CartItemQuantity $quantity): self
    {
        return new self($this->id, $this->sku, $quantity, $this->price);
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
