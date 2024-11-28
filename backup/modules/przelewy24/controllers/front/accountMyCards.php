<?php
/**
 * Class przelewy24accountMyCardsModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24accountMyCardsModuleFrontController
 */
class Przelewy24accountMyCardsModuleFrontController extends ModuleFrontController
{
    /**
     * Init.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();

        $this->registerStylesheet('p24-style-local', 'modules/przelewy24/views/css/przelewy24.css');

        $przelewy24 = new Przelewy24();
        $message = '';
        if (empty($this->context->customer->id)) {
            Tools::redirect('index.php');
        }
        $customerId = (int) $this->context->customer->id;

        if (Tools::isSubmit('submit')) {
            if ((int) Tools::getValue('remember_cc_post')) {
                $remember = (int) Tools::getValue('remember_credit_cards');

                Przelewy24CustomerSetting::initialize($customerId)->setIsCardRemembered($remember)->save();
                $message = $przelewy24->getLangString('Saved successfully');
            }
            if ($idOgRecurringToRemove = (int) Tools::getValue('remove_card')) {
                $entryToRemove = new Przelewy24Recurring($idOgRecurringToRemove);
                if ($entryToRemove->canBeRemovedByUser($customerId)) {
                    $entryToRemove->delete();
                }
                $message = $przelewy24->getLangString('Removed successfully');
            }
        }
        $customerCards = Przelewy24Recurring::findArrayByCustomerId($customerId);
        $this->context->smarty->assign(
            [
                'logo_url' => $this->module->getPathUri() . 'views/img/logo.png',
                'home_url' => _PS_BASE_URL_,
                'urls' => $this->getTemplateVarUrls(),
                'customer_cards' => $customerCards,
                'message' => $message,
                'remember_customer_cards' => Przelewy24CustomerSetting::initialize($customerId)->card_remember,
            ]
        );
        $this->setTemplate('module:przelewy24/views/templates/front/account_card_page.tpl');
    }
}
