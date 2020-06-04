<?php
namespace org\helper;
use think\Exception;
use ZipArchive;

/**
 * 文件、目录操作
 *
 * Class FileHelper
 * @since v1.0.0
 * @author ranj
 * @package org\helper
 */
class FileHelper{
    /**
     * @param $path
     * @param bool $recursive
     * @throws Exception
     */
    public static function rmdir($path, $recursive = false) {
        self::delete($path, $recursive);
    }


    /**
     * @param $source_dir
     * @param $target_dir
     * @param bool $overWrite
     * @throws Exception
     */
    public static function copyDir($source_dir, $target_dir, $overWrite = true) {
        $source_dir = str_replace( '\\', '/', $source_dir );
        $target_dir = str_replace( '\\', '/', $target_dir );
        if(empty($source_dir)||empty($target_dir)){
            return;
        }

        if (!self::is_dir($source_dir)) {
            return;
        }

        self::make_writeable_dir($target_dir);

        $dirHandle = @opendir($source_dir);
        if (!$dirHandle) {
            throw new Exception("拷贝目录时发生异常：源目录读取失败！{$source_dir}");
        }

        while (false !== ($file = @readdir($dirHandle))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!is_dir($source_dir.'/' . $file)) {
                self::copyFile($source_dir.'/' . $file, $target_dir .'/' . $file, $overWrite);
            } else {
                self::copyDir($source_dir .'/' .  $file, $target_dir .'/' . $file, $overWrite);
            }
        }

