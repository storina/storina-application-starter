<?php
$element = osa_get_option('appindex_element');
$elementID = osa_get_option('appindex_ID');
$i = 0;
if(! empty($element)){
foreach ($element as $item) {
	switch($item){
		case 'sliderItems':
			include('dinamic/slider.php');
			break;
		case 'categories':
			include('dinamic/categories.php');
			break;
		case 'featured':
			include('dinamic/featured.php');
			break;
		case 'productBox':
			include('dinamic/productBox.php');
			break;
		case 'oneColADV':
			include('dinamic/oneColADV.php');
			break;
		case 'scrollADV':
			include( 'dinamic/scrollADV.php' );
			break;
		case 'scrollBox':
			include ('dinamic/scrollBox.php');
			break;
		case 'line':
			include('dinamic/line.php');
			break;
		case 'space':
			include('dinamic/space.php');
			break;
		case 'postBox':
			include('dinamic/postBox.php');
			break;
		case 'productBoxColorize':
			include('dinamic/productBoxColorize.php');
			break;
	}
	
$i++;
}
}
