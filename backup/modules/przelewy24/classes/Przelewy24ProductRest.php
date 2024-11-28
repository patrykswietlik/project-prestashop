<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ProductRest
 */
class Przelewy24ProductRest
{
    /**
     * Prepare cart items for .
     */
    public function prepareCartItemsRest(Przelewy24PayloadForRestTransaction $payload, array $items = [])
    {
        if (empty($items)) {
            return;
        }

        $payload->cart = [];

        foreach ($items as $item) {
            $row = new Przelewy24PayloadCartItem();

            $row->sellerId = (string) $payload->posId;
            $row->sellerCategory = 'default';
            $row->name = Tools::substr(strip_tags($item['name']), 0, 127);
            $row->description = Tools::substr(strip_tags($item['description']), 0, 127);
            $row->quantity = (int) $item['quantity'];
            $row->price = (int) $item['price'];
            $row->number = (string) $item['number'];

            $payload->cart[] = $row;
        }
    }
}