        @closedir($dirHandle);
    }

    /**
     * @param $fileUrl
     * @param $aimUrl
     * @param bool $overWrite
     * @throws Exception
     */
    public static function copyFile($fileUrl, $aimUrl, $overWrite = true) {
        if (!file_exists($fileUrl)) {
            return;
        }

        if (file_exists($aimUrl)) {
            if (! $overWrite ) {
                return;
            }

            if(! @unlink($aimUrl)){
                throw new Exception("拷贝文件时发生异常:文件已存在，无法移除{$aimUrl}");
            }
        }

        $_dir = @dirname($aimUrl);
        self::make_writeable_dir($_dir);

        if(!@copy($fileUrl, $aimUrl)){
            throw new Exception("拷贝文件时发生异常:拷贝异常{$aimUrl}");
        }
    }
    /**
     * @param $source_dir
     * @param $target_dir
     * @param bool $overWrite
     * @throws Exception
     */
    public static function moveDir($source_dir, $target_dir, $overWrite = true) {
        $source_dir = str_replace( '\\', '/', $source_dir );
        $target_dir = str_replace( '\\', '/', $target_dir );
        if(empty($source_dir)||empty($target_dir)){
            return;
        }

        if (!self::is_dir($source_dir)) {
            return;
        }

        self::make_writeable_dir($target_dir);
        
        $dirHandle = @opendir($source_dir);
        if (!$dirHandle) {
            throw new Exception("拷贝目录时发生异常：源目录读取失败！{$source_dir}");
        }

        while (false !== ($file = @readdir($dirHandle))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!is_dir($source_dir . $file)) {
                self::moveFile($source_dir . $file, $target_dir . $file, $overWrite);
            } else {
                self::moveDir($source_dir . $file, $target_dir . $file, $overWrite);
            }
        }

        @closedir($dirHandle);
        if(!@rmdir($source_dir)){
            throw new Exception("拷贝目录时发生异常：源目录删除失败！{$source_dir}");
        }
    }

    /**
     * @param $fileUrl
     * @param $aimUrl
     * @param bool $overWrite
     * @throws Exception
     */
    public static function moveFile($fileUrl, $aimUrl, $overWrite = true) {
        if (!file_exists($fileUrl)) {
            return;
        }

        if (file_exists($aimUrl)) {
            if (! $overWrite ) {
                return;
            }

            if(! @unlink($aimUrl)){
                throw new Exception("拷贝文件时发生异常:文件已存在，无法移除{$aimUrl}");
            }
        }

        $_dir = @dirname($aimUrl);
        self::make_writeable_dir($_dir);

        if(!@rename($fileUrl, $aimUrl)){
            throw new Exception("拷贝文件时发生异常:重命名异常{$aimUrl}");
        }
    }

    /**
     * @param $file
     * @param bool $recursive 是否递归
     * @param bool $type
     * @throws Exception
     */
    public static function delete($file, $recursive = true, $type = false) {
        if ( empty( $file ) ) // Some filesystems report this as /, which can cause non-expected recursive deletion of all files in the filesystem.
            return;

        $file = str_replace( '\\', '/', $file ); // for win32, occasional problems deleting files otherwise
        if ( 'f' == $type || self::is_file($file) ){
            if(!@unlink($file)){
                throw new Exception("移除文件时发生异常:{$file}无法移除！");
            }
            return;
        }

        if ( ! $recursive && self::is_dir($file) ){
            if(!@rmdir($file)){
                throw new Exception("移除目录时发生异常:{$file}无法移除！");
            }
            return;
        }

        // At this point it's a folder, and we're in recursive mode
        $file = self::trailingslashit($file);
        $filelist = self::dirList($file, true);

        if ( is_array( $filelist ) ) {
            foreach ( $filelist as $filename => $fileinfo ) {
                self::delete($file . $filename, $recursive, $fileinfo['type']) ;
            }
        }

        if ( file_exists($file) && ! @rmdir($file) ){
            throw new Exception("异常文件时发生异常：{$file}移除失败！");
        }
    }

    /**
     * @param $file
     * @param $to
     * @throws Exception
     */
    public static function unzip($file, $to){
        if (class_exists('\ZipArchive', false)) {
            self::_unzip_file_ziparchive($file, $to);
            return;
        }

    }

    private static  function dirList($path, $include_hidden = true, $recursive = false) {
        if ( @is_file($path) ) {
            $limit_file = @basename($path);
            $path = @dirname($path);
        } else {
            $limit_file = false;
        }

        if ( ! @is_dir($path) )
            return false;

        $dir = @dir($path);
        if ( ! $dir )
            return false;

        $ret = array();

        while (false !== ($entry = $dir->read()) ) {
            $struc = array();
            $struc['name'] = $entry;

            if ( '.' == $struc['name'] || '..' == $struc['name'] )
                continue;

            if ( ! $include_hidden && '.' == $struc['name'][0] )
                continue;

            if ( $limit_file && $struc['name'] != $limit_file)
                continue;

            $struc['perms'] 	= self::get_h_chmod($path.'/'.$entry);
            $struc['permsn']	= self::getnumchmodfromh($struc['perms']);
            $struc['number'] 	= false;
            $struc['owner']    	= self::owner($path.'/'.$entry);
            $struc['group']    	= self::group($path.'/'.$entry);
            $struc['size']    	= self::size($path.'/'.$entry);
            $struc['lastmodunix']= self::mtime($path.'/'.$entry);
            $struc['lastmod']   = date('M j',$struc['lastmodunix']);
            $struc['time']    	= date('h:i:s',$struc['lastmodunix']);
            $struc['type']		= self::is_dir($path.'/'.$entry) ? 'd' : 'f';

            if ( 'd' == $struc['type'] ) {
                if ( $recursive )
                    $struc['files'] = self::dirList($path . '/' . $struc['name'], $include_hidden, $recursive);
                else
                    $struc['files'] = array();
            }

            $ret[ $struc['name'] ] = $struc;
        }
        $dir->close();
        unset($dir);
        return $ret;
    }

    private static  function is_file($file) {
        return @is_file($file);
    }
    /**
     *
     * @param string $path
     * @return bool
     */
    private static function is_dir($path) {
        return @is_dir($path);
    }

   
    private static  function mtime($file) {
        return @filemtime($file);
    }

    private static  function size($file) {
        return @filesize($file);
    }

    private static  function group($file) {
        $gid = @filegroup($file);
        if ( ! $gid )
            return false;
        if ( ! function_exists('posix_getgrgid') )
            return $gid;
        $grouparray = posix_getgrgid($gid);
        return $grouparray['name'];
    }

    private static  function owner($file) {
        $owneruid = @fileowner($file);
        if ( ! $owneruid )
            return false;
        if ( ! function_exists('posix_getpwuid') )
            return $owneruid;
        $ownerarray = posix_getpwuid($owneruid);
        return $ownerarray['name'];
    }

    private static  function getnumchmodfromh( $mode ) {
        $realmode = '';
        $legal =  array('', 'w', 'r', 'x', '-');
        $attarray = preg_split('//', $mode);

        for ( $i = 0, $c = count( $attarray ); $i < $c; $i++ ) {
            if ($key = array_search($attarray[$i], $legal)) {
                $realmode .= $legal[$key];
            }
        }

        $mode = str_pad($realmode, 10, '-', STR_PAD_LEFT);
        $trans = array('-'=>'0', 'r'=>'4', 'w'=>'2', 'x'=>'1');
        $mode = strtr($mode,$trans);

        $new_mode = $mode[0];
        $new_mode .= $mode[1] + $mode[2] + $mode[3];
        $new_mode .= $mode[4] + $mode[5] + $mode[6];
        $new_mode .= $mode[7] + $mode[8] + $mode[9];
        return $new_mode;
    }

    private static  function get_chmod( $file ) {
        return '777';
    }

    private static  function get_h_chmod( $file ){
        $perms = intval( self::get_chmod( $file ), 8 );
        if (($perms & 0xC000) == 0xC000) // Socket
            $info = 's';
        elseif (($perms & 0xA000) == 0xA000) // Symbolic Link
            $info = 'l';
        elseif (($perms & 0x8000) == 0x8000) // Regular
            $info = '-';
        elseif (($perms & 0x6000) == 0x6000) // Block special
            $info = 'b';
        elseif (($perms & 0x4000) == 0x4000) // Directory
            $info = 'd';
        elseif (($perms & 0x2000) == 0x2000) // Character special
            $info = 'c';
        elseif (($perms & 0x1000) == 0x1000) // FIFO pipe
            $info = 'p';
        else // Unknown
            $info = 'u';

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));
        return $info;
    }

    private static  function trailingslashit( $string ) {
        return self::untrailingslashit( $string ) . '/';
    }

    private static  function mbstring_binary_safe_encoding( $reset = false ) {
        static $encodings = array();
        static $overloaded = null;

        if ( is_null( $overloaded ) )
            $overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );

        if ( false === $overloaded )
            return;

        if ( ! $reset ) {
            $encoding = mb_internal_encoding();
            array_push( $encodings, $encoding );
            mb_internal_encoding( 'ISO-8859-1' );
        }

        if ( $reset && $encodings ) {
            $encoding = array_pop( $encodings );
            mb_internal_encoding( $encoding );
        }
    }

    private static  function reset_mbstring_encoding() {
        self::mbstring_binary_safe_encoding( true );
    }

    private static  function untrailingslashit( $string ) {
        return rtrim( $string, '/\\' );
    }

    public static function make_writeable_dir($dir)
    {
        if (! @is_dir($dir)) {
            if (! @mkdir($dir, 0777, true)) {
                throw new Exception("目录创建失败：{$dir}");
            }
        }
    }

    /**
     * @param $file
     * @param $to
     * @throws Exception
     */
    private  static function _unzip_file_ziparchive($file, $to)
    {
        if (! class_exists('\ZipArchive')) {
            throw new Exception('PHP ZipArchive is missing!');
        }

        $z = new \ZipArchive();
        $error = $z->open($file, \ZIPARCHIVE::CHECKCONS);
        if ($error!==true&& $error!=ZIPARCHIVE::ER_OK) {
            throw new Exception('ziparchive error:incompatible archive:errcode:'.$error);
        }

        try {
            $uncompressed_size = 0;
            $needed_dirs=array();
            for ($i = 0; $i < $z->numFiles; $i ++) {
                if (! $info = $z->statIndex($i)) {
                    throw new Exception('ziparchive error:Could not retrieve file from archive.');
                }

                if ('__MACOSX/' === substr($info['name'], 0, 9)) { // Skip the OS X-created __MACOSX directory
                    continue;
                }

                $uncompressed_size += $info['size'];

                if ('/' === substr($info['name'], - 1)) {
                    // Directory.
                    $needed_dirs[] = $to . self::untrailingslashit($info['name']);
                } elseif ('.' !== $dirname = dirname($info['name'])) {
                    // Path to a file.
                    $needed_dirs[] = $to . self::untrailingslashit($dirname);
                }
            }

            $needed_dirs = array_unique($needed_dirs);
            foreach ($needed_dirs as $dir) {
                // Check the parent folders of the folders all exist within the creation array.
                if (self::untrailingslashit($to) == $dir) // Skip over the working directory, We know this exists (or will exist)
                    continue;
                if (strpos($dir, $to) === false) // If the directory is not within the working directory, Skip it
                    continue;

                $parent_folder = dirname($dir);
                while (! empty($parent_folder) && self::untrailingslashit($to) != $parent_folder && ! in_array($parent_folder, $needed_dirs)) {
                    $needed_dirs[] = $parent_folder;
                    $parent_folder = dirname($parent_folder);
                }
            }
            asort($needed_dirs);

            // Create those directories if need be:
            foreach ($needed_dirs as $_dir) {
                self::make_writeable_dir($_dir);
            }
            unset($needed_dirs);

            for ($i = 0; $i < $z->numFiles; $i ++) {
                if (! $info = $z->statIndex($i)) {
                    throw new Exception('ziparchive error:Could not retrieve file from archive.');
                }

                if ('/' == substr($info['name'], - 1)) // directory
                    continue;

                if ('__MACOSX/' === substr($info['name'], 0, 9)) // Don't extract the OS X-created __MACOSX directory files
                    continue;

                $contents = $z->getFromIndex($i);
                if (false === $contents) {
                    throw new Exception("ziparchive error:Could not retrieve file from archive({$info['name']}).");
                }

                try {
                    $new_file = $to . $info['name'];
                    if (file_exists($new_file)) {
                        $result = @unlink($new_file);
                        if (! $result) {
                            throw new Exception("ziparchive error:Could not remove exists file({$new_file}).");
                        }
                    }

                    $myfile = @fopen($new_file, "w");
                    if(!$myfile){
                        throw new Exception("ziparchive error:Could not unzip file to new dictionary({$new_file}).");
                    }

                    if(!@fwrite($myfile, $contents)){
                        throw new Exception("ziparchive error:Could not unzip file to new dictionary({$new_file}).");
                    }

                    if(!@fclose($myfile)){
                        throw new Exception("ziparchive error:Can not free file handler({$new_file}).");
                    }
                } catch (\Exception $e) {
                    throw new Exception("ziparchive error:Could not create new file({$new_file}).");
                }
            }
        } catch (\Exception $e) {
            if ($z) {
                $z->close();
            }

            throw $e;
        }

        $z->close();
    }
}