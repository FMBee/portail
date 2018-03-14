<?php
    
    require_once "class.writeexcel_workbook.inc.php";
    require_once "class.writeexcel_worksheet.inc.php";
    
    $fname = tempnam("/tmp", "simple.xls");
    $workbook = &new writeexcel_workbook($fname);

    $results = unserialize(file_get_contents('export/NS_' .$_GET['agence'] .'.tmp'));
   
    // Pieces
    $worksheet = &$workbook->addworksheet('PIECES');
    $worksheet->freeze_panes(1, 0);
    
    if ( !affResults($worksheet, $results['pieces']) ) {

        $worksheet->write(1, 0, 'Pas de résultats pour PIECES');
    }

    // Pneus
    $worksheet = &$workbook->addworksheet('PNEUS');
    $worksheet->freeze_panes(1, 0);
    
    if ( !affResults($worksheet, $results['pneus']) ) {

        $worksheet->write(1, 0, 'Pas de résultats pour PNEUS');
    }
    
    $workbook->close();
    
    $nom_fichier = "Non_Stockes_{$_GET['agence']}_" .date('Y-m-d') .".xls";
    header("Content-Type: application/x-msexcel; name='{$nom_fichier}'");
    header("Content-Disposition: inline; filename='{$nom_fichier}'");
    $fh=fopen($fname, "rb");
    fpassthru($fh);
    unlink($fname);

    
    
    function affResults($worksheet, $results) {
    
        if ( count($results) > 0 ) {
            
            $lig = 1;
            $col = 0;
            // entetes
            foreach ( array_keys($results[0]) as $key ) {
                
            	$worksheet->write(0, $col++, $key);
            }
            // lignes
            foreach ( $results as $ligne ) {
            		
                $col = 0;
                
                foreach ( $ligne as $champs ) {
    
           			$worksheet->write($lig, $col++, $champs);
           		}
           		$lig++;
            }
            return true;
        }
        return false;
    }
    
?>
