<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\ScheduledTask;
use Piwik\ScheduledTime;
use Piwik\Settings\UserSetting;

/**
 *
 */
class CoreAdminHome extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
            'UsersManager.deleteUser'         => 'cleanupUser'
        );
    }

    public function cleanupUser($userLogin)
    {
        UserSetting::removeAllUserSettingsForUser($userLogin);
    }

    public function getScheduledTasks(&$tasks)
    {
        // general data purge on older archive tables, executed daily
        $purgeArchiveTablesTask = new ScheduledTask ($this,
            'purgeOutdatedArchives',
            null,
            ScheduledTime::factory('daily'),
            ScheduledTask::HIGH_PRIORITY);
        $tasks[] = $purgeArchiveTablesTask;

        // lowest priority since tables should be optimized after they are modified
        $optimizeArchiveTableTask = new ScheduledTask ($this,
            'optimizeArchiveTable',
            null,
            ScheduledTime::factory('daily'),
            ScheduledTask::LOWEST_PRIORITY);
        $tasks[] = $optimizeArchiveTableTask;
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "libs/jquery/themes/base/jquery-ui.css";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/menu.less";
        $stylesheets[] = "plugins/Morpheus/stylesheets/base.less";
        $stylesheets[] = "plugins/Morpheus/stylesheets/theme.less";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/generalSettings.less";
        $stylesheets[] = "plugins/CoreAdminHome/stylesheets/pluginSettings.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/jquery/jquery.js";
        $jsFiles[] = "libs/jquery/jquery-ui.js";
        $jsFiles[] = "libs/jquery/jquery.browser.js";
        $jsFiles[] = "libs/javascript/sprintf.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/ajaxHelper.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/jquery.icheck.min.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/morpheus.js";
        $jsFiles[] = "libs/jquery/jquery.history.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/broadcast.js";
        $jsFiles[] = "plugins/CoreAdminHome/javascripts/generalSettings.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/donate.js";
        $jsFiles[] = "plugins/CoreAdminHome/javascripts/pluginSettings.js";
    }

    function purgeOutdatedArchives()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            // Somehow we may have archive tables created with older dates, prevent exception from being thrown
            if($year > 1990) {
                ArchiveSelector::purgeOutdatedArchives(Date::factory("$year-$month-15"));
            }
        }
    }

    function optimizeArchiveTable()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        Db::optimizeTables($archiveTables);
    }
}
