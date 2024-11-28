<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

if (!class_exists('Przelewy24Installer', false)) {
    /**
     * Class Przelewy24Installer
     */
    class Przelewy24Installer implements Przelewy24Interface
    {
        /**
         * Array with transations.
         *
         * @var array
         */
        private $translations;

        /**
         * Is slider enabled.
         *
         * @var bool
         */
        private $sliderEnabled = true;

        /**
         * Installer pages.
         *
         * @var array
         */
        private $pages = [];

        /**
         * Przelewy24Installer constructor.
         *
         * @param bool $sliderEnabled
         * @param array $translations
         */
        public function __construct($sliderEnabled = true, array $translations = [])
        {
            $this->sliderEnabled = $sliderEnabled;
            $this->setTranslations($translations);
        }

        /**
         * Set translations.
         *
         * @param array $translations
         */
        public function setTranslations(array $translations = [])
        {
            $this->translations = $translations;

            // set default values
            if (empty($this->translations['php_version'])) {
                $this->translations['php_version'] = 'Wersja PHP min. 5.2';
            }
            if (empty($this->translations['curl_version'])) {
                $this->translations['curl_enabled'] = 'Włączone rozszerzenie PHP cURL (php_curl.dll)';
            }

            if (empty($this->translations['merchant_id'])) {
                $this->translations['merchant_id'] = 'ID sprzedawcy';
            }
            if (empty($this->translations['shop_id'])) {
                $this->translations['shop_id'] = 'ID sklepu';
            }
            if (empty($this->translations['crc_key'])) {
                $this->translations['crc_key'] = 'Klucz CRC';
            }
            if (empty($this->translations['api_key'])) {
                $this->translations['api_key'] = 'Klucz API';
            }
        }

        /**
         * Add pages.
         *
         * @param array $pages
         */
        public function addPages(array $pages = [])
        {
            $this->pages = array_values($pages);
        }

        /**
         * Render installer steps.
         *
         * @return string
         *
         * @throws Exception
         */
        public function renderInstallerSteps()
        {
            if (!$this->sliderEnabled || empty($this->pages) || !is_array($this->pages)) {
                return '';
            }

            $requirements = $this->checkRequirements();
            $params = [
                'requirements' => $requirements,
                'translations' => $this->translations,
            ];
            $maxSteps = 0;
            $data = [
                'steps' => [],
            ];
            foreach ($this->pages as $page) {
                $page = (int) $page;
                if ($page > 0) {
                    $step = $this->loadStep($page, $params);
                    $data['steps'][$page] = $step;
                    ++$maxSteps;
                }
            }

            if (0 === $maxSteps) {
                return '';
            }
            $data['maxSteps'] = $maxSteps;

            return $this->loadTemplate('installer', $data);
        }

        /**
         * Load step.
         *
         * @param int $number step number
         * @param array|null $params
         *
         * @return string
         *
         * @throws Exception
         */
        private function loadStep($number, $params = null)
        {
            $step = $this->loadTemplate('step' . $number, $params);
            $step = $this->removeNewLines($step);

            return $step;
        }

        /**
         * Remove new lines ("\n", "\r") from string.
         *
         * @param string $string
         *
         * @return string
         */
        private function removeNewLines($string)
        {
            return trim(str_replace(PHP_EOL, ' ', $string));
        }

        /**
         * Loads template.
         *
         * @param string $view
         * @param array|null $data
         *
         * @return string
         *
         * @throws Exception
         */
        private function loadTemplate($view, $data = null)
        {
            extract(['content' => $data]);
            ob_start();
            $viewFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . "$view.tpl.php";

            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                throw new Exception('View not exist in ' . get_class($this));
            }
            $content = ob_get_clean();

            return $content;
        }

        /**
         * Check requirements.
         *
         * @return array
         */
        private function checkRequirements()
        {
            $data = [
                'php' => [
                    'test' => (version_compare(PHP_VERSION, '5.2.0') > 0),
                    'label' => $this->translations['php_version'],
                ],
                'curl' => [
                    'test' => function_exists('curl_version'),
                    'label' => $this->translations['curl_enabled'],
                ],
            ];

            return $data;
        }
    }
}
