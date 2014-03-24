<?php
/**
 * File.php
 *
 * Copyright 2003-2013, Moxiecode Systems AB, All rights reserved.
 */

/**
 * Indexed file class.
 *
 * @package MOXMAN_Vfs_Cache
 */
class MOXMAN_Vfs_Cache_File implements MOXMAN_Vfs_IFile {
	private $fileSystem, $wrappedFile, $info;

	public function __construct(MOXMAN_Vfs_FileSystem $fileSystem, MOXMAN_Vfs_IFile $file, $info = null) {
		$this->fileSystem = $fileSystem;
		$this->wrappedFile = $file;
		$this->fileInfoStorage = MOXMAN_Vfs_Cache_FileInfoStorage::getInstance();
		$this->info = $info ? $info : $this->fileInfoStorage->getInfo($file);
	}

	public function getFileSystem() {
		return $this->fileSystem;
	}

	public function getParent() {
		return $this->wrappedFile->getParent();
	}

	public function getParentFile() {
		$file = $this->wrappedFile->getParentFile();
		if ($file) {
			$file = new MOXMAN_Vfs_Cache_File($this->fileSystem, $file);
		}

		return $file;
	}

	public function getName() {
		return $this->wrappedFile->getName();
	}

	public function getPath() {
		return $this->wrappedFile->getPath();
	}

	public function getPublicPath() {
		return $this->wrappedFile->getPublicPath();
	}

	public function getPublicLinkPath() {
		return $this->wrappedFile->getPublicLinkPath();
	}

	public function getUrl() {
		return $this->wrappedFile->getUrl();
	}

	public function exists() {
		if ($this->info) {
			return true;
		}

		return $this->wrappedFile->exists();
	}

	public function isDirectory() {
		if ($this->info) {
			return $this->info["isDirectory"];
		}

		return $this->wrappedFile->isDirectory();
	}

	public function isFile() {
		if ($this->info) {
			return !$this->info["isDirectory"];
		}

		return $this->wrappedFile->isFile();
	}

	public function isHidden() {
		return $this->wrappedFile->isHidden();
	}

	public function getLastModified() {
		if ($this->info) {
			return $this->info["lastModified"];
		}

		return $this->wrappedFile->getLastModified();
	}

	public function canRead() {
		if ($this->info) {
			return $this->info["canRead"];
		}

		return $this->wrappedFile->canRead();
	}

	public function canWrite() {
		if ($this->info) {
			return $this->info["canWrite"];
		}

		return $this->wrappedFile->canWrite();
	}

	public function getSize() {
		if ($this->info) {
			return $this->info["size"];
		}

		return $this->wrappedFile->getSize();
	}

	public function moveTo(MOXMAN_Vfs_IFile $dest) {
		if ($dest instanceof MOXMAN_Vfs_Cache_File) {
			$dest = $dest->getWrappedFile();
		}

		$this->wrappedFile->moveTo($dest);
		$this->fileInfoStorage->deleteFile($this->getWrappedFile());
		$this->fileInfoStorage->putFile($dest);
	}

	public function copyTo(MOXMAN_Vfs_IFile $dest) {
		if ($dest instanceof MOXMAN_Vfs_Cache_File) {
			$dest = $dest->getWrappedFile();
		}

		$this->wrappedFile->copyTo($dest);
		$this->fileInfoStorage->putFile($dest);
	}

	public function delete($deep = false) {
		$this->wrappedFile->delete($deep);
		$this->fileInfoStorage->deleteFile($this->getWrappedFile());
	}

	public function listFiles() {
		return $this->listFilesFiltered(new MOXMAN_Vfs_BasicFileFilter());
	}

	public function listFilesFiltered(MOXMAN_Vfs_IFileFilter $filter) {
		return new MOXMAN_Vfs_Cache_FileList($this, $filter);
	}

	public function mkdir() {
		$this->wrappedFile->mkdir();
		$this->fileInfoStorage->putFile($this);
	}

	public function open($mode = MOXMAN_Vfs_IStream::READ) {
		return new MOXMAN_Vfs_Cache_FileStream($this, $this->wrappedFile->open($mode), $mode);
	}

	public function exportTo($localPath) {
		return $this->wrappedFile->exportTo($localPath);
	}

	public function importFrom($localPath) {
		$this->wrappedFile->importFrom($localPath);
		$this->fileInfoStorage->putFile($this);
	}

	public function getConfig() {
		return $this->wrappedFile->getConfig();
	}

	public function getMetaData() {
		return $this->wrappedFile->getMetaData();
	}

	public function getWrappedFile() {
		return $this->wrappedFile;
	}

	public function getFileInfoStorage() {
		return $this->fileInfoStorage;
	}
}
?>