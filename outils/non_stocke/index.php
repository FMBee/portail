<?php

// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
error_reporting(0);

include('inc/connexion.php');

?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="refresh" content="7200">
    <title>Articles non stockés</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!--<script type="text/javascript" src="bootstrap/js/jquery.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>-->
    <link href="../../bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="../../bootstrap/css/tile.css" rel="stylesheet">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
    <style type="text/css">
	*, *:before, *:after {box-sizing:  border-box !important;}
	
      body {
        padding-top: 60px;
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
<link href="../../bootstrap/css/navbar.css" rel="stylesheet">
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
  
   <div class="container">
    <nav class="navbar navbar-default" role="navigation">
     
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="http://10.106.76.115/portail/outils/non_stocke/" style="font-size:200%">Articles non stockés</a>
		</div>
		<p class="navbar-text pull-right">
              <!--Un lien ici se mettra dans la barre en haut a droite -->
			  
			  <a href="http://10.106.76.115" target='_blank'><img src='../../bootstrap/img/logo_garrigue.png' alt='Portail Groupe Garrigue'> </a>
        </p>
    </nav>
    
   

    <div class="container">
      
        <div class="col-md-12">

    		<form class="form-inline" role="form">
    			<div class="form-group">
    				<label>Agence</label>
    				<select class="form-control" id="agence">
    				
    					<?php include('inc/agences.php'); ?>
    					
    				</select>
    			</div>
    		</form>
    		
    		<br>
    		<div id = "container_result" class = "col-md-12">
    		</div>		
    		
		</div>
	
     <!--<hr>-->

      <footer>
        <p>&copy; Univers Pneus 2018</p>
      </footer>

    
	</div><!--/.fluid-container-->
   </div><!--/.initial-container-->

    <script type='text/javascript' src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
	
	<script>
		$(function () {
		
    		$("#agence").change(function(){
    			
    			$( "#container_result" ).html("<div class='alert alert-warning'>Chargement en cours...</div>");
    			
    			$.post(	"inc/ajax.php", 
    					{agence:  $("#agence").val()},
    					function( data ) {
    						$( "#container_result" ).html( data );
    					}
    			);
    		});
    	
    	});
	</script>
	
  </body>
</html>