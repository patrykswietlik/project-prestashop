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

declare(strict_types=1);

namespace BluePayment\Install;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Analyse\Amplitude;
use BluePayment\Config\Config;
use Symfony\Component\Translation\TranslatorInterface;
use Tab;

class Installer
{
    public const MODULE_ADMIN_CONTROLLERS = [
        [
            'class_name' => 'AdminBluepaymentPayments',
            'visible' => false,
            'parent' => -1,
            'name' => 'Autopay - Configuration',
        ],
        [
            'class_name' => 'AdminBluepaymentAjax',
            'visible' => false,
            'parent' => -1,
            'name' => 'Autopay - Ajax',
        ],
    ];

    /**
     * @var \BluePayment
     */
    private $module;
    protected $translator;
    protected $db;

    public function __construct(\BluePayment $module, TranslatorInterface $translator)
    {
        $this->module = $module;
        $this->translator = $translator;
        $this->db = \Db::getInstance();
    }

    /**
     * Installer
     *
     * @throws \Exception
     */
    public function install(): bool
    {
        $this->installDb();
        $this->installTabs();
        $this->installContext();
        $this->eventInstalled();

        return true;
    }

    /**
     * Uninstall
     *
     * @throws \Exception
     */
    public function uninstall(): bool
    {
        $this->uninstallDb();
        $this->uninstallTabs();
        $this->eventUninstalled();

        return true;
    }

    /**
     * Sql data installation
     *
     * @throws \Exception
     */
    public function installDb($custom_path = null)
    {
        $sql_path = $this->module->getLocalPath() . 'src/Install/install.sql';
        if ($custom_path) {
            $sql_path = $custom_path;
        }
        if (!file_exists($sql_path)) {
            return false;
        }
        $this->executeSqlFromFile($sql_path, $this->db);
    }

    /**
     * Deleting sql data
     *
     * @throws \Exception
     */
    public function uninstallDb($custom_path = null)
    {
        $sql_path = $this->module->getLocalPath() . 'src/Install/uninstall.sql';
        if ($custom_path) {
            $sql_path = $custom_path;
        }
        if (!file_exists($sql_path)) {
            return false;
        }
        $this->executeSqlFromFile($sql_path, $this->db);
    }

    /**
     * Install tab controller
     */
    public function installTabs(): bool
    {
        $res = true;

        foreach (self::MODULE_ADMIN_CONTROLLERS as $controller) {
            if (\Tab::getIdFromClassName($controller['class_name'])) {
                continue;
            }

            $tab = new \Tab();
            $tab->class_name = $controller['class_name'];
            $tab->id_parent = $controller['parent'];
            $tab->active = $controller['visible'];

            if (isset($controller['icon'])) {
                $tab->icon = $controller['icon'];
            }

            foreach (\Language::getLanguages() as $lang) {
                if ($lang['locale'] === 'pl-PL') {
                    $tab->name[$lang['id_lang']] = $this->translator->trans(
                        'Autopay - Konfiguracja',
                        [],
                        'Modules.Bluepayment.Admin',
                        $lang['locale']
                    );
                } else {
                    $tab->name[$lang['id_lang']] = $this->translator->trans(
                        'Autopay - Configuration',
                        [],
                        'Modules.Bluepayment.Admin',
                        $lang['locale']
                    );
                }
            }
            $tab->module = $this->module->name;
            $res = $res && $tab->add();
        }

        return $res;
    }

    /**
     * Remove all tabs controller
     */
    public function uninstallTabs($tabId = 0): bool
    {
        foreach (static::MODULE_ADMIN_CONTROLLERS as $controller) {
            $id_tab = (int) \Tab::getIdFromClassName($controller['class_name']);
            $tab = new \Tab($id_tab);
            if (\Validate::isLoadedObject($tab)) {
                $parentTabID = $tabId ?: $tab->id_parent;
                $tab->delete();
                $tabCount = $this->getTabElements($parentTabID);
                if ($tabCount == 0) {
                    $this->deleteCurrentTab((int) $parentTabID);
                }
            }
        }

        return true;
    }

    public function deleteCurrentTab($parentTabID)
    {
        $parentTab = new \Tab((int) $parentTabID);
        $parentTab->delete();
    }

    public function getTabElements($parentTabID)
    {
        if ($parentTabID == '-1') {
            return 0;
        }

        return \Tab::getNbTabs((int) $parentTabID);
    }

    /**
     * Execute sql files
     *
     * @param string $path
     *
     * @throws \Exception
     */
    public function executeSqlFromFile(string $path, $dba = null)
    {
        $db = $dba ?? $this->db;

        if (!file_exists($path)) {
            return false;
        }
        $sqlStatements = \Tools::file_get_contents($path);
        $sqlStatements = str_replace(['_DB_PREFIX_', '_MYSQL_ENGINE_'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sqlStatements);

        $status = true;

        $sql = preg_split("/;\s*[\r\n]+/", trim($sqlStatements));

        foreach ($sql as $query) {
            $status &= $this->sqlExecute($db, $query);
        }

        if (!$status) {
            throw new \Exception();
        }

        return true;
    }

    public function sqlExecute($db, $sqlStatements)
    {
        if ($sqlStatements && is_object($db)) {
            return $db->execute($sqlStatements);
        } else {
            return false;
        }
    }

    public function eventInstalled()
    {
        $data = [
            'events' => [
                'event_type' => Config::PLUGIN_INSTALLED,
                'user_properties' => [
                    Config::PLUGIN_VERSION => $this->module->version,
                    Config::PLUGIN_INSTALLED => true,
                ],
            ],
        ];
        $amplitude = Amplitude::getInstance();
        $amplitude->sendEvent($data);
    }

    public function eventUninstalled()
    {
        $data = [
            'events' => [
                'event_type' => Config::PLUGIN_UNINSTALLED,
                'user_properties' => [
                    Config::PLUGIN_VERSION => $this->module->version,
                    Config::PLUGIN_INSTALLED => false,
                ],
            ],
        ];

        $amplitude = Amplitude::getInstance();
        $amplitude->sendEvent($data);
    }

    public function installContext(): bool
    {
        if (\Shop::isFeatureActive()) {
            \Shop::setContext(\Shop::CONTEXT_SHOP, \Context::getContext()->shop->id);
        }

        return true;
    }
}
