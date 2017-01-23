<?php
/**
 * Created by PhpStorm.
 * User: Siebe
 * Date: 22/01/2017
 * Time: 13:46
 */

require_once 'helpers.php';

$python = exec( "merged.py" );
//echoln($python);
//dd("");
//$python = '{"all": "USERS\nID\nEMAIL\nPASSWORD\nUSERNAME\nIS ADMIN\nPOSTS\nID\nUSER iD\nTITLE\nCONTENT\n", "text": {"0$USERS": [[247, 519], [661, 489], [669, 600], [255, 630]], "1$ID": [[266, 704], [388, 682], [408, 794], [286, 815]], "2$EMAIL": [[258, 836], [622, 852], [617, 961], [253, 945]], "3$PASSWORD": [[254, 982], [785, 988], [784, 1121], [253, 1115]], "4$USERNAME": [[258, 1154], [818, 1154], [818, 1251], [258, 1251]], "5$IS": [[277, 1310], [380, 1310], [380, 1427], [277, 1427]], "6$ADMIN": [[463, 1312], [818, 1313], [818, 1430], [463, 1429]], "7$POSTS": [[1336, 423], [1676, 425], [1675, 536], [1335, 534]], "8$ID": [[1351, 603], [1457, 603], [1457, 700], [1351, 700]], "9$USER": [[1338, 729], [1615, 729], [1615, 841], [1338, 841]], "10$iD": [[1683, 729], [1816, 729], [1816, 841], [1683, 841]], "11$TITLE": [[1336, 877], [1655, 891], [1651, 976], [1332, 962]], "12$CONTENT": [[1336, 1001], [1848, 991], [1850, 1093], [1338, 1103]]}, "rectangle": [{"coordinates": [597, 1039], "dimensions": [41, 52]}, {"coordinates": [1418, 1034], "dimensions": [44, 39]}, {"coordinates": [1762, 757], "dimensions": [42, 55]}, {"coordinates": [332, 706], "dimensions": [59, 83]}, {"coordinates": [179, 640], "dimensions": [731, 882]}, {"coordinates": [1405, 619], "dimensions": [43, 60]}, {"coordinates": [1268, 570], "dimensions": [672, 604]}, {"coordinates": [1407, 467], "dimensions": [47, 51]}, {"coordinates": [191, 464], "dimensions": [687, 176]}, {"coordinates": [1291, 376], "dimensions": [593, 185]}, {"coordinates": [0, 22], "dimensions": [2160, 3396]}, {"coordinates": [1478, 0], "dimensions": [625, 35]}]}';
$result = json_decode( $python );
//var_dump($result);

$allText      = $result->all;
$texts        = $result->text;
$allTextSplit = explode( "\n", $allText );
//var_dump( $texts );
//var_dump( $allTextSplit );
// make array to know which word is supposed to be concatenated
$textArray  = [];
$currentKey = 0;
foreach ( $allTextSplit as $key => $text ) {
	$textSplit = explode( ' ', $text );
	foreach ( $textSplit as $t ) {
		$textArray[ $currentKey ] = $key;
		$currentKey ++;
	}
}
//var_dump( $textArray );
$rectangles = $result->rectangle;

$bottomLefts = [];
$topLefts    = [];
$tables      = [];
// Search for rectangle pairs (title and content)
foreach ( $rectangles as $keyTitle => $rectangle ) {
	// calculate bottom left coordinate
	$coordinates   = $rectangle->coordinates;
	$dimensions    = $rectangle->dimensions;
	$bottomLeft    = [ "x" => $coordinates[0], "y" => $coordinates[1] + $dimensions[1] ];
	$bottomLefts[] = $bottomLeft;

	// Calculate distance between top lefts and bottom lefts
	//echoln( "----------------- Calculating distance -----------------" );
	foreach ( $topLefts as $keyContent => $topLeft ) {
		$distance = calculateDistanceBetweenPoints( $topLeft["x"], $topLeft["y"], $bottomLeft["x"], $bottomLeft["y"] );
		//echoln( $distance );
		if ( $distance <= 50 ) {
			//Rectangle is title of other
			$tables[] = [
				"title"   => [
					"rectangle" => $rectangle,
					"content"   => [],
				],
				"content" => [
					"rectangle" => $rectangles[ $keyContent ],
					"content"   => [],
				],
			];
		}
	}

	$topLefts[] = [ "x" => $rectangle->coordinates[0], "y" => $rectangle->coordinates[1] ];
}

$numberOfTablesFound = count( $tables );

//echoln( "############### Number of tables found: " . $numberOfTablesFound . " ###############" );

