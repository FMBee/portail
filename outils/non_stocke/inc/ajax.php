<?php

    $c_agence = trim($_POST['agence']);
    include('connexion.php');
    include('requete.php');
    
    //Requete Pieces
    
    echo "<div class='alert alert-success'>Télécharger le tableau <strong><a href='inc/excel.php?agence=".$c_agence."'>Excel</a></strong></div>";
    echo "<br /><h2>Pièces</h2>";
    
    if ( ($results1 = request($sqldb, $sql_pieces)) === false ) {
        
        return false;
    }
    if (count($results1) > 0) {
        
        affResults($results1, 1);
    }
    else{
        echo "<div class='alert alert-danger'>Pas de pièces pour l'agence <strong>".$c_agence."</strong></div>";
    }
    
    // Requete Pneus
    
    echo "<br /><h2>Pneus</h2>";
    
    // pneus en stock
    if ( ($data1 = request($sqldb, $sql_pneus1)) === false ) {
        
        return false;
    }
    $results2 = array();

    if (count($data1) > 0) {
        
// echo "<p>pneus1:", count($data1), "</p>";        
        foreach ( $data1 as $ligne) {

            // dimensions du pneu
            if ( ($data2 = request($sqldb, $sql_pneus2, array($ligne['c_art'])) ) === false ) {
            
                return false;
            }
            $data2 = $data2[0];
            array_walk($data2, 'trim_value');

            // stock mini de la dimension
            if ( ($data3 = request($mysqldb, $sql_pneus3, array(
                                                            $data2['c_sfam_art'],     
                                                            $data2['largeur'],     
                                                            $data2['serie'],     
                                                            $data2['diam'],     
                                                            $data2['ind_charge'],     
                                                            $data2['ind_vit'],     
                                                            $data2['runflat'],     
                                                            $data2['renforce'],     
                                                            $data2['c_marque'],     
                                                            $c_agence ) 
                                )
                ) === false ) {
                
                return false;
            }
            // la dimension/marque est gérée en stock mini pour l'agence
            if ( count($data3) > 0 ) {

                // ..mais la valeur est nulle donc pas utilisée
                if ( $data3[0]['stockMini'] == 0 ) {
                
                    $results2[] = $ligne;
                }
            }
            else{
// echo "<p>NT:", print_r($data2['c_art']),"</p>";        
                // on check le second indice de charge pour les C
                if ( !empty($data2['ind_charge_2']) && in_array($data2['c_sfam_art'], ['CE', 'CH', 'CTS']) ) {
                    
// echo "<p>IC2:", print_r($data2['c_art']),"</p>";        
                    $data2['ind_charge'] = $data2['ind_charge'].'/'.$data2['ind_charge_2'];

                    if ( ($data3 = request($mysqldb, $sql_pneus3, array(
                                                                    $data2['c_sfam_art'],     
                                                                    $data2['largeur'],     
                                                                    $data2['serie'],     
                                                                    $data2['diam'],     
                                                                    $data2['ind_charge'],     
                                                                    $data2['ind_vit'],     
                                                                    $data2['runflat'],     
                                                                    $data2['renforce'],     
                                                                    $data2['c_marque'],     
                                                                    $c_agence ) 
                                        )
                        ) === false ) {
                        
                        return false;
                    }
                    // la dimension/marque est gérée en stock mini pour l'agence
                    if ( count($data3) > 0 ) {
        
                        // ..mais la valeur est nulle donc pas utilisée
                        if ( $data3[0]['stockMini'] == 0 ) {
                        
                            $results2[] = $ligne;
                        }
                    }
                }
                else{
                    $results2[] = $ligne;
                }
// echo "<p>NT:", print_r($data2),"</p>";        
            }
        }
// echo "<p>pneus2:", count($results), "</p>";        
        affResults($results2, 2);
    }
    else{
        echo "<div class='alert alert-danger'>Pas de pneus pour l'agence <strong>".$c_agence."</strong></div>";
    }
    
    file_put_contents(  'export/NS_' .$c_agence .'.tmp', 
                        serialize( array('pieces' => $results1, 'pneus' => $results2) )
    );
    
    
    
function request($pdo, $sql, $params=null ) {
    
    try {
        $req = $pdo->prepare($sql);
        $req->execute($params);
        
    }catch(PDOException $e){
        
        echo ($req->errorInfo()[2] ." / Erreur de requete : {$sql}");
        return false;
    }
    
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function affResults($results, $tab) {
    
    $table = "<table class='table table-striped table-bordered'>";
    $table .= "<tr>";
    
    foreach ( $results[0] as $key => $value ) {
        
        $table .= '<td>'.$key.'</td>' ;
    }
    $table .= "</tr>";
    
    foreach ( $results as $ligne )
    {
        $table .= "<tr>";
        $col = 1;
        
        foreach ( $ligne as $value )
        {
            switch ($tab) {
                
                case 1:
                    
                    switch ($col) {
                        
                        case 4:
                            $table .= "<td class='warning' style='text-align:center'>" .(int)$value ."</td>";
                            break;
                        
                        case 5:
                            $table .= "<td>" .date_format(date_create($value), 'd-m-Y') ."</td>";
                            break;
                            
                        default:
                            $table .= "<td>{$value}</td>";
                    }
                    break;
                    
                case 2:
                    
                    switch ($col) {
                        
                        case 5:
                            $table .= "<td class='warning' style='text-align:center'>" .(int)$value ."</td>";
                            break;
                        
                        case 6:
                            $table .= "<td>" .date_format(date_create($value), 'd-m-Y') ."</td>";
                            break;
                            
                        default:
                            $table .= "<td>{$value}</td>";
                    }
                    break;
            }
            $col++;
        }
        $table .= "</tr>";
    }
    $table .= "</table>";
    
    echo $table;
}

/*
 * usage: array_walk($array, 'trim_value');
 */
function trim_value(&$value) {
    
    $value = trim ( $value );
}

?>