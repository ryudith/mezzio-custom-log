<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Helper;

use DirectoryIterator;
use Exception;
use ZipArchive;

/**
 * Main class for ZIP helper functionality.
 */
class Zip
{
    /**
     * @var string $errorMessage
     */
    public static string $errorMessage = '';
    
    /**
     * Do pack(ZIP) log files.
     * 
     * @param string $destFilename Full path file name.
     * @param string $srcPath Directory to search log for ZIP.
     * @param ?string $start Minimum datetime modify file to add to ZIP file.
     * @param ?string $end Maximum datetime modify file to add to ZIP file.
     * @return bool Process result.
     */
    public static function packLog (string $destFilename, string $srcPath, ?string $start = null, ?string $end = null) : bool
    {
        if (file_exists($destFilename))
        {
            self::$errorMessage = 'Destination file name already exists!';
            return false;
        }
        
        $destDirName = dirname($destFilename);
        if (! self::checkDirectory($destDirName))
        {
            self::$errorMessage = 'Can not create destination directory!';
            return false;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($destFilename, ZipArchive::CREATE) !== true)
        {
            self::$errorMessage = 'Can not open ZIP file!';
            return false;
        }

        list($startTime, $endTime) = self::checkStartEndTime($start, $end);
        
        $srcPath = rtrim($srcPath, '/').'/';
        $srcDir = new DirectoryIterator($srcPath);
        $zipFilename = basename($destFilename);
        $counterZipFile = 0;
        while ($srcDir->valid()) {
            $fileName = $srcDir->getFilename();
            if ($fileName === '.' || $fileName === '..')
            {
                $srcDir->next();
                continue;
            }

            try 
            {
                $lastAccessTime = $srcDir->getCTime();
                if ($startTime <= $lastAccessTime && $endTime > $lastAccessTime && $zipFilename !== $fileName)
                {
                    $zip->addFile($srcPath.$fileName, $fileName);
                    $counterZipFile++;
                }

                $srcDir->next();
            } 
            catch (Exception $e) 
            {
                // if read metadata file fail, it will throw exception. so we skip that file.
                $srcDir->next();
            }
        }

        $processResult = $zip->close();
        if ($counterZipFile > 0 && $processResult)
        {
            return true;
        }

        self::$errorMessage = 'No file can put into ZIP!';
        return false;
    }

    /**
     * Check directory, if not exists then create recursively.
     * 
     * @param string $dir Directory to check.
     * @param bool $autoCreate Flag for auto create directory if not exists.
     * @param int $permission Permission mode when create directory.
     * @return bool Check result.
     */
    private static function checkDirectory (string $dir, bool $autoCreate = true, int $permission = 0755) : bool
    {
        $isDirExist = file_exists($dir) && is_dir($dir);
        if (! $isDirExist && ! $autoCreate)
        {
            return false;
        }

        if (! $isDirExist)
        {
            return mkdir($dir, $permission, true);
        }

        return true;
    }

    /**
     * Check start and end time filter.
     * If any invalid value then set either with default value or throw exception.
     * 
     * @param ?string $start Start datetime filter.
     * @param ?string $end End datetime filter.
     * @return array Return array 2 elements [0] start and [1] end
     */
    private static function checkStartEndTime (?string $start = null, ?string $end = null) : array
    {
        $baseTime = strtotime(date('Y-m-d 00:00:00'));
        $startTime = null;
        if ($start === null)
        {
            $startTime = $baseTime;
        }
        else
        {
            $startTime = strtotime($start);
        }

        $endTime = null;
        if ($end === null)
        {
            $endTime = strtotime('+1 day', $baseTime);
        }
        else 
        {
            $endTime = strtotime($end);
        }

        if ($endTime < $startTime) 
        {
            throw new Exception('Start time is greater then end time!');
        }

        return [$startTime, $endTime];
    }
}