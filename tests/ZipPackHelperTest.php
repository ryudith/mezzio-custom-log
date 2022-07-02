<?php

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Test;

use PHPUnit\Framework\TestCase;
use Ryudith\MezzioCustomLog\Helper\Zip;

class ZipPackHelperTest extends TestCase
{
    private static string $dest;
    private static string $srcDir;
    private static array $errorMessage;

    public static function setUpBeforeClass () : void
    {
        self::$dest = './tests/samples/zip/log_'.date('Ymd').'.zip';
        self::$srcDir = './tests/samples/log';
        self::$errorMessage = [
            'No file can put into ZIP!',
            'Destination file name already exists!',
            'Can not create destination directory!',
            'Can not open ZIP file!',
            'No file can put into ZIP!',
        ];
    }

    public function setUp () : void
    {
        $this->deleteOutputFile();
    }

    public function tearDown () : void 
    {
        $this->deleteOutputFile();
    }

    public function testZipLogFileWithDifferentLocation ()
    {
        $result = Zip::packLog(self::$dest, self::$srcDir);
        echo "\tProcess result : ".($result ? 'true' : 'false')."\n";
        echo "\tWith message : ".Zip::$errorMessage."\n\n";

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
        else
        {
            $this->assertContains(Zip::$errorMessage, self::$errorMessage);
        }
    }

    public function testZipLogFileWithSameLocation ()
    {
        self::$dest = './tests/samples/log/log_'.date('Ymd').'.zip';
        $result = Zip::packLog(self::$dest, self::$srcDir);
        echo "\tProcess result : ".($result ? 'true' : 'false')."\n";
        echo "\tWith message : ".Zip::$errorMessage."\n\n";

        if ($result) 
        {
            $this->assertFileExists(self::$dest);
        }
        else
        {
            $this->assertContains(Zip::$errorMessage, self::$errorMessage);
        }
    }

    public function testZipLogFileWithStartTimeOnly ()
    {
        $result = Zip::packLog(self::$dest, self::$srcDir, date('Y-m-1 00:00:00'));
        echo "\tProcess result : ".($result ? 'true' : 'false')."\n";
        echo "\tWith message : ".Zip::$errorMessage."\n\n";

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
        else
        {
            $this->assertContains(Zip::$errorMessage, self::$errorMessage);
        }
    }

    public function testZipLogFileWithStartTimeAndEndTime ()
    {
        $result = Zip::packLog(self::$dest, self::$srcDir, date('Y-m-1 00:00:00'), date('Y-m-t 23:59:59'));
        echo "\tProcess result : ".($result ? 'true' : 'false')."\n";
        echo "\tWith message : ".Zip::$errorMessage."\n\n";

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
        else
        {
            $this->assertContains(Zip::$errorMessage, self::$errorMessage);
        }
    }

    public function testZipLogFileWithEndTimeOnly ()
    {
        $nextMonthTime = strtotime('+1 month');
        $result = Zip::packLog(self::$dest, self::$srcDir, end:date('Y-m-1 00:00:00', $nextMonthTime));
        echo "\tProcess result : ".($result ? 'true' : 'false')."\n";
        echo "\tWith message : ".Zip::$errorMessage."\n\n";

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
        else
        {
            $this->assertContains(Zip::$errorMessage, self::$errorMessage);
        }
    }

    public function testErrorZipLogFileWithEndTimeSetPast ()
    {
        $this->expectException('Exception');
        $pastTime = strtotime('-2 day');
        $result = Zip::packLog(self::$dest, self::$srcDir, end:date('Y-m-d 00:00:00', $pastTime));
        echo "\tProcess result : ".($result ? 'true' : 'false')."\n";
        echo "\tWith message : ".Zip::$errorMessage."\n\n";
    }

    private function deleteOutputFile ()
    {
        if (file_exists(self::$dest)) 
        {
            unlink(self::$dest);
        }
    }
}