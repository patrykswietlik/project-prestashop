<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class BluepaymentAjaxModuleFrontController extends ModuleFrontController
{
    public $ajax;

    public function __construct()
    {
        parent::__construct();
        $this->page_name = 'ajax';
    }

    public function init()
    {
        parent::init();
    }

    // Helper do analityki

    public function initContent()
    {
        $ajax = true;

        parent::initContent();
        if (Tools::getValue('action') == 'GaRemoveProduct') {
            $product_id = Tools::getValue('id_product');
            $product_id_attribute = Tools::getValue('id_attribute');

            if (!$product_id) {
                return;
            }

            $product = new Product(
                $product_id,
                true,
                (int) Context::getContext()->language->id,
                (int) Context::getContext()->shop->id
            );

            $this->ajaxDie(
                json_encode(
                    [
                        'success' => true,
                        'data' => [
                            'id' => $product_id,
                            'name' => $product->name,
                            'brand' => $product->manufacturer_name,
                            'category' => $product->category,
                            'variant' => $product_id_attribute,
                            'price' => $product->price,
                        ],
                    ]
                )
            );
        }
    }
}
