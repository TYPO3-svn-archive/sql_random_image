<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 nliaudat <nliaudat@pompiers-chatel.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
* Plugin 'Random image from SQL' for the 'sql_random_image' extension.
*
* @author	nliaudat <nliaudat@pompiers-chatel.ch>
* @based on ext maag_randomimage
* @package	TYPO3i
* @subpackage	tx_sqlrandomimage
*/
class tx_sqlrandomimage_pi1 extends tslib_pibase {
var $prefixId = 'tx_sqlrandomimage_pi1';		// Same as class name
var $scriptRelPath = 'pi1/class.tx_sqlrandomimage_pi1.php';	// Path to this script relative to the extension dir.
var $extKey = 'sql_random_image';	// The extension key.
var $pi_checkCHash = TRUE;

/**
* The main method of the PlugIn
*
* @param	string		$content: The PlugIn content
* @param	array		$conf: The PlugIn configuration
* @return	The content that is displayed on the website
*/


/**
* [Put your description here]
*/
function main($content,$conf)	{


$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
$this->conf=$conf;
$this->pi_setPiVarDefaults();
$this->pi_loadLL();

// Getting values from flexform
$this->init();

//retrieve config from flexform
//Sql conf
$regexp = $this->lConf["sql_regexp"];
$not_regexp = $this->lConf["sql_not_regexp"];
$img_max_width = $this->lConf["sql_img_max_width"];
$img_min_width = $this->lConf["sql_img_min_width"];
$hidden = $this->lConf["sql_hidden"];
$header = $this->lConf["sql_header"];
$only_image_field = $this->lConf["sql_only_image"];
$custom_sql_check = $this->lConf["sql_custom_check"];
$custom_sql_expr = $this->lConf["sql_custom_expr"];
//Display conf
$height = $this->lConf["img_height"];
$width = $this->lConf["img_width"];
//debug info
$debug = $this->lConf["debug"];
//modif 20.05.2009
$random_mode_check= $this->lConf["random_mode_check"];
$limit_display= $this->lConf["limit_display"];



if ($custom_sql_check == 1){
$query = $custom_sql_expr ;
}
else{

//make sql statement :

if (t3lib_extMgm::isLoaded('dmc_image_alttext'))  {
$query = "SELECT `pid` , `header` ,`image` , `tx_dmcimagealttext` , `tx_dmcimagetitletext` FROM `tt_content` WHERE `CType` = 'image'";
}else{
$query = "SELECT `pid` , `header` , `altText` , `titleText`, `image` FROM `tt_content` WHERE `CType` = 'image'";
}//dmc_image_alttext
if ($only_image_field ==0){$query .= " || `CType` = 'textpic'";}
if ($header == 0){$query .= " AND `header` != '' ";}
if ($not_regexp != ''){$query .= " AND `image` NOT REGEXP '" .$not_regexp ."'";}
if ($regexp != ''){$query .= " AND `image` REGEXP '" .$regexp  ."'";}
if ($img_max_width > 0){$query .= " AND `imagewidth` > " .$img_max_width;}
if ($img_min_width > 0){$query .= " AND `imagewidth` < " .$img_min_width;}
if ($hidden == 0){$query .= " AND `hidden` != '1'";}
if ($random_mode_check == 0){$query .= " ORDER BY RAND()";}else{$query .= " ORDER BY `tstamp` DESC";}
if ($limit_display != 0){$query .= " LIMIT " .$limit_display;}
}

$qr = mysql(TYPO3_db,$query);	// Performing query
if (mysql_error()) debug(array(mysql_error(), $qr));

$nrows = mysql_num_rows($qr);
if(isset($nrows)){
for ($i=0; $i < $nrows; $i++) {
  $row = mysql_fetch_array($qr);

$fileimg = $row['image'] ;
if(strrpos($fileimg, ",") >0){//has multiple images on field
$arr_img = explode(",", $fileimg);
$nb = count($arr_img) ;
//$fileimg = $arr_img[rand(0,$nb-1)]; //old way
if ($regexp != ''){
$pattern = '/' .$regexp .'/';
foreach( $arr_img as $img )
    {
if(preg_match($pattern, $img , $matches)){$fileimg = $matches[1];}
    }//for each

}else{
$fileimg = $arr_img[0];
}// regexp

} //strrpos

	$img_conf['file'] = "uploads/pics/" .$fileimg;
	$img_conf["file."]["maxW"] = $width;
	$img_conf["file."]["maxH"] = $height;
        if (t3lib_extMgm::isLoaded('dmc_image_alttext'))  {
	$img_conf["altText"]=$row['tx_dmcimagealttext'];
	$img_conf["titleText"]=$row['tx_dmcimagetitletext'];
        }else{
        if($debug == 1){
	$img_conf["altText"]=$fileimg;
	$img_conf["titleText"]=$fileimg;
	}else{
	$img_conf["altText"]=$row['altText'];
	$img_conf["titleText"]=$row['titleText'];
	}
        }//dmc_image_alttext
	$img_conf["params"] = 'hspace="0" vspace="0" border="0"';
	
	
	//imageLinkWrap Configuration:
	//$lconf['bodyTag'] = '<body bgColor=white leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">';
	//$lconf['wrap'] = '<a href="javascript: close();"> | </a>';
	//$lconf['JSwindow'] = '1';
	//$lconf['JSwindow.newWindow'] = '0';
	//$lconf['JSwindow.expand'] = '0,0';
	//$lconf['enable'] = '1';


	$content .= '<a href="index.php?id=' .$row['pid'] .'">' .$this->cObj->IMAGE($img_conf).'</a>';


}//mysql_fetch_array
}//if($nrows > 0)
else{$content = "No image found with that sql query : " .$query;}

	if($debug == 1){
	$content .= "<p><b><i>Debug info : </i></b><br>";
	$content .= "regexp : " .$regexp ."<br>";
	$content .= "not_regexp : " .$not_regexp ."<br>";
	$content .= "img_max_width : " .$img_max_width ."<br>";
	$content .= "img_min_width : " .$img_min_width ."<br>";
	$content .= "hidden : " .$hidden ."<br>";
	$content .= "header  : " .$header  ."<br>";
	$content .= "Only image fields : " .$only_image_field  ."<br>";
	$content .= "height  : " .$height  ."<br>";
	$content .= "width  : " .$width  ."<br>";
	$content .= "fileimg  : " .$fileimg  ."<br>";
	$content .= "query  : " .$query  ."<br>";
	$content .= "number of images in last field  : " .$nb  ."<br>";
	$content .= "total images found  : " .$nrows  ."<br>";
	$content .= "Custom Sql Activated : " .$custom_sql_check ."<br>";
	}

return ($this->local_cObj->stdWrap($content, $this->conf['stdWrap.']));
}


// Initializes the flexform and all config options
function init()
{
$this->pi_initPIflexForm();
$this->lConf = array();
$piFlexForm = $this->cObj->data['pi_flexform'];


if (is_array($piFlexForm['data']))
{
foreach ($piFlexForm['data'] as $sheet => $data)
foreach ($data as $lang => $value)
foreach ($value as $key => $val)
$this->lConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
}
}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sql_random_image/pi1/class.tx_sqlrandomimage_pi1.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sql_random_image/pi1/class.tx_sqlrandomimage_pi1.php']);
}

?>