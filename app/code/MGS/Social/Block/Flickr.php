<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Social\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Flickr extends Template 
{
	
	public function _iscurl(){
		if(function_exists('curl_version')) {
			return true;
		} else {
			return false;
		}
	}	
	
	public function getFlickrData($api_key = NULL,$user_id = NULL,$count = NULL, $width = NULL, $height = NULL, $album =  NULL) {
		$host = "https://api.flickr.com/services/rest/?";
		if($album == NULL){
			$method = "&method=flickr.people.getPublicPhotos";
			
		}else{
			$method = "&method=flickr.photosets.getPhotos";
		}
		$api_key = "&api_key=".$api_key;		
		$user_id = "&user_id=".$user_id;
		$format = "&format=json";
		if($album == NULL){
			$host = "https://api.flickr.com/services/rest/?".$method.$api_key.$user_id.$format;	
		}else{
			$album_id = "&photoset_id=".$album;
			$host = $host.$method.$api_key.$album_id.$user_id.$format;
		}
		
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = str_replace('jsonFlickrApi(','',$content);
		$content = str_replace(')','',$content);
		$content = json_decode($content, true);
		$number = count($content['photoset']['photo']); 
		$method_size ="&method=flickr.photos.getSizes";
		$link = "https://api.flickr.com/services/rest/?".$method_size.$api_key.$user_id.$format;
		if ($number >= $count) {
			for($i=0; $i < $count; $i++){
				$links[$i] = $link."&photo_id=".$content['photoset']['photo'][$i]['id'];			
			}
			for($j=0; $j < $count; $j++){
				if($this->_iscurl()) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $links[$j]);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

					//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
					$content = curl_exec($ch);
					curl_close($ch);
				}
				else {
					$content = file_get_contents($links[$j]);
				}
				$content = str_replace('jsonFlickrApi(','',$content);
				$content = str_replace(')','',$content);
				$content = json_decode($content, true);
	                        //var_dump($content);
				if($j % 3 == 0){
					$class = 'last';
				}else{
					$class='';
				}
				$html ="<a class='".$class."' href='".$content['sizes']['size'][0]['source']."' target='_blank'><img width='".$width."' height='".$height."' src='".$content['sizes']['size'][0]['source']."' alt='' /></a>";
				echo $html;
			}
		} else {
			for($i=0; $i < $number; $i++){
				$links[$i] = $link."&photo_id=".$content['photoset']['photo'][$i]['id'];			
			}
			for($j=0; $j < $number; $j++){
				if($this->_iscurl()) {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $links[$j]);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

					//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
					$content = curl_exec($ch);
					curl_close($ch);
				}
				else {
					$content = file_get_contents($links[$j]);
				}
				$content = str_replace('jsonFlickrApi(','',$content);
				$content = str_replace(')','',$content);
				$content = json_decode($content, true);
	                        //var_dump($content);
				if($j % 3 == 0){
					$class = 'last';
				}else{
					$class='';
				}
				$html ="<a class='".$class."' href='".$content['sizes']['size'][0]['source']."' target='_blank'><img width='".$width."' height='".$height."' src='".$content['sizes']['size'][0]['source']."' alt='' /></a>";
				echo $html;
			}
		}		
	}	
	
}