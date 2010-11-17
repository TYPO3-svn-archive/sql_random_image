This extension can display a random image from a sql statement : 

It display the image with defined width and height and a link to the page whitch is related to

Regexp can be used for selecting images by name (all images with the name like *good* , *sun*, ...)

Header is the name of the content element. This is replaces with alt and title if plugin Alttext for Images (dmc_image_alttext) is loaded

You can use custom sql like : 
SELECT `pid` , `header` , `image` FROM `tt_content` WHERE `CType` = 'image' || `CType` = 'textpic'
AND `header` != '' 
AND `image` NOT REGEXP '(,)' 
AND `image` REGEXP '(good)' 
AND `imagewidth` != 500  
AND `hidden` != '1' 
ORDER BY RAND() LIMIT 1