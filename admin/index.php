<script>
function goBack()
  {
  window.history.back()
  }
</script>

<?php

$nom_page = null;

require_once('../connexion.php');
require_once('../droits.php');
// require_once('preheader.php');
// include ('ajaxCRUD.class.php');

$page = $_GET['page'];

switch ($page){
	case 'utilisateurs':
		$titre = 'Utilisateurs';
		$users = new ajaxCrud ("Utilisateur","users","id");//,"nom");
		$users->defineRelationship("id_groupe","groupes","id_groupe","nom_groupe");
		$users->defineCheckbox("multi_agence");
		$users->defineCheckbox("admin");
		$users->displayAddFormTop();
		$users->omitPrimaryKey();
		$users->displayAs("ip", "Adresse IP");
		$users->displayAs("nom", "Utilisateur");
		$users->displayAs("id_groupe", "Groupe");
		$users->displayAs("multi_agence", "Multi-Agence");
		$users->displayAs("admin", "Admin");
		$table = $users;
		break;
	case 'agences' :
		$titre = 'Agences';
		$agences = new ajaxCrud ("Agence","agences","id_agence");//,"nom");
		$agences->displayAddFormTop();
		$agences->omitPrimaryKey();
		$agences->displayAs("ip", "Adresse IP");
		$agences->displayAs("c_agence", "Code Agence");
		$agences->displayAs("nom_agence", "Nom Agence");
		
		$table = $agences;
		break;
	case 'liens' :
		$titre = 'Liens';
		$outils = new ajaxCrud ("Liens","outils","id");
		$outils->defineRelationship("id_groupe","groupes","id_groupe","nom_groupe");
		$outils->omitPrimaryKey();
		$outils->defineCheckbox("acces_rapide");
		$outils->defineCheckbox("actif");
		$outils->displayAddFormTop();
		$outils->formatFieldWithFunction("icone", "displayImage");
		   function displayImage($value){
				 //displays the image instead of just the filename
				 if(!is_null($value)){return "<img src='../bootstrap/img/{$value}' width='64' \>\n";}
			}
		$table = $outils;
		break;
	case 'groupes' :
		$titre = 'Groupes';
		$groupes = new ajaxCrud ("Groupes","groupes","id_groupe");
		$groupes->defineRelationship("id_groupe","groupes","id_groupe","nom_groupe");
		$groupes->omitPrimaryKey();
		$groupes->displayAs("nom_groupe", "Nom");
		$groupes->displayAs("texte", "Message");
		$groupes->displayAs("type_alerte", "Couleur Message");
		$groupes->displayAddFormTop();
		$table = $groupes;
		break;
	case 'liensagences' :
		$titre = 'Liens Agences';
		$outils = new ajaxCrud ("Liens","outils_agences","id");
		$outils->defineRelationship("id_groupe","groupes","id_groupe","nom_groupe");
		$outils->omitPrimaryKey();
		$outils->defineCheckbox("protection");
		$outils->defineCheckbox("actif");
		$outils->displayAddFormTop();
		$outils->formatFieldWithFunction("icone", "displayImage");
		   function displayImage($value){
				 //displays the image instead of just the filename
				 if(!is_null($value)){return "<img src='../bootstrap/img/{$value}' width='64' \>\n";}
			}
		$table = $outils;
		break;
	case 'recherches' :
		$titre = 'Historiques Recherches';
		$recherche = new ajaxCrud ("Historique","search_log","id");
		$recherche->omitPrimaryKey();
		$recherche->displayAddFormTop();
		$recherche->turnOffAjaxEditing();
		$recherche->disallowAdd();
		$recherche->disallowDelete();
		$table = $recherche;
		break;	
	
	case 'recherches2' :
		break;
		
	default :
		header('Location: ../index.php');
		break;

}
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

    <link href="../bootstrap/css/bootstrap.css" rel="stylesheet">
  </head>
  <body><h3>Gestion des logs</h3>
  <button onclick='goBack()'>Retour</button>

<?php

$sql = "select * from search_log order by id desc limit 2000";
$req = mysql_query($sql) or die('Erreur SQL !<br>'.$sql.'<br>'.mysql_error());

echo "<table class='table table-striped table-bordered'>";
while($data = mysql_fetch_assoc($req)){
	echo '<tr>';
	foreach ($data as $value) {
		echo "<td>{$value}</td>";
	}
	echo '</tr>';
}
echo '</table>';
echo '</body></html>';
?>
