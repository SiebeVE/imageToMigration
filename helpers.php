<?php
/**
 * Created by PhpStorm.
 * User: Siebe
 * Date: 22/01/2017
 * Time: 14:29
 */

function echoln( $string ) {
	echo "<br>";
	echo $string;
	echo "</br>";
}

function dd( $data ) {
	var_dump( $data );
	die( 1 );
}

function calculateDistanceBetweenPoints( $x1, $y1, $x2, $y2 ) {
	return sqrt( ( $x2 - $x1 ) ** 2 + ( $y2 - $y1 ) ** 2 );
}

function checkIfTextIsInRectangle( $coordinatesText, $rectangleX, $rectangleY, $rectangleW, $rectangleH ) {
	$numberOfPointsInRectangle = 0;
	foreach ( $coordinatesText as $coordinates ) {
		if ( checkIfPointInRange( $coordinates[0], $coordinates[1], $rectangleX, $rectangleY, $rectangleX + $rectangleW, $rectangleY + $rectangleH ) ) {
			$numberOfPointsInRectangle ++;
		}
	}

	return $numberOfPointsInRectangle >= 2;
}

function checkIfPointInRange( $x1, $y1, $minX, $minY, $maxX, $maxY ) {
	return $x1 <= $maxX && $x1 >= $minX && $y1 <= $maxY && $y1 >= $minY;
}