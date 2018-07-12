<?php
// ------ récupération du nom d'utilisateur ------ //
//NEW:
// $infos = getLogin();	
$infos['username'] = 'fredericM';	//CODE

if (! $infos)     die('Problème d\'authentification ! (1)');

$_SESSION['infos'] = $infos;
$_SESSION['username'] = $infos['username'];
//:NEW

// $ip=$_SERVER['REMOTE_ADDR'];
$ip='127.0.0';	//CODE
$ip_temp=explode(".",$ip);

//Definition des droits de l'utilisateur qui se connecte
$sql_user = "select * from users u, groupes g where domain_user = '".$infos['username']."' and u.id_groupe=g.id_groupe";
$req_user = mysql_query($sql_user) or die('Erreur SQL !<br>'.$sql_user.'<br>'.mysql_error());

// if (mysql_num_rows($req_user) == 0)     die('Problème d\'authentification ! (2)');
// non car les users non présents dans la table users ont accès à la page 1/groupe 1

while($data_user = mysql_fetch_assoc($req_user)){
    $droit_user = $data_user['id_groupe'];
    $multi_agence = $data_user['multi_agence'];
    $admin = $data_user['admin'];
//     $ip_user = trim($data_user['ip']);
}

if(!is_null($nom_page)){
    
    //Récupération des autorisations nécessaires pour la page demandées
    $sql_page = "select * from groupes where nom_groupe = '".$nom_page."'";
    $req_page = mysql_query($sql_page) or die('Erreur SQL !<br>'.$sql_user.'<br>'.mysql_error());
    while($data_page = mysql_fetch_assoc($req_page)){
        $droit_page = $data_page['id_groupe'];
        $texte_page = $data_page['texte'];
        $type_alerte = $data_page['type_alerte'];
    }
    
    //On regarde si l'utilisateur a le droit de voir cette page
    if($droit_page<=$droit_user){
        $page = $droit_page;
    }else{
        $page = "1";
        unset($texte_page,$type_alert);
        $sql_texte = "select * from groupes where id_groupe = '".$page."'";
        $req_texte = mysql_query($sql_texte) or die('Erreur SQL !<br>'.$sql_texte.'<br>'.mysql_error());
        while($data_texte = mysql_fetch_assoc($req_texte)){
            $texte_page = $data_texte['texte'];
            $type_alerte = $data_texte['type_alerte'];
        }
    }
}else{
    $page = "1";
    $sql_texte = "select * from groupes where id_groupe = '".$page."'";
    $req_texte = mysql_query($sql_texte) or die('Erreur SQL !<br>'.$sql_texte.'<br>'.mysql_error());
    while($data_texte = mysql_fetch_assoc($req_texte)){
        $texte_page = $data_texte['texte'];
        $type_alerte = $data_texte['type_alerte'];
    }
}
// ------ Fonctions ntlm ------ //
function getLogin() {
    
    define('_NTLM_AUTH_FAILED',1); 
    define('_NTLM_PROXY',2); 
    
    $infos = getInfosFromNTLM();

    switch ($infos) {
        case _NTLM_PROXY:
            die('No proxy for ntlm');
        case _NTLM_AUTH_FAILED:
            die('Sorry NTLM auth failed');
        default:
            if($infos['domain'] === "UNIVERSPNEUS"){
            	
                return $infos;	//NEW
            }
            break;
    }
    
    return false;
}

function getInfosFromNTLM() {

    if (!empty($_SERVER['HTTP_VIA'])) {
        return _NTLM_PROXY;
    }
    $header = apache_request_headers();
    $auth = isset($header['Authorization']) ? $header['Authorization'] : null;

    if (is_null($auth)) {
        return unAuthorized();
    }
    if ($header['Host'] != 'srvweb') {
    	return unAuthorized();
    }
    
    if ($auth && (substr($auth,0,4) == 'NTLM')) {
        $c64 = base64_decode(substr($auth,5));
        $state = ord($c64{8});
        $GLOBALS['auth_seq'] = 'auth:' .print_r($c64, true) .' / state:' .print_r($state, true);
        
        switch ($state) {
            case 1:
                $chrs = array(0,2,0,0,0,0,0,0,0,40,0,0,0,1,130,0,0,0,2,2,2,0,0,0,0,0,0,0,0,0,0,0,0);
                $ret = "NTLMSSP";
                foreach ($chrs as $chr) {
                    $ret .= chr($chr);
                }
                return unAuthorized(trim(base64_encode($ret)));
                break;
            case 3:
                $l = ord($c64{31}) * 256 + ord($c64{30});
                $o = ord($c64{33}) * 256 + ord($c64{32});
                $domain = str_replace("\0","",substr($c64,$o,$l));
                $l = ord($c64{39}) * 256 + ord($c64{38});
                $o = ord($c64{41}) * 256 + ord($c64{40});
                $user = str_replace("\0","",substr($c64,$o,$l));
//NEW:                
                $l = ord($c64{47}) * 256 + ord($c64{46});
                $o = ord($c64{49}) * 256 + ord($c64{48});
                $host = str_replace("\0","",substr($c64,$o,$l));
                return array('domain' => $domain, 'user' => $user, 'host' => $host);
//:NEW                
                break;
        }
    }
}

function unAuthorized($msg=null) {
    
    $ntlm = 'WWW-Authenticate: NTLM';
    if ($msg) {
        $ntlm .= ' '.$msg;
    }
    header('HTTP1.0 401 Unauthorized');
    header($ntlm);
    
    return _NTLM_AUTH_FAILED;
//     return array('domain'=>"UNIVERSPNEUS",'user'=>"anonyme");
}

