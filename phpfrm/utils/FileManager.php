<?php

namespace corsica\framework\utils;

use corsica\framework\config\Config;
use Exception;

/**
 * Para administrar archivos
 * */

class FileManager extends Manager
{
	private $conf = null;

	public function __construct($env, $conn = null)
	{
		parent::__construct($env, $conn);
		if ($this->conf == null)
			$this->conf = new Config($env);
	}

	/**
	 * para subir un archivo a la carpeta upload luego de hacer upload
	 * @param archivo el objeto archivo subido
	 * @param filename el nombre final del archivo sin extensión
	 * @return object con la url y el path o error
	 */
	public function uploadArchivo($archivo, $filename, $subfolders = null)
	{
		$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
		$path = $this->getUploadPath($filename . "." . $extension, $subfolders);
		$url = $this->getUploadUrl($filename . "." . $extension, $subfolders);

		if (move_uploaded_file($archivo['tmp_name'], $path)) {
			$out = array("path" =>  $path, "url" =>  $url);
			return (object)$out;
		} else {
			//var_dump($archivo);
			$out = array("error" => "No se pudo mover el archivo desde " . $archivo['tmp_name'] . " a " . $path);
			return (object)$out;
		}
	}


	public function subirArchivos($archivos = null, array $carpeta)
	{
		$this->logger->debug("subirArchivos");

		$out = [];
		if (!is_null($archivos)) {
			if (!is_array($archivos['name'])) {
				//cuando el archivo viene de a uno, lo meto en un array para homologar
				$archivos['name'] = [$archivos['name']];
				$archivos['tmp_name'] = [$archivos['tmp_name']];
				$archivos['type'] = [$archivos['type']];
			}

			$q = count($archivos['name']);
			for ($i = 0; $i < $q; $i++) {
				$name = $archivos['name'][$i];
				$tmp_name = $archivos['tmp_name'][$i];
				$type = $archivos['type'][$i];

				$newname = $this->getUniqueName($name, $carpeta);

				$path = $this->getUploadPath($newname, $carpeta);
				$url = $this->getUploadUrl($newname, $carpeta);
				$uploaded = $this->subir1Archivo($newname, $tmp_name, $type, $path, $url);
				array_push($out, $uploaded);
			}
		} else {
			$out = [];
		}

		return $out;
	}
	private function getUniqueName($name, $carpeta)
	{
		$this->logger->debug("getUniqueName", ["name" => $name]);
		$extension = '';
		$basename = $name;
		$dot = strrpos($name, '.');
		if ($dot !== false) {
			$basename = substr($name, 0, $dot);
			$extension = substr($name, $dot + 1, strlen($name) - $dot + 1);
		}


		$newname = $basename . '.' . $extension;
		$fullname = $this->getUploadPath($name, $carpeta);
		$existe = file_exists($fullname);
		$corr = 1;
		while ($existe) {
			$newname = $basename . ' (' . $corr . ').' . $extension;
			$fullname = $this->getUploadPath($newname, $carpeta);
			$existe = file_exists($fullname);
			$corr++;
		}
		$this->logger->debug("getUniqueName", ["newname" => $newname]);

		return $newname;
	}
	/**
	 * Cuando sube un archivo queda un path temporal, que hay que mover 
	 * a la carpeta definitiva con el nombre definittivo
	 * @param name nombre archivo
	 * @param tmp_name ruta completa del archov tempora
	 * @param type
	 * @param path
	 * @param url
	 */
	private function subir1Archivo($name, $tmp_name, $type, $path, $url)
	{
		$this->checkPath($path);
		move_uploaded_file($tmp_name, $path);
		$out = ["name" => $name, "type" => $type, "path" => $path, "url" => $url];
		return (object)$out;
	}
	public function eliminarArchivo($path)
	{
		//lo siguiente es un filtro para no cagarla con archivos
		// importantes
		if (strpos($path, Config::COMMON_CLIENT_PATH) == false)
			throw new Exception("No se puede eliminar nada fuera de la carpeta cliente");
		if (strpos($path, Config::UPLOAD_PATH) == false)
			throw new Exception("No se puede eliminar nada fuera de la carpeta upload");

		$result = unlink($path);
		return $result;
	}
	/**retorna las rutas */
	public function test($archivo = null, array $carpeta)
	{
		$out = [];
		if (!is_null($archivo)) {
			if (is_array($archivo['name'])) {
				$q = count($archivo['name']);
				for ($i = 0; $i < $q; $i++) {
					$out['name' . $i] = $archivo['name'][$i];
					$out['tmp_name' . $i] = $archivo['tmp_name'][$i];
					$out['type' . $i] = $archivo['type'][$i];
					$out['size' . $i] = $archivo['size'][$i];
					$out['error' . $i] = $archivo['error'][$i];
					$out['path' . $i] = $this->getUploadPath($archivo['name'][$i], $carpeta);
					$out['url' . $i] = $this->getUploadUrl($archivo['name'][$i], $carpeta);
					$this->checkPath($this->getUploadPath($archivo['name'][$i], $carpeta));
				}
			} else {
				$out['name'] = $archivo['name'];
				$out['tmp_name'] = $archivo['tmp_name'];
				$out['type'] = $archivo['type'];
				$out['size'] = $archivo['size'];
				$out['error'] = $archivo['error'];
				$out['path'] = $this->getUploadPath($archivo['name'], $carpeta);
				$out['url'] = $this->getUploadUrl($archivo['name'], $carpeta);
				$this->checkPath($this->getUploadPath($archivo['name'], $carpeta));
			}
		} else {
			$out = ["path" => $this->getUploadPath(), "url" => $this->getUploadUrl()];
		}


		return (object)$out;
	}


	public function getUploadPath(String $filename = "", array $subfolders = null)
	{
		$folders = [Config::UPLOAD_PATH];
		if (!is_null($subfolders)) {
			$folders = array_merge($folders, $subfolders);
		}
		$path = $this->conf->getPath($folders, $filename);
		return $path;
	}
	public function getUploadUrl(String $filename = "", array $subfolders = null)
	{
		$folders = [Config::UPLOAD_PATH];
		if (!is_null($subfolders)) {
			$folders = array_merge($folders, $subfolders);
		}
		$url = $this->conf->getUrl($folders, $filename);
		return $url;
	}

	public function checkPath(String $path)
	{
		$lastIsFolder = substr($path, strlen($path) - 1, 1) == '/';

		$folders = explode('/', $path);
		$index = array_search(Config::COMMON_CLIENT_PATH, $folders);
		if ($index === false) {
			throw new Exception("La carpeta COMMON_CLIENT_PATH debe existir");
		}
		$max = count($folders);
		//de esta forma evita que verifique si existe el archivo
		if (!$lastIsFolder)
			$max--;
		$arr = array_slice($folders, 0, $max);

		$folderpath = implode('/', $arr);
		$existe = file_exists($folderpath);
		$this->logger->debug("Verificando la carpeta", ["folderpath" => $folderpath, "existe" => $existe]);
		if (!$existe) {
			$result = mkdir($folderpath, 0777, true);
			$this->logger->debug("Creando", ["folderpath" => $folderpath, "result" => $result]);
		}
	}
}
