<?php
/**
 * Created by PhpStorm.
 * User: darkfriend <hi@darkfriend.ru>
 * Date: 05.01.2020
 * Time: 3:19
 */

namespace darkfriend\helpers;


class LogRotate
{
    /**
     * Rotate process
     * @param string $file path to file
     * @param int $size size in Mb
     * @return bool
     */
    public static function process($file, $size=30)
    {
        if(!static::fileIsValid($file)) {
            return false;
        }
        if (!static::checkProcessFileSize($file, $size)) {
            return false;
        }

        clearstatcache();

        $fileInfo = static::getFileInfo($file);

        $fd = fopen($file, 'r+');

        if (!$fd) {
            return false;
        }

        if (!flock($fd, LOCK_EX)) {
            flock($fd, LOCK_UN);
            fclose($fd);
            return false;
        }

        $glob = $fileInfo['dirname'].'/'.$fileInfo['filename'];
        $curFiles = glob($glob.'*');

        if(!$curFiles) {
            $countFiles = 1;
        } else {
            $countFiles = count($curFiles);
        }

        $copied = copy($file, $glob."-$countFiles.{$fileInfo['extension']}");

        if(!$copied) {
            flock($fd, LOCK_UN);
            fclose($fd);
            return false;
        }

        if(!ftruncate($fd, 0)) {
            flock($fd, LOCK_UN);
            fclose($fd);
            unlink($file);
            return true;
        }

        flock($fd, LOCK_UN);
        fflush($fd);
        fclose($fd);

        return $copied;
    }

    /**
     * Check start rotate process
     * @param string $file
     * @param int $size
     * @return bool
     */
    protected static function checkProcessFileSize($file, $size=30)
    {
        clearstatcache();
        $sizeFile = filesize($file);

        if(!$sizeFile) return false;

        $sizeFile /= pow(1024, 2);

        if($sizeFile<$size) {
            return false;
        }

        return true;
    }

    /**
     * Check file is valid
     * @param string $file
     * @return bool
     */
    protected static function fileIsValid($file)
    {
        return is_file($file) && is_writable($file);
    }

    /**
     * Get file information
     * @param string $file
     * @return array [
     *      'dirname' => '/www/htdocs/inc',
     *      'basename' => 'lib.inc.php',
     *      'extension' => 'php',
     *      'filename' => 'lib.inc',
     * ]
     */
    protected static function getFileInfo($file)
    {
        return pathinfo($file);
    }
}