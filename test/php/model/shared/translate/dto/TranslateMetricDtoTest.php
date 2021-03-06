<?php

use Api\Model\Shared\Translate\Dto\TranslateMetricDto;
use Api\Model\Shared\Translate\TranslateDocumentSetModel;
use Api\Model\Shared\Translate\TranslateMetricModel;
use PHPUnit\Framework\TestCase;

class TranslateMetricDtoTest extends TestCase
{
    /** @var MongoTestEnvironment Local store of mock test environment */
    private static $environ;

    public function setUp()
    {
        self::$environ = new MongoTestEnvironment();
        self::$environ->clean();
    }

    public function testEncode_IpNotPresent_DtoHasCorrectGeoData()
    {
        // Create
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $userId = self::$environ->createUser('User', 'Name', 'name@example.com');
        $documentSet = new TranslateDocumentSetModel($project);
        $documentSet->name = 'SomeDocument';
        $documentSetId = $documentSet->write();
        $metric = new TranslateMetricModel($project, '', $documentSetId, $userId);
        $metric->metrics->mouseClickCount = 1;
        $metric->write();
        $ipAddress = '0.0.0.0';
        $_SERVER['REMOTE_ADDR'] = $ipAddress;

        $dto = TranslateMetricDto::encode($metric, $project, false);

        // test for a few default values
        $this->assertArrayNotHasKey('id', $dto);
        $this->assertFalse($dto['isTestData']);
        $this->assertEquals($ipAddress, $dto['ipAddress']);
        $this->assertArrayNotHasKey('geoCountryIsoCode', $dto);
        $this->assertArrayNotHasKey('geoLocation', $dto);
    }

    public function testEncode_EmptyIp_DtoHasCorrectGeoData()
    {
        // Create
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $userId = self::$environ->createUser('User', 'Name', 'name@example.com');
        $documentSet = new TranslateDocumentSetModel($project);
        $documentSet->name = 'SomeDocument';
        $documentSetId = $documentSet->write();
        $metric = new TranslateMetricModel($project, '', $documentSetId, $userId);
        $metric->metrics->mouseClickCount = 1;
        $metric->write();

        $dto = TranslateMetricDto::encode($metric, $project, false);

        // test for a few default values
        $this->assertArrayNotHasKey('id', $dto);
        $this->assertFalse($dto['isTestData']);
        $this->assertEquals('', $dto['ipAddress']);
        $this->assertArrayNotHasKey('geoCountryIsoCode', $dto);
        $this->assertArrayNotHasKey('geoLocation', $dto);
    }

    public function testEncode_Metric_DtoHasCorrectGeoData()
    {
        // Create
        $project = self::$environ->createProject(SF_TESTPROJECT, SF_TESTPROJECTCODE);
        $userId = self::$environ->createUser('User', 'Name', 'name@example.com');
        $documentSet = new TranslateDocumentSetModel($project);
        $documentSet->name = 'SomeDocument';
        $documentSetId = $documentSet->write();
        $metric = new TranslateMetricModel($project, '', $documentSetId, $userId);
        $metric->metrics->mouseClickCount = 1;
        $metric->write();
        $ipAddress = '110.77.202.231';
        $_SERVER['REMOTE_ADDR'] = $ipAddress;

        $dto = TranslateMetricDto::encode($metric, $project, true);

        // test for a few default values
        $this->assertArrayNotHasKey('id', $dto);
        $this->assertTrue($dto['isTestData']);
        $this->assertEquals($ipAddress, $dto['ipAddress']);
        if (file_exists(TranslateMetricDto::GEO_CITY_DB_FILE_PATH)) {
            $this->assertEquals('TH', $dto['geoCountryIsoCode']);
            $this->assertInternalType('float', $dto['geoLocation']['lat']);
            $this->assertInternalType('float', $dto['geoLocation']['lon']);
        }
    }

}
