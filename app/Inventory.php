<?php

namespace App;

use App\Helpers\InventoryAction;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory_log';
    protected $guarded=[];

    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const ACTION_MOVE = 'move';

    const TARGET_GIFT = 'gift';
    const TARGET_CASH = 'cash';


    private static $inv;
    private static $product;
    private static $quantityRow;

    public static function updateInventory(InventoryAction $inv){
        
        Location::findOrFail($inv->location_id);
        static::$inv = $inv;
        
        $product = Product::findOrFail($inv->product_id);
        static::$product = $product;

        if(!$quantityRow = $product->getQuantity($inv->location_id)){ return ; }
        static::$quantityRow = $quantityRow;

        switch ($inv->action) {
            case static::ACTION_ADD:
                static::addQuantity($inv->target);
                break;
            case static::ACTION_REMOVE:
                static::removeQuantity($inv->target);
                break;
            case static::ACTION_MOVE:

                switch ($inv->target) {
                    case static::TARGET_GIFT:

                        static::addQuantity(static::TARGET_CASH);
                        static::removeQuantity(static::TARGET_GIFT);

                        break;
                    case static::TARGET_CASH:

                        static::addQuantity(static::TARGET_GIFT);
                        static::removeQuantity(static::TARGET_CASH);

                        break;
                    default:
                        break;
                }

                break;
            default:
                break;
        }

    }

    private static function addQuantity($target){
        switch ($target) {
            case static::TARGET_GIFT:
                $quantity = static::$quantityRow->quantity + static::$inv->quantity;
                static::$product->updateQuantity(static::$inv->location_id,$quantity);
                break;
            case static::TARGET_CASH:
                $quantity = static::$quantityRow->quantity_cash + static::$inv->quantity;
                static::$product->updateQuantityCash(static::$inv->location_id,$quantity);
                break;
            default:
                break;
        }
        static::log(1,$target);
    }

    private static function removeQuantity($target){
        switch ($target) {
            case static::TARGET_GIFT:
                $quantity = static::$quantityRow->quantity - static::$inv->quantity;
                static::$product->updateQuantity(static::$inv->location_id,$quantity);
                break;
            case static::TARGET_CASH:
                $quantity = static::$quantityRow->quantity_cash - static::$inv->quantity;
                static::$product->updateQuantityCash(static::$inv->location_id,$quantity);
                break;
            default:
                break;
        }
        static::log(0,$target);
    }

    private static function log($give_take,$target){

        $row = [
            'location_id'=>static::$inv->location_id,
            'product_id'=>static::$inv->product_id,
            'give_take'=>$give_take,
            'comment'=>static::$inv->comment,
        ];

        switch ($target) {
            case static::TARGET_GIFT:
                $row['quantity_gift'] = static::$inv->quantity;
                break;
            case static::TARGET_CASH:
                $row['quantity_cash'] = static::$inv->quantity;
                break;
            default:
                break;
        }

        Inventory::create($row);
    }



}
