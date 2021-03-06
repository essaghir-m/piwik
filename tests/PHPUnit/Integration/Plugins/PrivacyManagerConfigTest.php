<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;

/**
 * Class Plugins_SitesManagerTest
 *
 * @group Plugins
 */
class Plugins_PrivacyManagerConfigTest extends DatabaseTestCase
{
    /**
     * @var PrivacyManagerConfig
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

        $this->config = new PrivacyManagerConfig();
    }

    public function test_useAnonymizedIpForVisitEnrichment()
    {
        $this->assertTrue($this->config->useAnonymizedIpForVisitEnrichment);

        $this->config->useAnonymizedIpForVisitEnrichment = false;

        $this->assertFalse($this->config->useAnonymizedIpForVisitEnrichment);

        $this->config->useAnonymizedIpForVisitEnrichment = true;

        $this->assertTrue($this->config->useAnonymizedIpForVisitEnrichment);
    }

    public function test_doNotTrackEnabled()
    {
        $this->assertTrue($this->config->doNotTrackEnabled);

        $this->config->doNotTrackEnabled = true;

        $this->assertTrue($this->config->doNotTrackEnabled);

        $this->config->doNotTrackEnabled = false;

        $this->assertFalse($this->config->doNotTrackEnabled);
    }

    public function test_ipAnonymizerEnabled()
    {
        $this->assertFalse($this->config->ipAnonymizerEnabled);

        $this->config->ipAnonymizerEnabled = true;

        $this->assertTrue($this->config->ipAnonymizerEnabled);
    }

    public function test_ipAddressMaskLength()
    {
        $this->assertSame(1, $this->config->ipAddressMaskLength);

        $this->config->ipAddressMaskLength = '19';

        $this->assertSame(19, $this->config->ipAddressMaskLength);
    }

    public function test_setTrackerCacheContent()
    {
        $content = $this->config->setTrackerCacheGeneral(array('existingEntry' => 'test'));

        $expected = array(
            'existingEntry' => 'test',
            'PrivacyManager.ipAddressMaskLength' => 1,
            'PrivacyManager.ipAnonymizerEnabled' => false,
            'PrivacyManager.doNotTrackEnabled'   => true,
            'PrivacyManager.useAnonymizedIpForVisitEnrichment' => true,
        );

        $this->assertEquals($expected, $content);
    }

    public function test_setTrackerCacheContent_ShouldGetValuesFromConfig()
    {
        Option::set('PrivacyManager.ipAddressMaskLength', '232');

        $content = $this->config->setTrackerCacheGeneral(array('existingEntry' => 'test'));

        $this->assertEquals(232, $content['PrivacyManager.ipAddressMaskLength']);
    }

}
