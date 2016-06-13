<?php
/**
 * Suco_File_Folder 类, 目录操作封装
 *
 * @version		3.0 2009/09/01
 * @author		Eric Yu (blueflu@live.cn)
 * @copyright	Copyright (c) 2009, Suconet, Inc.
 * @package		File
 * @license		http://www.suconet.com/license
 * -----------------------------------------------------------
 */

class Suco_File
{
	/**
	 * 批量上传
	 * @param  string $file 文件
	 * @param  string $dest 目标路径
	 * @param  string $allowExt 允许格式
	 * @param  string $maxSize 允许容量
	 * @return  string
	 */
	public static function multiUpload($files, $dest = 'uploads/', $allowTypes = array(), $maxSize = 2048000)
	{
		$urls = array();
		$length = count($files['name']);
		for ($i=0; $i<$length; $i++) {
			if ($files['error'][$i] === 0) {
				$urls[] = self::upload(array(
					'name' => $files['name'][$i],
					'type' => $files['type'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error' => $files['error'][$i],
					'size' => $files['size'][$i]
				), $dest, $allowTypes, $maxSize);
			}
		}
		return $urls;
	}

	/**
	 * 远程上传文件
	 *
	 * @param string $file
	 * @param string $remoteScript
	 */
	public static function remoteUpload($file, $remoteScript)
	{
		$file = array("filedata"=>'@'.$file['tmp_name'], 'filename'=>$file['name'], 'token'=>APP_KEY);//文件路径，前面要加@，表明是文件上传.
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $remoteScript);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $file);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
	}

	/**
	 * 上传文件
	 *
	 * @param string $file
	 * @param string $dest
	 * @param array $allowTypes
	 * @param int $maxSize
	 * @return string
	 */
	public static function upload($file, $dest = 'uploads/', $allowTypes = array(), $maxSize = 2048000)
	{
		if (!$file) { return; }

		if ($file['size'] > $maxSize) {
			Suco_Loader::loadClass('Suco_File_Exception');
			throw new Suco_File_Exception("The file size is out of range {$maxSize}");
		}

		//禁止上传的格式
		$denyTypes = array('php', 'asp', 'jsp', 'aspx', 'html', 'js', 'css');
		$ext = pathinfo($file['name']);
		$ext = strtolower($ext['extension']);

		if (($allowTypes && !in_array($ext, $allowTypes)) || in_array($ext, $denyTypes)) {
			Suco_Loader::loadClass('Suco_File_Exception');
			throw new Suco_File_Exception('The file format is illegal, the system is only allow the this '.implode(',', $allowTypes).' format');
		}

		$dp = pathinfo($dest);

		if ($dp['extension']) {
			$dest = rtrim($dp['dirname'], '/') . '/';
			$fileName = $dp['basename'];
		} else {
			$dest = rtrim($dest, '/') . '/' . date('Ymd') . '/';
			$fileName = md5(microtime()) . '.' . $ext;
			
			if (!is_dir($dest)) mkdir($dest, 0777); 
			$dest = $dest . date('H') . '/'; 

			if (!is_dir($dest)) mkdir($dest, 0777);
		}

		if (!move_uploaded_file($file['tmp_name'], $dest . $fileName)) {
			Suco_Loader::loadClass('Suco_File_Exception');
			throw new Suco_File_Exception('The file upload fail');
		}

		return $dest . $fileName;
	}

	/**
	 * 复制文件
	 *
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	public static function copy($source, $dest)
	{
		if (is_file($source)) {
			copy($source, $dest);
			return true;
		}
		return false;
	}

	/**
	 * 删除文件
	 *
	 * @param string $file
	 * @return bool
	 */
	public static function delete($file)
	{
		if (is_file($file)) {
			unlink($file);
			return true;
		}
		return false;
	}

	/**
	 * 移动文件
	 *
	 * @param string $source
	 * @param string $dest
	 */
	public static function move($source, $dest)
	{
		rename($source, $dest);
	}

	/**
	 * 写文件
	 *
	 * @param string $file
	 * @param string $content
	 * @param string $mode w新建, a追加
	 * @return bool
	 */
	public static function write($file, $content, $mode = 'w')
	{
		if (is_writable(dirname($file))) {
			$handle = fopen($file, $mode);
			flock($handle, LOCK_EX);
			fwrite($handle, $content);
			flock($handle, LOCK_UN);
			fclose($handle);
			return true;
		}
		return false;
	}

	/**
	 * 读文件
	 *
	 * @param string $file
	 * @param string $mode
	 * @return string
	 */
	public static function read($file, $mode = 'r')
	{
		if (is_readable($file)) {
			$handle = fopen($file, $mode);
			flock($handle, LOCK_EX);
			$content = fread($handle, filesize($file));
			flock($handle, LOCK_UN);
			fclose($handle);
			return $content;
		}
	}

	/**
	 * 检查文件是否存在
	 * 
	 * @param  string $file 文件地址
	 * @return bool
	 */
	public static function exists($file)
	{
		return is_file($file);
	}

}