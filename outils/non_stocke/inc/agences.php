<?php

    //Une ligne vide pour obliger a faire un choix
    echo "<option>Veuillez choisir une agence</option>";
   
    $sql = "
        select c_agence, nom_agence from agence 
        where c_agence not in ('AGIP','XAVC','SIPL','SIAC','SITC','SIGM','SICA','SISO','SILG','SIL2','SIDI','SIGC','SIMA') 
        order by 1
    ";
	
    $req = $sqldb->prepare($sql);
    $req->execute(); 
	
    foreach ( $req->fetchAll(PDO::FETCH_ASSOC) as $data )  
	{ 
		echo "<option value='"
		      .trim($data['c_agence'])
		      ."'>"
		      .trim($data['c_agence'])
		      ." - "
		      .trim($data['nom_agence'])
		      ."</option>" ;
	}