// Link text to tables
foreach ( $texts as $text => $coordinatesText ) {
	foreach ( $tables as $tableId => $table ) {
		foreach ( $table as $sort => $data ) {
			$coordinatesRect = $data["rectangle"]->coordinates;
			$dimensionsRect  = $data["rectangle"]->dimensions;
			if ( checkIfTextIsInRectangle( $coordinatesText, $coordinatesRect[0], $coordinatesRect[1], $dimensionsRect[0], $dimensionsRect[1] ) ) {
				$previous         = NULL;
				$numberOfElements = count( $tables[ $tableId ][ $sort ]["content"] );
				if ( count( $tables[ $tableId ][ $sort ]["content"] ) > 0 ) {
					$previous = array_values( array_slice( $tables[ $tableId ][ $sort ]["content"], - 1 ) )[0];
				}
				$idOfPrevious = explode( '$', $previous );
				$idOfCurrent  = explode( '$', $text );
				if ( $idOfPrevious[0] != '' && $textArray[ $idOfCurrent[0] ] == $textArray[ $idOfPrevious[0] ] ) {
					// Belongs together
					$tables[ $tableId ][ $sort ]["content"][ $numberOfElements - 1 ] = $previous . "_" . strtolower( $idOfCurrent[1] );
				} else {
					$tables[ $tableId ][ $sort ]["content"][] = strtolower( $text );
				}
			}
		}
	}
}

foreach ( $tables as $id => $table ) {
	foreach ( $table as $sortId => $sort ) {
		foreach ( $sort['content'] as $key => $content ) {
			$content = str_replace( '-', '', $content );

			$tables[ $id ][ $sortId ]["content"][ $key ] = explode( '$', $content )[1];
		}
	}
}

// Write to migration
foreach ( $tables as $table ) {
	$template  = file_get_contents( 'migrations/migration_template.php' );
	$tableName = $table["title"]["content"][0];
	$template  = str_replace( "::tableClassName::", ucfirst( $tableName ), $template );
	$template  = str_replace( "::tableName::", $tableName, $template );

	$contents     = $table["content"]["content"];
	$tableContent = "";
	$templateRule = "\t\t\t" . '$table->::dataType::("::columnName::")';
	foreach ( $contents as $content ) {
		$splited    = explode( '_', $content );
		$dataType   = array_pop( $splited );
		$columnName = implode( '_', $splited );
		$newRule    = "";

		switch ( $dataType ) {
			case 'increment':
			case 'increments':
			case 'integer':
			case 'text':
			case 'varchar':
			case 'string':
			case 'unsigned':
				if ( $dataType == "increment" ) {
					$dataType .= "s";
				} elseif ( $dataType == "varchar" ) {
					$dataType = "string";
				} elseif ( $dataType == "unsigned" ) {
					$dataType = "integer";
				}

				$newRule = str_replace( '::dataType::', $dataType, $templateRule );
				$newRule = str_replace( '::columnName::', $columnName, $newRule );

				if ( $dataType == "unsigned" ) {
					$newRule .= "->unsigned()";
				}
				break;
            default:
                $columnName .= "_".$dataType;
                $dataType = "string";

	            $newRule = str_replace( '::dataType::', $dataType, $templateRule );
	            $newRule = str_replace( '::columnName::', $columnName, $newRule );
                break;
		}
		if ( $newRule != "" ) {
			$tableContent .= $newRule . ";\n";
		}
	}

	$template = str_replace( "::tableUp::", $tableContent, $template );

	file_put_contents( "migrations/" . date( 'Y_m_d_His' ) . '_create_' . $tableName . "_table.php", $template );
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Image to migration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css"
          integrity="sha256-gvEnj2axkqIj4wbYhPjbWV7zttgpzBVEgHub9AAZQD4=" crossorigin="anonymous"/>
    <style>
        .images img
        {
            width: 350px;
            margin-right: 25px;
        }

        .tables
        {
            display: flex;
        }

        .table
        {
            margin: 15px;
            border: 2px solid black;
            border-radius: 2px;
            padding: 10px;
        }

        .title
        {
            font-weight: bold;
            font-size: 1.5em;
            border-bottom: 1px solid black;
            margin-bottom: .5em;
        }
    </style>
</head>
<body>
<div class="images">
    <img src="sofsqure.png">
    <img src="sofsqure3.png">
</div>
<div class="tables">
	<?php foreach ( $tables as $key => $table ): ?>
        <div class="table">
            <div class="title"><?= $table["title"]["content"][0] ?></div>
            <div class="content">
				<?php foreach ( $table["content"]["content"] as $content ): ?>
                    <p><?= $content ?></p>
				<?php endforeach; ?>
            </div>
        </div>
	<?php endforeach; ?>
</div>
</body>
</html>