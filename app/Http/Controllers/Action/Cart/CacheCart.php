<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action\Cart;

use App\Chron\Projection\Provider\CartProvider;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\RedisTaggedCache;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use stdClass;

final readonly class CacheCart
{
    private RedisTaggedCache $cache;

    public function __construct(private CartProvider $cartProvider)
    {
        $cache = Cache::store('redis')->getStore();

        if (! $cache instanceof RedisStore) {
            throw new RuntimeException('Cache store must be an instance of RedisStore');
        }

        $this->cache = $cache->tags('carts');
    }

    public function update(string $cartId): void
    {
        $key = $this->determineCacheKey($cartId);

        $cart = $this->cartProvider->findCartById($cartId);

        $this->cache->put($key, $cart, 3600);
    }

    public function getCart(string $cartId): stdClass
    {
        $key = $this->determineCacheKey($cartId);

        if (! $this->cache->has($key)) {
            $cart = $this->cartProvider->findCartById($cartId);

            $this->cache->put($key, $cart, 3600);
        }

        return $this->cache->get($key);
    }

    public function delete(string $cartId): void
    {
        $key = $this->determineCacheKey($cartId);

        $this->cache->forget($key);
    }

    private function determineCacheKey(string $cartId): string
    {
        return "cart:$cartId";
    }
}
