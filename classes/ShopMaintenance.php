<?php
/**
 * Copyright (C) 2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2018 thirty bees
 * @license   Open Software License (OSL 3.0)
 */

/**
 * Class ShopMaintenance
 *
 * This class implements tasks for maintaining hte shop installation, to be
 * run on a regular schedule. It gets called by an asynchronous Ajax request
 * in DashboardController.
 *
 * @since 1.0.8
 */
class ShopMaintenanceCore
{
    /**
     * Run tasks as needed. Should take care of running tasks not more often
     * than needed and that one run takes not longer than a few seconds.
     *
     * @since 1.0.8
     */
    public static function run()
    {
        $now = time();
        $lastRun = Configuration::get('SHOP_MAINTENANCE_LAST_RUN');
        if ($now - $lastRun > 86400) {
            // Run daily tasks.
            static::adjustThemeHeaders();

            Configuration::updateGlobalValue('SHOP_MAINTENANCE_LAST_RUN', $now);
        }
    }

    /**
     * Correct the "generator" meta tag in templates. Technology detection
     * sites like builtwith.com don't recognize thirty bees technology if the
     * theme template inserts a meta tag "generator" for PrestaShop.
     *
     * @since 1.0.8
     */
    public static function adjustThemeHeaders()
    {
        foreach (scandir(_PS_ALL_THEMES_DIR_) as $themeDir) {
            if ( ! is_dir(_PS_ALL_THEMES_DIR_.$themeDir)
                || in_array($themeDir, ['.', '..'])) {
                continue;
            }

            $headerPath = _PS_ALL_THEMES_DIR_.$themeDir.'/header.tpl';
            if (is_writable($headerPath)) {
                $header = file_get_contents($headerPath);
                $newHeader = preg_replace('/<\s*meta\s*name\s*=\s*["\']generator["\']\s*content\s*=\s*["\'].*["\']\s*>/i',
                    '<meta name="generator" content="thirty bees">', $header);
                if ($newHeader !== $header) {
                    file_put_contents($headerPath, $newHeader);
                    Tools::clearSmartyCache();
                }
            }
        }
    }
}