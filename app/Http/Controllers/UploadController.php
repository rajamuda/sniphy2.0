<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\sequence;

class UploadController extends Controller
{
	//
	public function test (Request $request) {
		sleep(20);
		return header("HTTP/1.0 200 Ok");
	}

	/**
	 * 
	 * Delete a directory RECURSIVELY
	 * @param string $dir - directory path
	 * @link http://php.net/manual/en/function.rmdir.php
	 */
	public static function rrmdir($dir) {
	    if (is_dir($dir)) {
	        $objects = scandir($dir);
	        foreach ($objects as $object) {
	            if ($object != "." && $object != "..") {
	                if (filetype($dir . "/" . $object) == "dir") {
	                    self::rrmdir($dir . "/" . $object); 
	                } else {
	                    unlink($dir . "/" . $object);
	                }
	            }
	        }
	        reset($objects);
	        rmdir($dir);
	    }
	}

	/**
	 *
	 * Check if all the parts exist, and 
	 * gather all the parts of the file together
	 * @param string $temp_dir - the temporary directory holding all the parts of the file
	 * @param string $fileName - the original file name
	 * @param string $chunkSize - each chunk size (in bytes)
	 * @param string $totalSize - original file size (in bytes)
	 */
	public static function createFileFromChunks($temp_dir, $fileName, $chunkSize, $totalSize,$total_files) {

		// count all the parts of this file
		$total_files_on_server_size = 0;
		$temp_total = 0;

		foreach(scandir($temp_dir) as $file) {
			$temp_total = $total_files_on_server_size;
			$tempfilesize = filesize($temp_dir.'/'.$file);
			$total_files_on_server_size = $temp_total + $tempfilesize;
		}

		// if file is for references
		if(substr($fileName, -2) == "fa" || substr($fileName, -5) == "fasta") {
			$save_dir = resource_path("sequence/references");
			$types = "references";
		} else if(substr($fileName, -5) == "fastq") {
			$save_dir = resource_path("sequence/reads");
			$types = null;
		} else {
			return response(['status' => false , 'reason' => 'FileNotValid', 'message' => 'File is not valid!'], 400);
		}

		// check that all the parts are present
		// If the Size of all the chunks on the server is equal to the size of the file uploaded.
		if ($total_files_on_server_size >= $totalSize) {
		// create the final destination file 
			if (($fp = fopen($save_dir.'/'.$fileName, 'w')) !== false) {
				for ($i=1; $i<=$total_files; $i++) {
					fwrite($fp, file_get_contents($temp_dir.'/'.$fileName.'.part'.$i));
					// _log('writing chunk '.$i);
				}
				fclose($fp);
			} else {
				// _log('cannot create the destination file');
				return response(['status' => false , 'reason' => 'MergeFileFailed', 'message' => 'Fail to merge chunks'], 500);
			}

			$sequence = new Sequence;
			$sequence->name = $fileName;
			$sequence->type = $types;
			$sequence->public = -1; // unpublished
			$sequence->user_id = auth()->user()->id;
			$sequence->created_at = date('Y-m-d H:i:s');
			if(!$sequence->save()){
				return response(['status' => false , 'reason' => 'InsertDBFailed', 'message' => 'Fail to insert file information into database'], 500);
			}

			// rename the temporary directory (to avoid access from other 
			// concurrent chunks uploads) and than delete it
			if (rename($temp_dir, $temp_dir.'_UNUSED')) {
				self::rrmdir($temp_dir.'_UNUSED');
			} else {
				self::rrmdir($temp_dir);
			}
		}

	}


	public function upload (Request $request) {
		if ($request->isMethod('get')) {

			$resumableIdentifier = $request->query('resumableIdentifier');
			if($resumableIdentifier == null){
				$resumableIdentifier = '' ;
			}
			$temp_dir = storage_path('tmp/'.$resumableIdentifier);

			$resumableFilename = $request->query('resumableFilename');
			if($resumableFilename == null){
				$resumableFilename = '' ;
			}

			$resumableChunkNumber = $request->query('resumableChunkNumber');
			if($resumableChunkNumber == null){
				$resumableChunkNumber = '' ;
			}

			$chunk_file = $temp_dir.'/'.$resumableFilename.'.part'.$resumableChunkNumber;
			if (file_exists($chunk_file)) {
				return response(['status' => true , 'reason' => 'ChunkFound', 'message' => 'Chunk is in good condition'], 200);
		   	} else {
				return response(['status' => false , 'reason' => 'ChunkNotFound', 'message' => 'One of chunk files not found'], 404);
		   	}
		}

		if ($request->hasFile('file')) {
			if (!$request->file('file')->isValid()) {
				return response(['status' => false , 'reason' => 'FileNotValid', 'message' => 'Failed to upload file into server. Please try again in another minutes'], 400);
			}

			// init the destination file (format <filename.ext>.part<#chunk>
			// the file is stored in a temporary directory
			if ($request->resumableIdentifier !== null && trim($request->resumableIdentifier) != '') {
				$temp_dir = storage_path('tmp/'.$request->resumableIdentifier);
			}
			$dest_file = $request->resumableFilename.'.part'.$request->resumableChunkNumber;

			if(substr($request->resumableFilename, -2) != "fa" && substr($request->resumableFilename, -5) != "fasta" 
				&& substr($request->resumableFilename, -5) != "fastq") 
			{
				return response(['status' => false , 'reason' => 'FileNotValid', 'message' => 'File type is not valid!'], 400);
			}

			// create the temporary directory
			if (!is_dir($temp_dir)) {
				mkdir($temp_dir, 0777, true);
			}

			if($request->file('file')->storeAs($request->resumableIdentifier, $dest_file, 'tmp') === false) {
				return response(['status' => false , 'reason' => 'UploadFail', 'message' => 'Fail to store file into server. Please, try again.'], 500);
			} else {
				// check if all the parts present, and create the final destination file
				self::createFileFromChunks($temp_dir, $request->resumableFilename, $request->resumableChunkSize, 
					$request->resumableTotalSize, $request->resumableTotalChunks);
			}
		}
	}
}
