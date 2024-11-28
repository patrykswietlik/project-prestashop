<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AmazonPayFormHelper extends HelperForm
{

    public function generateAmazonForm(&$smarty, $fields_form)
    {
        $this->fields_form = $fields_form;
        if (isset($fields_form[0]['form']['form']['id_form'])) {
            $this->fields_form['form']['form']['id_form'] = $fields_form[0]['form']['form']['id_form'];
        }
        $base_generate = $this->generate();
        $smarty->assign('form_vars', $this->tpl->getTemplateVars());
        return $base_generate;
    }

    public function createTemplate($tpl_name)
    {
        $this->context->smarty->assign('default_template', $this->base_folder . $tpl_name);
        return parent::createTemplate($tpl_name);
    }
}
