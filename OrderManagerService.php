<?php

namespace App\Modules\Orders\Services\OrderManager;

use App\Enums\OrderItemType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Modules\Orders\Services\OrderManager\Contracts\OrderItemGiverInterface;
use App\Modules\Orders\Services\OrderManager\Contracts\OrderItemTakerInterface;
use Exception;

/**
 * Менеджер заказов даёт или забирает заказы.
 * Обычно заказ отдаётся заказчику, когда заказчик оплатил заказ.
 * Обычно заказ забирается у заказчика, когда заказчик вернул (refund) заказ или средства.
 *
 * Под "дать заказ" имеется в виду присвоение пользователю купленных им вещей.
 * Под "забрать заказ" имеется в виду изъятие выданного ранее заказа у пользователя.
 */
class OrderManagerService
{
    /**
     * Передать содержимое заказа пользователю
     *
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function give(Order $order): void
    {
        foreach ($order->items as $item) {
            $taker = self::makeGiver($item);
            $taker->give();
        }
    }

    /**
     * Забрать (возврат\refund) содержимое заказа у пользователя
     *
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function take(Order $order): void
    {
        foreach ($order->items as $item) {
            $taker = self::makeTaker($item);
            $taker->take();
        }
    }

    /**
     * Factory Method
     *
     * @param OrderItem $orderItem
     * @return OrderItemGiverInterface
     * @throws Exception
     */
    public static function makeGiver(OrderItem $orderItem): OrderItemGiverInterface
    {
        return match ($orderItem->type) {
            OrderItemType::ACCOUNT => new OrderItemAccountGiver($orderItem),
            OrderItemType::BOOK => new OrderItemBookGiver($orderItem),
            OrderItemType::SAVE_CARD => new SaveCardGiver(),
            default => throw new Exception('Invalid order item type'),
        };
    }

    /**
     * Factory Method
     *
     * @param OrderItem $orderItem
     * @return OrderItemTakerInterface
     * @throws Exception
     */
    public static function makeTaker(OrderItem $orderItem): OrderItemTakerInterface
    {
        return match ($orderItem->type) {
            OrderItemType::ACCOUNT => new OrderItemAccountTaker($orderItem),
            OrderItemType::BOOK => new OrderItemBookTaker($orderItem),
            OrderItemType::SAVE_CARD => new SaveCardTaker(),
            default => throw new Exception('Invalid order item type'),
        };
    }
}
