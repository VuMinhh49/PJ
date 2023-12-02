<?php

namespace App\Domains\Cart\Services;

use App\Services\BaseService;
use Illuminate\Http\Response;
use App\Domains\Cart\Models\Cart;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Product\Models\Product;
use App\Domains\ProductDetail\Models\ProductDetail;

/**
 * Class CartService.
 */
class CartService extends BaseService
{

    protected ProductDetail $productDetail;
    protected Product $product;
    protected Coupon $coupon;
    /**
     * CartService constructor.
     * @param Cart $cart
     * @param ProductDetail $productDetail
     * @param Product $product
     * @param Coupon $coupon
     */
    public function __construct(
        Cart $cart,
        ProductDetail $productDetail,
        Product $product,
        Coupon $coupon

    ) {
        $this->model = $cart;
        $this->productDetail = $productDetail;
        $this->product = $product;
        $this->coupon = $coupon;
    }

    public function getProductInCartByUserId()
    {
        return $this->model
            ->where('user_id', auth()->user()->id)
            ->where('product_quantity', '!=', 0)
            ->get();
    }

    public function addToCart(array $data)
    {
        foreach ($data['productDetail'] as $item) {
            $productInCart = $this->getExistProductInCart($item['productDetailId']);
            if (isset($productInCart) && $productInCart->product_detail_id == $item['productDetailId']) {
                $productInCart->update([
                    'user_id' => auth()->user()->id,
                    'product_detail_id' => $item['productDetailId'],
                    'product_quantity' => $productInCart->product_quantity + (int) $item['quantity'],
                    'product_size' => $item['size'],
                    'product_color' => $item['color'],
                    'product_price' => $item['price']
                ]);
            } else {
                $this->model->create([
                    'user_id' => auth()->user()->id,
                    'product_detail_id' => $item['productDetailId'],
                    'product_quantity' => $item['quantity'],
                    'product_size' => $item['size'],
                    'product_color' => $item['color'],
                    'product_price' => $item['price']
                ]);
            }
            $productDetail = $this->getProductDetail($item['productDetailId']);
            $productDetail->update([
                'quantity' => $productDetail->quantity - (int) $item['quantity']
            ]);
        }
    }

    public function updateProductInCart(array $data)
    {
        $productDetailInCart = $this->getExistProductInCartByCartId($data['productDetailId'], $data['cartId']);

        abort_if(!$productDetailInCart, Response::HTTP_NOT_FOUND);
        $productDetail = $this->getProductDetail($data['productDetailId']);

        if ($data['newQuantity'] < 1) {
            $this->deleteProductFromCart($data['productDetailId'], $data['cartId']);
        } else if ($data['newQuantity'] > $data['oldQuantity']) { // Inc quantity
            $excessQuantity = $data['newQuantity'] - $data['oldQuantity'];
            $productDetailInCart->update([
                'product_quantity' => $data['newQuantity'],
            ]);

            $productDetail->update([
                'quantity' => $productDetail->quantity - (int) $excessQuantity
            ]);
        } else if ($data['newQuantity'] < $data['oldQuantity']) { //Dec quantity
            $excessQuantity = $data['oldQuantity'] - $data['newQuantity'];
            $productDetailInCart->update([
                'product_quantity' => $data['newQuantity'],
            ]);

            $productDetail->update([
                'quantity' => $productDetail->quantity + (int) $excessQuantity
            ]);
        }
    }

    public function deleteProductFromCart(int $productDetailId, int $cartId)
    {
        $productDetailInCart = $this->getExistProductInCartByCartId($productDetailId, $cartId);

        abort_if(!$productDetailInCart, Response::HTTP_NOT_FOUND);
        $productDetail = $this->getProductDetail($productDetailId);
        $productDetail->update([
            'quantity' => $productDetail->quantity + $productDetailInCart->product_quantity
        ]);

        $productDetailInCart->delete();
    }

    public function getExistProductInCart(int $productDetailId)
    {
        return $this->model
            ->where('user_id', auth()->user()->id)
            ->where('product_detail_id', $productDetailId)
            ->first();
    }

    public function getExistProductInCartByCartId(int $productDetailId, int $cartId)
    {
        return $this->model
            ->where('id', $cartId)
            ->where('user_id', auth()->user()->id)
            ->where('product_detail_id', $productDetailId)
            ->first();
    }

    public function getProductDetail(int $productDetailId)
    {
        return $this->productDetail
            ->where('id', $productDetailId)
            ->first();
    }

    public function getProductInCart(int $product)
    {
        return $this->product
            ->where('id', $product)
            ->first();
    }

    public function getProductDetailInCartByProductDetailId(int $productDetailId)
    {
        return $this->product
            ->where('user_id', auth()->user()->id)
            ->where('id', $productDetailId)
            ->first();
    }

    public function getPriceProductInCart()
    {
        $productsInCart = $this->getProductInCartByUserId();

        return $productsInCart->reduce(function ($carry, $product) {
            $quantity = $product->product_quantity;
            $price = $product->product_price;

            return $carry + ($quantity * $price);
        }, 0);
    }

    public function checkCouponUnusedUserAndStillExpiryDate(string|null $name)
    {
        return $this->coupon->firstWithExpiryDate($name ?? '');
    }

    public function getCouponByName(string|null $name)
    {
        return $this->coupon->where('name', $name ?? '')->first();
    }

    public function getCountQuantityProductInCart()
    {
        return $this->model->where('user_id', auth()->user()->id)->count();
    }

    public function applyCouponIntoCart(string $name)
    {
        $coupon = $this->coupon->firstWithExpiryDate($name);

        $coupon->update([
            'quantity' => (int) $coupon->quantity - 1
        ]);

        $coupon->syncUser(auth()->user()->id);

        return $coupon;
    }

    public function deleteCouponFromCart($name)
    {
        $coupon = $this->getCouponByName($name);

        $coupon->update([
            'quantity' => (int) $coupon->quantity + 1
        ]);

        $coupon->detachUser(auth()->user()->id);

        return $coupon;
    }
}
