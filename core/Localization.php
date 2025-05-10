<?php
/**
 * Localization System for multi-language support
 */
class Localization {
    private static $instance = null;
    private $strings = [];
    private $currentLang = 'en';
    private $supportedLangs = ['en', 'fr', 'ar'];
    private $rtlLangs = ['ar'];

    /**
     * Constructor - loads default language
     */
    private function __construct() {
        $config = require_once __DIR__ . '/../config/xampp.php';

        // Check if config has default_lang and set it
        if (isset($config['app']) && isset($config['app']['default_lang'])) {
            $this->currentLang = $config['app']['default_lang'];
        }

        // Load language from session if available
        if (isset($_SESSION['preferred_lang']) && in_array($_SESSION['preferred_lang'], $this->supportedLangs)) {
            $this->currentLang = $_SESSION['preferred_lang'];
        }

        $this->loadLanguage($this->currentLang);
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load language file
     */
    private function loadLanguage($lang) {
        $file = __DIR__ . "/../lang/{$lang}.php";

        if (file_exists($file)) {
            $this->strings = require $file;
        } else {
            // Fallback to English
            $this->strings = require __DIR__ . "/../lang/en.php";
        }
    }

    /**
     * Set current language
     */
    public function setLanguage($lang) {
        if (in_array($lang, $this->supportedLangs)) {
            $this->currentLang = $lang;
            $_SESSION['preferred_lang'] = $lang;
            $this->loadLanguage($lang);
            return true;
        }
        return false;
    }

    /**
     * Get current language
     */
    public function getCurrentLanguage() {
        return $this->currentLang;
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages() {
        return $this->supportedLangs;
    }

    /**
     * Check if current language is RTL
     */
    public function isRtl() {
        return in_array($this->currentLang, $this->rtlLangs);
    }

    /**
     * Get direction (ltr or rtl)
     */
    public function getDirection() {
        return $this->isRtl() ? 'rtl' : 'ltr';
    }

    /**
     * Translate a string
     */
    public function translate($key) {
        return isset($this->strings[$key]) ? $this->strings[$key] : $key;
    }

    /**
     * Shorthand for translate
     */
    public function t($key) {
        return $this->translate($key);
    }
}
