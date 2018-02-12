<?php

error_reporting(0);
session_start();

$nom_page = $_GET['page'];
include('connexion.php');
include('droits.php');
include('agence.php');
include('inc/wpcrypt.php');

if($c_agence != '') {
    $_SESSION['agence'] = $c_agence;
}
/*
if( !isset($_SESSION['granted'])) {
    // On autolog
    include('outils/conges/inc/constants.php');
    include('outils/conges/inc/bdd.php');
    $bdd = new connec();
    $infosUser = $bdd->getUser($_SERVER['REMOTE_ADDR']);
    if( isset($infosUser->id) AND $infosUser->id > 0) {
        $groupe = (int) $infosUser->id_groupe;
        if($groupe > 2) {
            $_SESSION['granted'] = true;
        }
    } else {
        $_SESSION['granted'] = false;
    }
}*/
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE10">
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="3600">
    <title>Outils du Groupe Garrigue</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="bootstrap/css/tile.css" rel="stylesheet">
    <link href="/portail/outils/conges/css/global.css" rel="stylesheet">
    <link href="bootstrap/css/navbar.css" rel="stylesheet">
    
    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
 	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/r-2.2.1/sc-1.4.4/datatables.min.css"/>
    
    <style type="text/css">
	*, *:before, *:after {box-sizing:  border-box !important;}
	
      body {
        padding-top: 10px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
	  
.row {
 -moz-column-width: 18m;
 -webkit-column-width: 18em;
 -moz-column-gap: 1em;
 -webkit-column-gap: 1em; 
  
}

.menu-category {
 display: inline-block;
 margin:  0.25rem;
 padding:  1rem;
 width:  100%; 
}
    </style>
<!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//10.106.76.115/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>

<noscript><p><img src="//10.106.76.115/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
 
  </head>

  <body>
  
	<?php if ( isset($_GET['search']) ) {
	
		include 'multi-search.php';
		include 'search-modal.php';
	}
	?>  
  
	<div class="container">
    <p class="text-right">
        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
        Utilisateur connecté : <?php echo $_SESSION['username'] ?>
    </p>
    <nav class="navbar navbar-default" role="navigation">
     
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/portail">Outils <?php isset($nom_agence) ? $nom_agence : '' ?></a>
		</div>
		
          <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav navbar-left">
			<?php
			  // Génération des onglets en fonction des droits
				$sql_onglet = "select * from groupes where id_groupe <= '".$droit_user."' order by id_groupe";
				$req_onglet = mysql_query($sql_onglet) or die('Erreur SQL !<br>'.$sql_onglet.'<br>'.mysql_error()); 
				while($data_onglet = mysql_fetch_assoc($req_onglet)) 
					{ 
					if($page == $data_onglet['id_groupe']){echo "<li class='active'><a href='".$data_onglet['nom_groupe'].".html'>".$data_onglet['nom_groupe']."</a></li>";}else{echo "<li><a href='".$data_onglet['nom_groupe'].".html'>".$data_onglet['nom_groupe']."</a></li>";}
					}
			  ?>
			  <li>
			   <form class="navbar-form navbar-left" action="index.php" method="get">
				<input type="text" class="form-control" 
				placeholder="<?php if(isset($_GET['search'])){echo $_GET['search'];}else{echo'Rechercher';}?>" 
				name="search" id="search" autocomplete="off" >
			   </form>
			  </li>
              <!--<li><a href="#contact">Contact</a></li>-->
            </ul>
			<ul class="nav navbar-nav navbar-right">
            
              <!--Un lien ici se mettra dans la barre en haut a droite -->
			  <?php if($multi_agence == 1){include('dropdown-agence.php');}?>
			  <?php if($admin == 1){include('dropdown-admin.php');}?>
			 <!--<li><a href="http://www.groupegarrigue.fr" target='_blank'><img src='bootstrap/img/logo_garrigue.png' class="img-responsive" alt='GroupeGarrigue.fr'> </a></li>-->
            
          </ul><!--/.nav-collapse -->
		  </div>
        </nav>
    
   

    <div class="container">
      
        <div class="col-md-2">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header text-center"><span class="glyphicon glyphicon-globe"></span> Acces Rapide</li>
              <!--<li class="active"><a href="#">Link</a></li>-->
              <?php 
			  ///Génération des liens en fonction des enregistrements de la BDD
			    
				if(isset($db))
				{
				$sql = "SELECT * FROM outils where acces_rapide = 1 and actif = 1 and id_groupe = '".$page."' order by nom"; 
				$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error()); 
				while($data = mysql_fetch_assoc($req)) 
					{ 
					$description = htmlspecialchars(($data['description']),ENT_QUOTES);
					echo "<li><a rel='tooltip' data-original-title='".$description."' href='".$data['lien']."' target='_blank'>".$data['nom']."</a></li>";
					}
				}else{echo 'Problème d\'accès a la base de données';}
			  ?>

            </ul>
          </div><!--/.well -->
        </div><!--/span-->
        <div class="col-md-10">
        
	<?php

		//Affichage du texte de Groupe
		if(!empty($texte_page)){
		echo "<div class='alert ".$type_alerte."' id='alerte'>";
		//echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		echo $texte_page;
		echo "</div>";
		}		
		//Des commandes INOWEB
		if($page == 1){
// 			include('inc/cde_inoweb.php');	//:CODE
		}	
		//Si c'est l'informatique, on affiche l'etat des imports comptables et encours
		if($page == 5){
		echo "<a href='#' id='refresh_info'><span class='glyphicon glyphicon-refresh'>Actualiser</span></a>";
		echo "<div class='well well-sm' id='container_info'>";
		echo "</div>";
		}
		//Si c'est la compta on affiche le Dashboard
		if($page == 3){
		echo "<a href='#' id='refresh_compta'><span class='glyphicon glyphicon-refresh'>Actualiser</span></a>";
		echo "<div class='well well-sm' id='container_compta'>";
		echo "</div>";
		}
		if($page == 1){
		//Cadre a Onglets pour les agences
		echo "<div class='well well-sm' id='container_tabs'>";
		echo "<ul class='nav nav-tabs'>";
			echo "<li><a href='#raccourcis' class='liens_onglets' data-toggle='tab' data-div='raccourcis' data-post='inc/liens_agences.php' id='lien_agences'>Votre Agence</a></li>";
			echo "<li class='active'><a href='#container_presence' data-toggle='tab'>Présence</a></li>";
			echo "<li><a href='#stats' class='liens_onglets' data-toggle='tab' data-div='stats' data-post='inc/stats_obj.php' id='lien_stats'>Stats</a></li>";
			echo "<li><a href='#cde_bloque' class='liens_onglets' data-toggle='tab' data-div='cde_bloque' data-post='inc/cde_bloque.php' id='lien_cde_bloque'>Cde Bloquées</a></li>";
			echo "<li><a href='#stock_bloque' class='liens_onglets' data-toggle='tab' data-div='stock_bloque' data-post='inc/stock_bloque.php' id='lien_stock_bloque'>Stock Bloqué</a></li>";
			if($droit_user == '5'){echo "<li><a href='#vnc' class='liens_onglets' data-toggle='tab' data-div='vnc' data-post='inc/liens_vnc.php' id='lien_vnc_bloque'>VNC</a></li>";}
			//echo "<li><a href='#settings' data-toggle='tab'>Settings</a></li>";
		echo "</ul>";
		
		//<!-- Tab panes -->
		echo "<div class='tab-content'>";
			echo "<div class='tab-pane' id='raccourcis'>";
			echo "</div>";
			echo "<div class='tab-pane active' id='container_presence'>";
// 			include('outils/conges/absences.php');	//:CODE
			echo "</div>";
			echo "<div class='tab-pane' id='stats'>";
			echo "</div>";
			echo "<div class='tab-pane' id='cde_bloque'>";
			echo "</div>";
			echo "<div class='tab-pane' id='stock_bloque'>";
			echo "</div>";
			echo "<div class='tab-pane' id='vnc'>";
			echo "</div>";
			//echo "<div class='tab-pane' id='settings'>...</div>";
		echo "</div>";
		}		
		
		//Fin du container_tabs
		echo "</div>";
  ?>

    <footer>
        <p class="text-center">&copy; Univers Pneus 2014</p>
    </footer>

    </div>
	</div><!--/.fluid-container-->
	<div id = "container_test"></div>
	</div><!--/.initial-container-->
	
    <!-- Le javascript
    ================================================== -->
	
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
    <script type='text/javascript' src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/r-2.2.1/sc-1.4.4/datatables.min.js"></script>
 
    <script src="/portail/outils/conges/js/global.js"></script>
    <script src="/portail/outils/conges/library/jquery/ui/datepicker-fr.js"></script>
 
	<script>
	
	$(document).ready(function() {
		
		$('body').tooltip({
		    selector: '[rel=tooltip]'
		});
		$("#container_info").html("<b>Chargement en cours...</b>");
	    $("#container_info").load("informatique.php");
		$("#container_compta").html("<b>Chargement en cours...</b>");
	    $("#container_compta").load("compta.php");
	
	  	$("#refresh_info").click(function() {
			 $("#container_info").html("<b>Chargement en cours...</b>");
		     $("#container_info").load("informatique.php");
			 return false;
		});
		
	  	$("#refresh_compta").click(function() {
			 $("#container_compta").html("<b>Chargement en cours...</b>");
		     $("#container_compta").load("compta.php");
			 return false;
		});	
		$(function () {
	    	$('#myTab a:last').tab('show')
	  	})
	  
		$("a.liens_onglets").click(function() {
			var div = $(this).data('div');
			var appel = $(this).data('post');
			var c_agence = $("input#c_agence").val();
			$( "div#"+div ).html("<br><br><b>Chargement en cours...</b><br><br>");
			$.post(appel, {agence: c_agence},function( data ) {
				$( "div#"+div ).html( data );
				return false;
			});
		});
		$('#search').focusin(function(){

	        $(this).css("background-color", "#FFFFCC");
	        $(this).val('');
	    });
		$('#search').focus();

	<?php if ( ! empty($query) ): ?> 

	    $('.table').DataTable( {
	
	    	dom: 'tip',
// 	    	scroller: true,
// 	    	scrollX: true,
// 	    	scrollY: '500px',
			responsive: {
				details:true
			},
	        pagingType: 'full',
	        pageLength: 10,
	        language: {
	            url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/French.json"
	        }
		});

		$('#searchModal').modal({backdrop: 'static'});

	<?php endif; ?>
		
	});
    </script>
    
  </body>
</html>
