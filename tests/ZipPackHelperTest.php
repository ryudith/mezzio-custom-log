<?php

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Test;

use PHPUnit\Framework\TestCase;
use Ryudith\MezzioCustomLog\Helper\Zip;

class ZipPackHelperTest extends TestCase
{
    private static string $dest;
    private static string $srcDir;

    public static function setUpBeforeClass () : void
    {
        self::$dest = './tests/samples/zip/log_'.date('Ymd').'.zip';
        self::$srcDir = './tests/samples/log';
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
        $this->assertTrue($result);

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
    }

    public function testZipLogFileWithSameLocation ()
    {
        self::$dest = './tests/samples/log/log_'.date('Ymd').'.zip';
        $result = Zip::packLog(self::$dest, self::$srcDir);
        $this->assertTrue($result);

        if ($result) 
        {
            $this->assertFileExists(self::$dest);
        }
    }

    public function testZipLogFileWithStartTimeOnly ()
    {
        $result = Zip::packLog(self::$dest, self::$srcDir, date('Y-m-1 00:00:00'));
        $this->assertTrue($result);

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
    }

    public function testZipLogFileWithStartTimeAndEndTime ()
    {
        $result = Zip::packLog(self::$dest, self::$srcDir, date('Y-m-1 00:00:00'), date('Y-m-t 23:59:59'));
        $this->assertTrue($result);

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
    }

    public function testZipLogFileWithEndTimeOnly ()
    {
        $nextMonthTime = strtotime('+1 month');
        $result = Zip::packLog(self::$dest, self::$srcDir, end:date('Y-m-1 00:00:00', $nextMonthTime));
        $this->assertTrue($result);

        if ($result)
        {
            $this->assertFileExists(self::$dest);
        }
    }

    public function testErrorZipLogFileWithEndTimeSetPast ()
    {
        $this->expectException('Exception');
        $pastTime = strtotime('-2 day');
        Zip::packLog(self::$dest, self::$srcDir, end:date('Y-m-d 00:00:00', $pastTime));
    }

    private function deleteOutputFile ()
    {
        if (file_exists(self::$dest)) 
        {
            unlink(self::$dest);
        }
    }
}