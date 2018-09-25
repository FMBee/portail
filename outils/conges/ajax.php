<?php
include('inc/constants.php');
include('inc/functions.php');
include('inc/bdd.php');
$result = array('error' => 500, 'message' => 'Accès refusé');

$module = (isset($_GET['module']) AND $_GET['module'] != NULL) ? htmlentities($_GET['module']) : NULL;
$action = (isset($_POST['action']) AND $_POST['action'] != NULL) ? htmlentities($_POST['action']) : NULL;
$post = $_POST;

function calcRepos($dateDebutCycle, $totSemaine, $dt, $repos, $sameDay=false, $startPeriod='', $endPeriod='') {
    if($dateDebutCycle != '') {
        $madate = new DateTime($dateDebutCycle);
        $mondayTemp = new DateTime($dt->format('Y-m-d'));

        $myMonday = clone $mondayTemp->modify(('Sunday' == $mondayTemp->format('l')) ? 'Monday last week' : 'Monday this week');
        $interval = $myMonday->diff($madate)->format('%a');

        $nbSemaines = (int) ($interval / 7);
        $semaineActive = ($totSemaine > 0) ? ($nbSemaines % $totSemaine)+1 : 1;
    } else {
        $semaineActive = 1;
    }
    
    $day = $dt->format('N');
    $remove = 0;
    if(isset($repos[$semaineActive]) AND isset($repos[$semaineActive][$day])) {
        if($repos[$semaineActive][$day]['am'] AND $repos[$semaineActive][$day]['pm']) {
            $remove = 1;
            /*if( ($sameDay AND $startPeriod == 'am' AND $endPeriod == 'am') OR ($sameDay AND $startPeriod == 'pm' AND $endPeriod == 'pm') ) {
                $remove = 0.5;
            }*/
        } else if ($repos[$semaineActive][$day]['am'] OR $repos[$semaineActive][$day]['pm']) {
            $remove = 0.5;
            if( (!$repos[$semaineActive][$day]['am'] AND $sameDay AND $startPeriod == 'am' AND $endPeriod == 'am') 
                    OR (!$repos[$semaineActive][$day]['pm'] AND $sameDay AND $startPeriod == 'pm' AND $endPeriod == 'pm') ) {
                $remove = 0;
            }
        } else if (!$repos[$semaineActive][$day]['am'] AND !$repos[$semaineActive][$day]['pm']) {
            $remove = 0;
        }
    }
    return $remove;
}

function appendPresences($presences, $listePresences, &$listDates) {
    if($presences AND count($presences) > 0) {
        foreach($presences AS $presence) {

            if(! in_array($presence->datefr, $listDates)) {
                $listDates[] = $presence->datefr;
            }

            if(!isset($listePresences[$presence->datefr])) {
                $listePresences[$presence->datefr] = array();
            }
            if(!isset($listePresences[$presence->datefr][$presence->monteur])) {
                $listePresences[$presence->datefr][$presence->monteur] = array();
            }
            $listePresences[$presence->datefr][$presence->monteur][$presence->periode] = array('infosPresence' => $presence, 'infosConges'=>array());
        }
    }
    return $listePresences;
}

function appendConges($monteur, $horaires, $eventsArray) {

    $colors = array('#E64A19', '#FFC107', '#388E3C', '#448AFF', '#3F51B5', '#727272', '#7B1FA2', '#FF5252', '#4CAF50', '#607D8B',
    '#795548', '#009688', '#536DFE');

    $i=0;
    $tempColors = array();
    foreach($horaires AS $h) {         
        if($monteur == null) {
            $title = '['.$h->nomEmploye.'] '.$h->conge_motif;
        } else {
            $title = ($h->conge_motif == '') ? $h->nomEmploye : $h->conge_motif;
        }
        
        $start = $h->conge_start;
        $end = $h->conge_end;

        if( strtolower($h->conge_start_period) == 'am' AND  strtolower($h->conge_end_period) == 'pm') {
            $allDay = 1;
            $end = new DateTime($end);
            $end->add(new DateInterval('P1D'));
            $end = $end->format('Y-m-d');
        } else {
            $allDay = 0;
            if(strtolower($h->conge_start_period) == 'am') {
                $start .= ' 08:00';
            } else {
                $start .= ' 14:00';
            }
            if(strtolower($h->conge_end_period) == 'am') {
                $end .= ' 12:00';
            } else {
                $end .= ' 19:00';
            }
        }

        if(!isset($tempColors[$h->conge_employe_id])) {
            $tempColors[$h->conge_employe_id] = $colors[$i];
            $i++;
            if($i >= count($colors) ) {
                $i=0;
            }
        }

        $type = strtolower($h->conge_type);
        switch($type) {
            case 'congesrtt':
                $type = 'Congés / RTT';
                break;
            case 'arretmaladie':
                $type = 'Arrêt maladie';
                break;
            case 'ecole':
                $type = 'Formation / Ecole';
                break;
        }
        $content = 'Type de congé : '.$type.'<br />';
        $content .= ($h->conge_nb_jours > 0) ? 'Nombre de jours : '.$h->conge_nb_jours.'<br />' : '';
        $content .= (isset($_SESSION['granted']) AND $_SESSION['granted']) ? 'Motif : '.($h->conge_motif != '' ? nl2br($h->conge_motif) : 'Aucun motif') : '';

        $eventsArray[] = array('id' => 'conge'.$h->idconge, 'type' => 'b', 'title' => $title, 'allDay' => $allDay, 'start' => $start, 
            'end' => $end, 'content' => $content, 'backgroundColor' => $tempColors[$h->conge_employe_id]);
    }

    return $eventsArray;
}
                    
switch($module) {

    case 'planning':
        switch($action) {
            case 'infoEvent' :
                $event = isset($post['id']) ? (int) $post['id'] : NULL;
                
                if($event > 0) {
                    $db = new connec();

                    $infos = $db->infoEvent($event);
                    
                    if($infos) {
                        $functions = new functions();
                        $infos->datestart = substr($functions->dateFr($infos->datestart),0,16);
                        $infos->dateend = substr($functions->dateFr($infos->dateend),0,16);
                        $infos->title = html_entity_decode($infos->title);
                        $result = array('error' => 0, 'infos' => $infos);
                    } else {
                        $result = array('error' => 10, 'message' => 'Une erreur est survenue lors de la r&eacute;cup&eacute;ration des informations de l\'&eacute;v&eagrave;nement');
                    }
                }
                
                break;
            
            case 'loadPlanning':
                
                $monteur = isset($post['monteur']) ? $post['monteur'] : NULL;
                $agence = isset($post['agence']) ? $post['agence'] : NULL;
                $start = isset($post['start']) ? substr(htmlentities($post['start']),0,10) : NULL;
                $end = isset($post['end']) ? substr(htmlentities($post['end']),0,10) : NULL;
                
                if($monteur != '' AND $agence != '' AND $start != '' AND $end != '') {
                    $functions = new functions();
                    $etat = array('A' => array('text' => 'Absent', 'color' => '#d9534f'), 
                                  'P' => array('text' => 'Pr&eacute;sent', 'color' => '#5cb85c'), 
                                  'X' => array('text' => 'Non renseign&eacute;', 'color' => '#ccc'),
                                  'R' => array('text' => 'Repos', 'color' => '#5bc0de'),
                                  'C' => array('text' => 'Congés', 'color' => '#f0ad4e'));
                    
                    $db = new connec();
                    $horaires = $db->getAllHoraires($agence, $monteur);
                    
                    $eventsArray = array();
                    $n = 0;
                    
                    // Chargement du planning exchange
                    require_once('inc/exchangeCal.php');
                    
                    // on prend le calendrier en fonction de l'agence;
                    $infos = $db->getExchangeCal($agence);
                    if(isset($infos->idexchange) AND $infos->idexchange != '') {
                        $ex = new ExchangeCal($infos->idexchange);

                        $j = $ex->getEvents(true);
                        if(count($j) > 0) {
                            foreach($j AS $v) {
                                $eventsArray[$n]['id'] = '0exchange'.$n;
                                $eventsArray[$n]['title'] = $v['sujet'];
                                $eventsArray[$n]['allDay'] = $v['allday'];
                                $eventsArray[$n]['start'] = $functions->dateEn($v['debut'],'-','-');
                                $eventsArray[$n]['end'] = $functions->dateEn($v['fin'],'-','-');
                                $eventsArray[$n]['backgroundColor'] = '#f0ad4e';
                                $eventsArray[$n]['className'] = 'fc-exchange';
                                $n++;
                                //echo $v['sujet'].' - '.$v['debut'].' - '.$v['fin'].'<br />';

                            }
                        }
                    }
                    
                    foreach($horaires AS $h) {
                        // Chargement du planning de base
                        $nbDays = intval($h->day) - 1;
                        
                        $tmpStart = date('Y-m-d', strtotime($start. ' +'.$nbDays.' days'));
                        $tmpEnd = date('Y-m-d', strtotime($start. ' +'.$nbDays.' days'));
                        
                        if( intval($h->am_repos) === 0) {
                            $eventsArray[$n]['id'] = 'am'.$h->idmonteurhoraire;
                            $eventsArray[$n]['title'] = 'Pr&eacute;sence';
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $tmpStart.' '.$h->am_start;
                            $eventsArray[$n]['end'] = $tmpEnd.' '.$h->am_end;
                            $n++;
                        } else {
                            $eventsArray[$n]['id'] = 'am'.$h->idmonteurhoraire;
                            $eventsArray[$n]['title'] = 'Repos planning';
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $tmpStart.' 08:00:00';
                            $eventsArray[$n]['end'] = $tmpEnd.' 12:00:00';
                            $n++;
                        }
                        
                        // On prend la saisie réélle AM
                        $presences = $db->getPresence('AM', $tmpStart, $agence, $monteur);
                        if( isset($presences->id) AND $presences->id > 0) {
                            
                            $checkbox = '<input'.((intval($presences->valider) === 1) ? ' checked' : '').' type="checkbox" class="check-validate" name="check-'.$presences->id.'" id="check-'.$presences->id.'" />';
                            $commentaire = '<span data-id="'.$presences->id.'" data-comment="'.$presences->commentaire.'" class="edit-comment glyphicon glyphicon-pencil"></span>';
                            
                            $eventsArray[$n]['id'] = 'saisieAM'.$presences->id;
                            $eventsArray[$n]['title'] = $checkbox.$commentaire.'<br />'.$etat[$presences->presence]['text'];
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $tmpStart.' 08:00:00';
                            $eventsArray[$n]['end'] = $tmpEnd.' 12:00:00';
                            $eventsArray[$n]['backgroundColor'] = $etat[$presences->presence]['color'];
                            $eventsArray[$n]['className'] = 'fc-validate'.($presences->presence == 'RE' ? ' fc-write-black' : '');
                            $n++;
                        }
                            
                        //$tmpStart = date('Y-m-d', strtotime($tmpStart. ' +'.$nbDays.' days'));
                        //$tmpEnd = date('Y-m-d', strtotime($tmpEnd. ' +'.$nbDays.' days'));
                        
                        if( intval($h->pm_repos) === 0) {
                            $eventsArray[$n]['id'] = 'pm'.$h->idmonteurhoraire;
                            $eventsArray[$n]['title'] = 'Pr&eacute;sence';
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $tmpStart.' '.$h->pm_start;
                            $eventsArray[$n]['end'] = $tmpEnd.' '.$h->pm_end;
                            $n++;
                        } else {
                            $eventsArray[$n]['id'] = 'pm'.$h->idmonteurhoraire;
                            $eventsArray[$n]['title'] = 'Repos planning';
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $tmpStart.' 14:00:00';
                            $eventsArray[$n]['end'] = $tmpEnd.' 19:00:00';
                            $n++;
                        }
                        
                        // On prend la saisie réélle PM
                        $presences = $db->getPresence('PM', $tmpStart, $agence, $monteur);
                        if( isset($presences->id) AND $presences->id > 0) {
                            $checkbox = '<input'.((intval($presences->valider) === 1) ? ' checked' : '').' type="checkbox" class="check-validate" name="check-'.$presences->id.'" id="check-'.$presences->id.'" />';
                            $commentaire = '<span data-id="'.$presences->id.'" data-comment="'.$presences->commentaire.'" class="edit-comment glyphicon glyphicon-pencil"></span>';
                            
                            $eventsArray[$n]['id'] = 'saisiePM'.$presences->id;
                            $eventsArray[$n]['title'] = $checkbox.$commentaire.'<br />'.$etat[$presences->presence]['text'];
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $tmpStart.' 14:00:00';
                            $eventsArray[$n]['end'] = $tmpEnd.' 18:30:00';
                            $eventsArray[$n]['backgroundColor'] = $etat[$presences->presence]['color'];
                            $eventsArray[$n]['className'] = 'fc-validate'.($presences->presence == 'RE' ? ' fc-write-black' : '');
                            $n++;
                        }
                    }
                    
                    
                    // on ajoute les jours fériés
                    $holidays = $functions->getHolidays();
                    $begin = new DateTime( $start );
                    $end = new DateTime( $end );

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);

                    foreach ( $period as $dt ) {
                        $temp = $dt->format('m-d');
                        if (in_array($temp,$holidays)) {
                            $eventsArray[$n]['id'] = 'jourferie'.$n;
                            $eventsArray[$n]['title'] = 'Férié';
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $dt->format('Y-m-d').' 08:00:00';
                            $eventsArray[$n]['end'] = $dt->format('Y-m-d').' 19:00:00';
                            $eventsArray[$n]['backgroundColor'] = '#bb4040';
                            $eventsArray[$n]['className'] = 'fc-validate';
                            $n++;
                        }
                    }
                    
                    $result = ($eventsArray);
                }

                break;
                
            case 'loadPresences':
                
                $agence = isset($post['agence']) ? $post['agence'] : NULL;
                $start = isset($post['start']) ? substr(htmlentities($post['start']),0,10) : NULL;
                $end = isset($post['end']) ? substr(htmlentities($post['end']),0,10) : NULL;
                $views = isset($post['views']) ? $post['views'] : NULL;
                $filters = isset($post['filters']) ? $post['filters'] : NULL;
                $noConge = '';
                
                if($agence != '' AND $start != '' AND $end != '') {
                    
                    $functions = new functions();
                    
                    $etats = $functions->listeEtatsPresence();
                    $etats['A'] = array('text' => 'Absent', 'color' => '#d9534f', 'class' => 'danger');
                    
                    $db = new connec();
                    $dbExt = new connec_ext();
                                        
                    $holidays = $functions->getHolidays();
                    $monteurs = $dbExt->listMonteurs();
                    
                    $listDates = array();
                    
                    $listMonteurs = array();
                    foreach($monteurs AS $m) {
                        if(!isset($listMonteurs[$m->c_agence])) {
                            $listMonteurs[$m->c_agence] = array();
                        }
                        $listMonteurs[$m->c_agence][trim($m->c_magasinier)] = utf8_encode(trim($m->nom_magasinier));
                    }
                    
                    if(! is_array($filters) OR ! in_array('normal', $filters)) {
                        $presences = $db->getPresenceMonth($start, $end, $agence, $views, $filters);
                        $listePresences = appendPresences($presences, array(), $listDates);
                    } else {
                        $listePresences = array();
                        
                        // on obtiens les jours fériés de la période
                        $joursFeries = $functions->isFerie($start, $end);
                        
                        // on démarre au lundi
                        $mondayTemp = new DateTime($start);
                        $newStart = clone $mondayTemp->modify(('Sunday' == $mondayTemp->format('l')) ? 'Monday last week' : 'Monday this week');
                        
                        $interval = DateInterval::createFromDateString('1 week');
                        $period = new DatePeriod($newStart, $interval, new Datetime($end));
                        
                        foreach($period AS $week) {
                            
                            $startTemp = $week->format('Y-m-d');
                            $endTemp = $week->modify('+5 day')->format('Y-m-d');
                            
                            // on vérifie les semaines avec soucis
                            /*  -Quand il y a 5 jours de présences + 1 jour de repos
                                -Quand il y a 4 jours de présences + 1 jour férié + 1 repos
                                -Quand il y a 5 jours de présences + 1 férié
                            */
                            $listAnormal = $db->getFilterAnormal($agence, $startTemp, $endTemp);
                            $errors = [];
                            foreach($listAnormal AS $a) {
                                $nbRepos = $a->nbRepos;
                                $nbPresences = $a->nbPresences;
                                $joursFeries = $functions->isFerie($startTemp, $endTemp);
                                
                                if( ($nbRepos == 1 AND $nbPresences == 5) OR 
                                        ($nbRepos == 1 AND $nbPresences == 4 AND count($joursFeries) == 1 ) OR
                                        ($nbPresences == 5 AND count($joursFeries) == 1 ) ) {
                                    // tout est ok
                                } else {
                                    $errors[] = $a->monteur;
                                }
                            }
                            if(count($errors) > 0) {
                                foreach($errors AS $e) {
                                    $presences = $db->getPresenceMonth($startTemp, $endTemp, $agence, $views, $filters, $e);
                                    $listePresences = appendPresences($presences, $listePresences,$listDates);
                                }
                            }
                        }
                        
                        $firstDay = date('Y-m-01', strtotime($start));
                        $lastDay = date('Y-m-t', strtotime($start));
                        
                        $listCongesPresence = $db->getCongesInPresence($agence, $firstDay, $lastDay);
                        $listePresences = appendPresences($listCongesPresence, $listePresences,$listDates);
                        
                        $dateTemp = new DateTime($start);
                        $date = $dateTemp->format('Y-m-\%');
                        
                        setlocale (LC_TIME, 'fr_FR.utf8'); 
                        
                        $dateObj   = DateTime::createFromFormat('!m', $dateTemp->format('m'));
                        $monthName = ucfirst(strftime('%B', strtotime($dateObj->format('Y-m-d'))));
                        
                        /*$listeNoConge = $db->getMonteurNoConge($date, $agence);
                        foreach($listeNoConge AS $c) {
                            $noConge .= '<div class="alert alert-danger" role="alert">Pas de congés pour '.$c->nom_magasinier.' ce mois-ci ('.$monthName.')</div>';
                        }*/
                    }
                    
                    if(! is_array($filters) OR ! in_array('normal', $filters)) {
                        $conges = $db->getConges($start, $end, $agence);
                    } else {
                        $conges = $db->getConges($start, $end, $agence, null, '03');
                    }
                                        
                    $listeConges = array();
                    if($conges AND count($conges) > 0) {
                        foreach($conges AS $conge) {
                            $startPeriod = $conge->conge_start_period;
                            $endPeriod = $conge->conge_end_period;
                            
                            if($conge->conge_start == $conge->conge_end) {
                                if(! in_array($conge->conge_startfr, $listDates)) {
                                    $listDates[] = $conge->conge_startfr;
                                }
                                
                                if(!isset($listePresences[$conge->conge_startfr])) {
                                    $listePresences[$conge->conge_startfr] = array();
                                }
                                if(!isset($listePresences[$conge->conge_startfr][$conge->c_magasinier])) {
                                    $listePresences[$conge->conge_startfr][$conge->c_magasinier] = array();
                                }
                                if(!isset($listePresences[$conge->conge_startfr][$conge->c_magasinier][strtoupper($startPeriod)])) {
                                    $listePresences[$conge->conge_startfr][$conge->c_magasinier][strtoupper($startPeriod)] = array('infosPresence' => array(), 'infosConges' => array());
                                }
                                $listePresences[$conge->conge_startfr][$conge->c_magasinier][strtoupper($startPeriod)]['infosConges'] = $conge;
                                
                                $dateTemp = $conge->conge_startfr;
                                
                                if(!isset($listeConges[$dateTemp])) {
                                    $listeConges[$dateTemp] = array();
                                }
                                
                                if($startPeriod == 'am' AND $endPeriod == 'am') {
                                    $period = 'AM';
                                } else if($startPeriod == 'pm' AND $endPeriod == 'pm') {
                                    $period = 'PM';
                                } else {
                                    $period = 'AM/PM';
                                }
                                
                                $listeConges[$dateTemp][$conge->monteur][$period] = $conge;
                            } else {
                                $interval = DateInterval::createFromDateString('1 day');
                                if($endPeriod == 'pm') {
                                    $conge->conge_end .= ' 23:59:59';
                                }
                                $period = new DatePeriod(new DateTime($conge->conge_start), $interval, new DateTime($conge->conge_end));
                                foreach($period AS $p) {
                                    
                                    $dateTemp = $p->format('d-m-Y');
                                    /*if(!isset($listeConges[$dateTemp])) {
                                        $listeConges[$dateTemp] = array();
                                    }*/
                                    
                                    if(! in_array($dateTemp, $listDates)) {
                                        $listDates[] = $dateTemp;
                                    }
                                    
                                    /*
                                    if(!isset($listePresences[$dateTemp])) {
                                        $listePresences[$dateTemp] = array();
                                    }
                                    if(!isset($listePresences[$dateTemp][$conge->monteur])) {
                                        $listePresences[$dateTemp][$conge->monteur] = array();
                                    }
                                    */

                                    $period = 'AM/PM';
                                    if($dateTemp == $conge->conge_startfr AND $startPeriod == 'pm') {
                                        $period = 'PM';
                                    } else if($dateTemp == $conge->conge_endfr AND $endPeriod == 'am') {
                                        $period = 'AM';
                                    }
                                    
                                    if($period == 'AM' OR $period == 'PM') {
                                        if(!isset($listePresences[$dateTemp][$conge->monteur][$period])) {
                                            $listePresences[$dateTemp][$conge->monteur][$period] = array('infosPresence' => null, 'infosConges' => array());
                                        }
                                        $listePresences[$dateTemp][$conge->monteur][$period]['infosConges'] = $conge;
                                    } else {
                                        if(!isset($listePresences[$dateTemp][$conge->monteur]['AM'])) {
                                            $listePresences[$dateTemp][$conge->monteur]['AM'] = array('infosPresence' => null, 'infosConges' => array());
                                        }
                                        $listePresences[$dateTemp][$conge->monteur]['AM']['infosConges'] = $conge;
                                        
                                        if(!isset($listePresences[$dateTemp][$conge->monteur]['PM'])) {
                                            $listePresences[$dateTemp][$conge->monteur]['PM'] = array('infosPresence' => null, 'infosConges' => array());
                                        }
                                        $listePresences[$dateTemp][$conge->monteur]['PM']['infosConges'] = $conge;
                                    }
                                    
                                    //$listeConges[$dateTemp][$conge->monteur][$period] = $conge;
                                }
                            }
                            
                        }
                    }
                    
                    $html = '';
                    //foreach($listePresences AS $date => $employes) {
                    foreach($listDates AS $k => $date) {
                           
                        $html .=  '
                        <tr>
                            <td>'.$date.'</td>
                            <td colspan="4"></td>
                        </tr>';
                        
                        foreach($listePresences[$date] AS $monteur => $periodes) {
                            
                            foreach($periodes AS $periode => $datas) {
                            
                                $infosPresence = $datas['infosPresence'];
                                $infosConges = $datas['infosConges'];
                                
                                $codeMonteur = trim($monteur);
                                
                                if($infosPresence != null) {
                                    $nomMonteur = ( isset($listMonteurs[$infosPresence->c_agence]) AND isset($listMonteurs[$infosPresence->c_agence][$codeMonteur])) ? $listMonteurs[$infosPresence->c_agence][$codeMonteur] : $codeMonteur;

                                    if($periode == 'AM') {
                                        $etat = $etats[$infosPresence->presence]['text'].' le matin';
                                    } else {
                                        $etat = $etats[$infosPresence->presence]['text'].' l\'après midi';
                                    }
                                    $action = (intval($infosPresence->valider) === 0) ? '<button type="button" class="btn btn-primary" onclick="validAbsence('.$infosPresence->id.', 1);">Valider</button>' : '<button type="button" class="btn btn-inverse" onclick="validAbsence('.$infosPresence->id.', 0);">Invalider</button>' ;
                                    $id = $infosPresence->id;
                                    $commentaire = nl2br($infosPresence->commentaire);
                                    $presence = $infosPresence->presence;
                                    $conge = '';
                                    $etatClass = isset($etats[$presence]) ? $etats[$presence]['class'] : '';
                                }
                                
                                if($infosConges != null) { // on a un congé
                                    if($infosPresence == null) {
                                        $nomMonteur = $infosConges->nomEmploye;
                                        $id = $infosConges->idconge;
                                        $presence = '';
                                        $commentaire = '';
                                        $action = '';
                                        if($periode == 'AM') {
                                            $etat = 'Le matin';
                                        } else {
                                            $etat = 'L\'après midi';
                                        }
                                        $etatClass = '';
                                    }
                                    
                                    $saisie = $functions->dateFr($infosConges->conge_dateadd, '-');
                                    $type = ($infosConges->conge_type == 'congesrtt') ? 'Congés / RTT' : 'Arrêt Maladie';
                                    $duree = '<br />Durée : '.$infosConges->conge_nb_jours.' jour'.($infosConges->conge_nb_jours > 1 ? 's' : '').' ouvré'.($infosConges->conge_nb_jours > 1 ? 's' : '');
                                    $motif = ($infosConges->conge_motif != '') ? '<br />Motif : '.$infosConges->conge_motif : '<br />Aucun motif renseigné';
                                    
                                    $conge = '<span class="glyphicon glyphicon-ok popT" title="Saisie le '.$saisie.'" data-content="Type : '.$type.$duree.$motif.'"></span>';// $listeConges[$date][$codeMonteur]['AM/PM']->nomEmploye;
                                    
                                }


                                $html .=  '
                                <tr id="line'.$id.'">
                                    <td></td>
                                    <td>'.$nomMonteur.'</td>
                                    <td class="'.$etatClass.'">'.$etat.'</td>
                                    <td>'.$conge.'</td>
                                    <td><span>'.$commentaire.'</span><span data-id="'.$id.'" class="edit-comment glyphicon glyphicon-pencil ttipT" title="Editer le commentaire"></span></td>
                                    <td>'.$action.'</td>
                                </tr>';
                            }
                        }
                        
                    }
                    
                    $result = array('error' => 0, 'html' => $html, 'noConge' => $noConge);
                    
                }

                break;
            
            case 'loadPlanningMonth':
                
                $agence = isset($post['agence']) ? $post['agence'] : NULL;
                $start = isset($post['start']) ? substr(htmlentities($post['start']),0,10) : NULL;
                $end = isset($post['end']) ? substr(htmlentities($post['end']),0,10) : NULL;
                $views = isset($post['views']) ? $post['views'] : NULL;
                
                if($agence != '' AND $start != '' AND $end != '' AND is_array($views) AND count($views) > 0) {
                    $functions = new functions();
                    $etat = array('A' => array('text' => 'Absent', 'color' => '#d9534f'), 
                                  'P' => array('text' => 'Pr&eacute;sent', 'color' => '#5cb85c'), 
                                  'X' => array('text' => 'Non renseign&eacute;', 'color' => '#ccc'),
                                  'R' => array('text' => 'Repos', 'color' => '#5bc0de'),
                                  'C' => array('text' => 'Congés', 'color' => '#f0ad4e'));
                    
                    $db = new connec();
                    $holidays = $functions->getHolidays();
                    $eventsArray = array();
                    $n = 0;
                    
                    /*
                    if(LOCAL_MODE) {
                        $monteurs = $db->listMonteurs();
                    } else {
                        $dbExt = new connec_ext();
                        $monteurs = $dbExt->listMonteurs();
                    }*/
                    
                    $dbExt = new connec_ext();
                    $monteurs = $dbExt->listMonteurs();
                    
                    $listMonteurs = array();
                    foreach($monteurs AS $m) {
                        if(!isset($listMonteurs[$m->c_agence])) {
                            $listMonteurs[$m->c_agence] = array();
                        }
                        $listMonteurs[$m->c_agence][trim($m->c_magasinier)] = utf8_encode(trim($m->nom_magasinier));
                    }
                    
                    $presences = $db->getPresenceMonth($start, $end, $agence, $views);
                    
                    if($presences AND count($presences) > 0) {
                        foreach($presences AS $presence) {
                            $checkbox = '<input'.((intval($presence->valider) === 1) ? ' checked' : '').' type="checkbox" class="check-validate" name="check-'.$presence->id.'" id="check-'.$presence->id.'" />';
                            $commentaire = '<span data-id="'.$presence->id.'" data-comment="'.$presence->commentaire.'" class="edit-comment glyphicon glyphicon-pencil"></span>';
                            
                            $codeMonteur = trim($presence->monteur);
                            $nomMonteur = ( isset($listMonteurs[$presence->c_agence]) AND isset($listMonteurs[$presence->c_agence][$codeMonteur])) ? $listMonteurs[$presence->c_agence][$codeMonteur] : $codeMonteur;
                            
                            $eventsArray[$n]['id'] = 'saisie'.$presence->periode.$presence->id;
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['backgroundColor'] = $etat[$presence->presence]['color'];
                            $eventsArray[$n]['className'] = 'fc-validate';
                            $eventsArray[$n]['monteur'] = $codeMonteur;
                            $eventsArray[$n]['presence'] = $presence->presence;
                            
                            if($presence->periode == 'AM') {
                                $eventsArray[$n]['start'] = $presence->dateen.' 08:00:00';
                                $eventsArray[$n]['end'] = $presence->dateen.' 12:00:00';
                                $eventsArray[$n]['title'] = $checkbox.$commentaire.'<br />'.$nomMonteur.'<br />'.$etat[$presence->presence]['text'].' le matin';
                                $n++;
                            } else {
                                if(isset($eventsArray[($n-1)]['monteur']) AND $eventsArray[($n-1)]['monteur'] == $codeMonteur AND $eventsArray[($n-1)]['presence'] == $presence->presence 
                                    AND $eventsArray[($n-1)]['start'] == $presence->dateen.' 08:00:00') {
                                    $eventsArray[($n-1)]['end'] = $presence->dateen.' 19:00:00';
                                    $eventsArray[($n-1)]['title'] = $checkbox.$commentaire.'<br />'.$nomMonteur.'<br />'.$etat[$presence->presence]['text'].' la journée';
                                    unset($eventsArray[$n]);
                                } else {
                                    $eventsArray[$n]['start'] = $presence->dateen.' 14:00:00';
                                    $eventsArray[$n]['end'] = $presence->dateen.' 19:00:00';
                                    $eventsArray[$n]['title'] = $checkbox.$commentaire.'<br />'.$nomMonteur.'<br />'.$etat[$presence->presence]['text'].' l\'après midi';
                                    $n++;
                                }
                            }
                            
                        }
                    }
                    
                    
                    $begin = new DateTime( $start );
                    $end = new DateTime( $end );
                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);
                    foreach ( $period as $dt ) {                  
                        // on ajoute les jours fériés
                        $temp = $dt->format('m-d');
                        if (in_array($temp,$holidays)) {
                            $eventsArray[$n]['id'] = 'jourferie'.$n;
                            $eventsArray[$n]['title'] = 'Férié';
                            $eventsArray[$n]['allDay'] = 0;
                            $eventsArray[$n]['start'] = $dt->format('Y-m-d').' 08:00:00';
                            $eventsArray[$n]['end'] = $dt->format('Y-m-d').' 19:00:00';
                            $eventsArray[$n]['backgroundColor'] = '#bb4040';
                            $eventsArray[$n]['className'] = 'fc-validate';
                            $n++;
                        }
                    }                   
                                       
                    $result = ($eventsArray);
                    
                }

                break;
            case 'addEvent':
                
                $title = isset($post['title']) ? htmlentities($post['title']) : NULL;
                $monteur = isset($post['monteur']) ? $post['monteur'] : NULL;
                $start = isset($post['start']) ? substr(htmlentities($post['start']),0,16) : NULL;
                $end = isset($post['end']) ? substr(htmlentities($post['end']),0,16) : NULL;
                
                if($monteur != '' AND $title != '' AND $start !== NULL AND $end !== NULL) {
                    $functions = new functions();
                    
                    $start = $functions->formatDateFC($start);
                    $end = $functions->formatDateFC($end);
                    
                    $db = new connec();
                    
                    $event = $db->addEvent($monteur, $title, $start, $end);

                    $result = array('error' => 0, 'id' => $event);
                }
                
                break;
                
            case 'editEvent':
                
                $title = isset($post['title']) ? htmlentities($post['title']) : NULL;
                $id = isset($post['id']) ? (int) $post['id'] : NULL;
                $start = isset($post['start']) ? substr(htmlentities($post['start']),0,16) : NULL;
                $end = isset($post['end']) ? substr(htmlentities($post['end']),0,16) : NULL;
                
                if($id > 0 AND $title != '' AND $start !== NULL AND $end !== NULL) {
                    $functions = new functions();
                    
                    $start = $functions->formatDateFC($start);
                    $end = $functions->formatDateFC($end);
                    
                    $db = new connec();
                    
                    $event = $db->editEvent($id, $title, $start, $end);

                    $result = array('error' => 0);
                }
                
                break;
                
            case 'moveEvent' :
                $id = isset($post['id']) ? (int) $post['id'] : NULL;
                $resize = isset($post['resize']) ? (int) $post['resize'] : false;
                $datas = array();
                
                if($id > 0) {
                    $daymove = isset($post['daymove']) ? (float) $post['daymove'] : 0;
                    $minmove = isset($post['minmove']) ? (float) $post['minmove'] : 0;
                    
                    if($resize) {
                        $datas['dateend'] = isset($post['newend']) ? $post['newend'] : NULL;
                    } else {
                        $datas['dateend'] = isset($post['newend']) ? $post['newend'] : NULL;
                        $datas['datestart'] = isset($post['newstart']) ? $post['newstart'] : NULL;
                        $datas['allday'] = (isset($post['allday']) AND $post['allday'] == 'true') ? 1 : 0;
                    }
                    
                    $db = new connec();
                    
                    $event = $db->update('plannings_events', $datas, array('idplanningevent' => $id));
                    
                    $result = array('error' => 0);
                }
                
                break;
        }
        
        break;
        
    case 'presences':
        
        switch($action) {
        
            case 'editPresence':
                
                $id = isset($post['presence']) ? (int) $post['presence'] : NULL;
                $etat = isset($post['etat']) ? substr($post['etat'],0,5) : NULL;
                $functions = new functions();
                $etats = $functions->listeEtatsPresence();
                
                if($id > 0 AND isset($etats[$etat])  ) {
                    $db = new connec();
                    
                    $db->update('absences', array('presence' => $etat, 'saisie' => 1), array('id' => $id));
                    $result = array('error' => 0, 'etats' => $etats);
                }
                
                
                break;
                
            case 'listPresences' :
                $date = isset($post['date']) ? substr(trim($post['date']),0,10) : Date('d/m/Y');
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : NULL;
                if(! isset($_SESSION)) {
                    session_start();
                }
                $admin = ( isset($_SESSION['granted']) AND $_SESSION['granted']  );
                
                $functions = new functions();
                                                
                $dateToday = new DateTime(Date('Y-m-d'));
                $dateSel = new DateTime($functions->dateEn($date));
                $interval = $dateToday->diff($dateSel);
                $diff = (int) $interval->format('%a');
                
                $min = abs($functions->getMinDate()) + 1;
                $holidays = $functions->getHolidays();
                $dateSelShort = substr($functions->dateEn($date, '/', '-'),5);                
                
                if($date !== '' AND $agence != '' AND ($admin OR ($diff < $min AND !in_array($dateSelShort, $holidays)) ) ) {
                    
                    $date = $functions->dateEn($date);
                    $db = new connec();
                    
                    $dbExt = new connec_ext();
                    $listMonteurs = $dbExt->listMonteurs($agence);
                    /*if(! LOCAL_MODE) {
                        $dbExt = new connec_ext();
                        $listMonteurs = $dbExt->listMonteurs($agence);
                    } else {
                        $listMonteurs = $db->listMonteurs(); //debug
                    }*/
                    
                    $day = Date('w', strtotime($date));

                    $datas = '<tr>';
                    foreach($listMonteurs AS $k => $m) {
                        $agence = trim($m->c_agence);
                        $monteur = trim($m->c_magasinier);
                        
                        $db->completeAbsences($date, $agence, $monteur);
                        
                        $planning = $functions->getPlanningActif($monteur, $date);
                        
                        $saisie = $db->getAllHoraires($agence, $monteur, $day, $planning);
                        $am_repos = (isset($saisie->am_repos) AND intval($saisie->am_repos) === 1);
                        $pm_repos = (isset($saisie->pm_repos) AND intval($saisie->pm_repos) === 1);
                        
                        // On met la présence en repos automatiquement si son planning de base est coché 'REPOS'
                        if($am_repos) {
                            $db->autoRepos('AM', $date, $agence, $monteur);
                        }
                        if($pm_repos) {
                            $db->autoRepos('PM', $date, $agence, $monteur);
                        }
                        
                        $am = $db->getPresence('AM', $date, $agence, $monteur);
                        $pm = $db->getPresence('PM', $date, $agence, $monteur);
                        
                        if($k % 7 == 0) {
                            $datas .= '</tr><tr>';
                        }
                        
                        $datas .= '
                        <td>
                            <div class="monteur">
                                <h4>'.utf8_encode(trim($m->nom_magasinier)).'</h4>

                                '.$functions->createBlocButton($am).'
                                '.( (Date('Y/m/d') !== $date OR ( Date('Y/m/d')=== $date AND intval(Date('H') > 13) )) ? $functions->createBlocButton($pm) : '').'
                            </div>
                        </td>
                        ';
                    }
                    $datas .= '</tr>';
                    
                    $result = array('error' => 0, 'date' => $functions->dateFr($date), 'datas' => $datas);
                } else {
                
                    if($diff > $min-1) {
                        $result = array('error' => 10, 'message' => 'Vous ne pouvez revenir que 2 jours en arrière');
                    } else if (in_array($dateSelShort, $holidays)) {
                        $result = array('error' => 11, 'message' => 'Vous ne pouvez pas choisir un jour férié');
                    }
                }
                
                break;
                
            case 'loadOublis' :
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : '';
                
                if($agence != '') {
                    $db = new connec();
                    $functions = new functions();
                    $min = $functions->getMinDate(1);

                    $oublis = $db->getAgenceRetard(abs($min), $agence);
                    $oublis = isset($oublis['j1']) ? $oublis['j1'] : null;
                    
                    if(count($oublis) > 0) {

                        $dbExt = new connec_ext();
                        $monteurs = $dbExt->listMonteurs();
                            
                        /**if(LOCAL_MODE) {
                            $monteurs = $db->listMonteurs();
                        } else {
                            $dbExt = new connec_ext();
                            $monteurs = $dbExt->listMonteurs();
                        }*/

                        $listMonteurs = array();
                        foreach($monteurs AS $m) {
                            if(!isset($listMonteurs[$m->c_agence])) {
                                $listMonteurs[$m->c_agence] = array();
                            }
                            $listMonteurs[$m->c_agence][trim($m->c_magasinier)] = utf8_encode(trim($m->nom_magasinier));
                        }

                        $datas = '';
                        $k=0;
                        $code_monteur = '';
                        foreach($oublis AS $o) {
                            if($k > 0 AND ($k % 3 == 0) AND $o->monteur != $code_monteur) {
                                if($k > 0) {
                                    $datas .= '</tr>';
                                }
                                $datas .= '<tr>';
                            }
                            $codeMonteur = trim($o->monteur);
                            $monteur = ( isset($listMonteurs[$o->c_agence]) AND isset($listMonteurs[$o->c_agence][$codeMonteur])) ? $listMonteurs[$o->c_agence][$codeMonteur] : $codeMonteur;

                            if($o->monteur != $code_monteur) {
                                if($code_monteur != '') {
                                    $datas .= '</td>';
                                }
                                $datas .= '
                                <td>
                                    <h4>'.$monteur.'</h4>
                                    '.$functions->createBlocButton($o);

                                $k++;
                            } else {
                                $datas .= $functions->createBlocButton($o);
                            }


                            $code_monteur = $o->monteur;

                        }
                        $datas .= '</tr>';

                        setlocale(LC_TIME, 'fr_FR.utf8');

                        $date = $oublis{0}->date;
                        $date_string = strftime('%A %d %B', strtotime($date));

                        $html ='
                        <p>Vous avez des présences non renseignés pour<br /> le '.$date_string.' !</p>
                        <table class="table table-bordered table-hover table-oublis" id="table-oublis">
                            <tbody>
                            '.$datas.'
                            </tbody>
                        </table>';

                        $result = array('error' => 0, 'datas' => $html);
                    } else {
                        $result = array('error' => 0, 'datas' => '');
                    }
                }
                
                break;
        }
        
        
        break;
        
    case 'horaires':
        
        switch($action) {
        
            case 'addSemaine':
                
                $employe = isset($post['employe']) ? htmlentities($post['employe']) : NULL;
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : NULL;
                
                if($employe != '' AND $agence != '') {
                    $db = new connec();
                    $db->addDefaultPlanning($employe, $agence);
                    
                    $result = array('error' => 0);
                }
                

                break;
                
            case 'delSemaine':
                
                $semaine = isset($post['semaine']) ? intval($post['semaine']) : 0;
              
                if($semaine > 0) {
                    $db = new connec();
                    $db->delPlanning($semaine);
                    
                    $result = array('error' => 0);
                }
                

                break;
        
            case 'saveHoraires':
                $monteur = isset($post['monteur']) ? htmlentities($post['monteur']) : NULL;
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : NULL;
                $planning = isset($post['planning']) ? intval($post['planning']) : NULL;
                $horaires = isset($post['horaires']) ? $post['horaires'] : NULL;
                $facul = isset($post['facul']) ? (int) $post['facul'] : NULL;
                $datedebut = isset($post['datedebut']) ? $post['datedebut'] : NULL;
                
                if($monteur != '' AND $agence != '' AND $horaires !== NULL) {
                    
                    $functions = new functions();
                    $datedebut = $functions->dateEn($datedebut);
                    
                    $db = new connec();
                                        
                    foreach($horaires AS $id => $h) {
                        $where = array('idday' => $id);
                        $datas = array( 'am_start' => $h['AM']['start_time'].':00', 'am_end' => $h['AM']['end_time'].':00', 'am_repos' => $h['AM']['repos'],
                                        'pm_start' => $h['PM']['start_time'].':00', 'pm_end' => $h['PM']['end_time'].':00', 'pm_repos' => $h['PM']['repos']);
                        
                        
                        $db->update('planning_days', $datas, $where);
                        
                    }
                    
                    $result = $db->_fetch('SELECT idmonteur FROM monteurs WHERE c_agence=:agence AND c_magasinier=:monteur', array('agence' => $agence, 'monteur' => $monteur));
                    
                    if($result AND isset($result->idmonteur) AND $result->idmonteur > 0) {
                        $db->update('monteurs', array('planning_facul' => $facul, 'planning_date_debut' => $datedebut), array('idmonteur' => $result->idmonteur));
                    } else {
                        $db->add('monteurs', array('c_agence' => $agence, 'planning_date_debut' => $datedebut, 'c_magasinier' => $monteur, 'planning_facul' => $facul));
                    }
                    
                    $result = array('error' => 0);
                    
                }
                
                break;
        }
        
        break;
        
    case 'validation':
        
        switch($action) {
        
            case 'validateEvent':
                $event = isset($post['event']) ? (int) $post['event'] : NULL;
                $value = isset($post['value']) ? (int) $post['value'] : NULL;
                
                if($event > 0) {
                    
                    $db = new connec();
                    $db->exec('UPDATE absences SET valider=:value WHERE id=:id', array('id' => $event, 'value' => $value));
                    
                    $result = array('error' => 0);
                    
                }
                
                break;
                
            case 'saveComment' :
                $event = isset($post['id']) ? (int) $post['id'] : NULL;
                
                if($event > 0) {
                    $comment = isset($post['comment']) ? htmlentities($post['comment']) : '';
                    
                    $db = new connec();
                    $db->exec('UPDATE absences SET commentaire=:comment WHERE id=:id', array('id' => $event, 'comment' => $comment));
                    
                    $result = array('error' => 0);
                    
                }
                break;
                
            case 'checkMonth' :
                $month = isset($post['month']) ? (int) $post['month'] : NULL;
                $year = isset($post['year']) ? (int) $post['year'] : NULL;
                $start = isset($post['start']) ? $post['start'] : NULL;
                $end = isset($post['end']) ? $post['end'] : NULL;
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : NULL;
                
                if($start != '' AND $end != '' AND $agence != '') {
                    
                    $db = new connec();
                    //$db->exec('UPDATE absences SET valider=1 WHERE c_agence=:agence AND MONTH(date)=:month AND YEAR(date)=:year', array('agence' => $agence, 'month' => $month, 'year' => $year));
                    $db->exec('UPDATE absences SET valider=1 WHERE c_agence=:agence AND date >= :start AND date <= :end', array('agence' => $agence, 'start' => $start, 'end' => $end));
                    
                    $result = array('error' => 0);
                    
                }
                break;
        }
        
        break;
    
    case 'events':
        
        switch($action) {
            case 'addEvent':
            case 'editEvent':
                $functions = new functions();
                
                $start = isset($post['datedebut']) ? htmlentities($post['datedebut']) : '';
                $end = isset($post['datefin']) ? htmlentities($post['datefin']) : '';
                $startPeriod = isset($post['startPeriode']) ? strtolower(htmlentities($post['startPeriode'])) : '';
                $endPeriod = isset($post['endPeriode']) ? strtolower(htmlentities($post['endPeriode'])) : '';
                $agences = isset($post['list_agences']) ? htmlentities($post['list_agences']) : '';
                $title = isset($post['title']) ? htmlentities($post['title']) : '';
                $description = isset($post['description']) ? htmlentities($post['description']) : '';
                $idevent = isset($post['idevent']) ? (int) $post['idevent'] : 0;
                
                $listAgences = explode(',', $agences);
                
                if($agences != '' AND count($listAgences) > 0 AND $title !== '' AND $startPeriod != '' AND $endPeriod != '') {
                    $db = new connec();
                    
                    $datas = array('event_start' => $functions->dateEn($start), 'event_end' => $functions->dateEn($end), 'event_title' => $title, 
                        'event_text' => $description, 'event_start_period' => strtoupper($startPeriod), 'event_end_period' => strtoupper($endPeriod),
                        'event_date_add' => Date('Y-m-d H:i:s'), 'event_ip' => $_SERVER['REMOTE_ADDR']);

                    // on ajoute
                    if($idevent <= 0) {
                        $db->add('events', $datas);
                        $idevent = $db->lastId();
                        
                        if($idevent > 0) {
                            foreach($listAgences AS $agence) {
                                $datasTemp = array('idevent' => $idevent, 'idagence' => $agence);
                                $db->add('events_agences', $datasTemp);
                            }
                            $result = array('error' => 0);
                        }
                        
                    } else {
                        $db->update('events', $datas, array('idevent' => $idevent));
                        
                        $listAgencesBdd = $db->getAgencesEvent($idevent);
                        $tempList = array();
                        foreach($listAgencesBdd AS $a) {
                            if(! in_array($a->idagence, $listAgences)) {
                                $db->delete('events_agences', array('idevent' => $idevent, 'idagence' => $a->idagence));
                            }
                            $tempList[] = $a->idagence;
                        }
                        
                        foreach($listAgences AS $agence) {
                            if(! in_array($agence, $tempList)) {
                                $db->add('events_agences', array('idevent' => $idevent, 'idagence' => $agence));
                            }
                        }
                        
                        $result = array('error' => 0);
                    }
                    
                } else {
                    $result = array('error' => 10, 'message' => 'Merci de remplir tout les champs obligatoires');
                }
                
                break;
                
            case 'deleteEvent' :
                $idevent = isset($post['idevent']) ? (int) $post['idevent'] : 0;
                
                if($idevent > 0) {
                    $db = new connec();                    
                    $db->delete('events', array('idevent' => $idevent));
                    
                    $result = array('error' => 0);
                }
                
                break;
        }
        
        break;
    
    case 'conges':
        switch($action) {
            case 'viewCalendar':
                include('conges.php');
                $result = '';
                break;
            
            case 'loadConges':
                
                $monteur = isset($post['monteur']) ? $post['monteur'] : NULL;
                $getEvents = ( isset($post['events']) AND intval($post['events']) ===1);
                $agence = isset($post['agence']) ? $post['agence'] : NULL;
                $employes = isset($post['employes']) ? $post['employes'] : NULL;
                $agences = isset($post['agences']) ? $post['agences'] : NULL;
                $start = isset($post['start']) ? substr(htmlentities($post['start']),0,10) : NULL;
                $end = isset($post['end']) ? substr(htmlentities($post['end']),0,10) : NULL;
                
                if( ( ($monteur != '' AND $agence != '') OR ( is_array($agences) OR is_array($employes) ) ) AND $start != '' AND $end != '') {
                    session_start(); 
                    $functions = new functions();
                    $db = new connec();
                                       
                    $eventsArray = array();
                          
                    $n = count($eventsArray);
                    // on ajoute les évènements si besoin
                    if($getEvents) {
                        $listEvents = $db->getEventsAgence($agence);
                        foreach ( $listEvents as $e ) {
                            
                            $allDay = ($e->event_start_period == 'AM' AND $e->event_end_period == 'PM') ? 1 : 0;
                            $hourStart = ($e->event_start_period == 'AM') ? '08:00:00' : '14:00:00';
                            $hourEnd = ($e->event_end_period == 'AM') ? '12:00:00' : '19:00:00';
                            
                            if($allDay) {
                                $endEvent = new DateTime($e->event_end);
                                $endEvent->add(new DateInterval('P1D'));
                                $endEvent = $endEvent->format('Y-m-d');
                            }else {
                                $endEvent = $e->event_end;
                            }
                            
                            $eventsArray[$n]['type'] = 'a';
                            $eventsArray[$n]['id'] = 'evenement'.$e->idevent;
                            $eventsArray[$n]['title'] = '<span class="glyphicon glyphicon-star star-yellow"></span> '.$e->event_title;
                            $eventsArray[$n]['allDay'] = $allDay;
                            $eventsArray[$n]['start'] = $e->event_start.' '.$hourStart;
                            $eventsArray[$n]['end'] = $endEvent.' '.$hourEnd;
                            $eventsArray[$n]['backgroundColor'] = '#000000';
                            $eventsArray[$n]['className'] = 'fc-evenement';
                            $eventsArray[$n]['content'] = str_replace('"', "'", nl2br(html_entity_decode($e->event_text)));
                            $n++;
                            
                        }
                    }
                    
                    if(( is_array($agences) OR is_array($employes) )) {
                        if(is_array($agences) AND count($agences) > 0) {
                            foreach($agences AS $a) {
                                $horaires = $db->getConges($start, $end, $a, null);
                                $eventsArray = appendConges(null, $horaires,$eventsArray);
                            }
                        }
                        if(is_array($employes) AND count($employes) > 0) {
                            foreach($employes AS $a) {
                                $employe = $a['id'];
                                $agence = $a['agence'];
                                $infos = $db->getInfosEmploye($employe, $agence);
                                $monteur = isset( $infos->idmonteur) ? $infos->idmonteur : 0;
                                $horaires = $db->getConges($start, $end, $agence, $monteur);

                                $eventsArray = appendConges($monteur, $horaires, $eventsArray);
                            }
                        }
                    } else {
                        if($monteur != 'ALL') {
                            $infos = $db->getInfosEmploye($monteur, $agence);
                            $monteur = isset( $infos->idmonteur) ? $infos->idmonteur : 0;
                        }

                        $monteur = ($monteur == 'ALL') ? NULL : $monteur;
                        $horaires = $db->getConges($start, $end, $agence, $monteur);
                        
                        //$eventsArray = array();
                        $eventsArray = appendConges($monteur, $horaires, $eventsArray);
                    }
                    
                    $n = count($eventsArray);
                    
                    // on ajoute les jours fériés
//                     $holidays = $functions->getHolidays();
                    $begin = new DateTime( $start );
                    $end = new DateTime( $end );
                    $holidays = $functions->getHolidays($begin->format('Y'));	//CODE:

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);
                    //$n = count($eventsArray);
                    
                    foreach ( $period as $dt ) {
                        $temp = $dt->format('m-d');
                        if (in_array($temp,$holidays)) {
                            $eventsArray[$n]['type'] = 'c';
                            $eventsArray[$n]['id'] = 'jourferie'.$n;
                            $eventsArray[$n]['title'] = 'Férié';
                            $eventsArray[$n]['allDay'] = 1;
                            $eventsArray[$n]['start'] = $dt->format('Y-m-d').' 08:00:00';
                            $eventsArray[$n]['end'] = $dt->add(new DateInterval('P1D'))->format('Y-m-d').' 19:00:00';	//CODE:on ajoute un jour, sinon erreur popover()
                            $eventsArray[$n]['backgroundColor'] = '#bb4040';
                            $eventsArray[$n]['className'] = 'fc-validate';
                            $eventsArray[$n]['content'] = 'Jour férié';
                            $n++;
                        }
                    }
                    
                    
                    
                    $result = ($eventsArray);
                }

                break;
            case 'addConge':
            case 'editConge':
                $functions = new functions();
                
                $start = isset($post['datedebut']) ? htmlentities($post['datedebut']) : '';
                $end = isset($post['datefin']) ? htmlentities($post['datefin']) : '';
                $startPeriod = isset($post['startPeriode']) ? strtolower(htmlentities($post['startPeriode'])) : '';
                $endPeriod = isset($post['endPeriode']) ? strtolower(htmlentities($post['endPeriode'])) : '';
                $employe = isset($post['employes']) ? htmlentities($post['employes']) : '';
                $agence = isset($post['agences']) ? htmlentities($post['agences']) : '';
                $type = isset($post['type']) ? htmlentities($post['type']) : '';
                $motif = isset($post['motif']) ? htmlentities($post['motif']) : '';
                //$nbJours = isset($post['nbconges']) ? (float) $post['nbconges'] : 0;
                // on peut modifier le nombre de jours
                $nbJours = isset($post['calcNbJours']) ? $functions->toFloat($post['calcNbJours']) : 0;
                $nbFeries = isset($post['nbferies']) ? (float) $post['nbferies'] : 0;
                $nbRepos = isset($post['nbrepos']) ? (float) $post['nbrepos'] : 0;
                $idconge = isset($post['idconge']) ? (int) $post['idconge'] : 0;
                
                $db = new connec();
                $infos = $db->getInfosEmploye($employe, $agence);
                $idemploye = isset( $infos->idmonteur) ? $infos->idmonteur : 0;
                
                if($idemploye <= 0) {
                    $result = array('error' => 10, 'message' => 'Ce monteur n\'est pas encore disponible dans la saisie des congés, merci d\'attendre demain matin');
                } else {
                    if($start != '' AND $end != '' AND $employe != '' AND $agence != '' AND ($type == 'congesrtt' OR $type == 'arretmaladie')) {
                        
                        $startTemp = new DateTime($functions->dateEn($start));
                        $endTemp = new DateTime($functions->dateEn($end));
                        if( ($start == $end AND $startPeriod == 'pm' AND $endPeriod == 'am') OR ($startTemp > $endTemp) ) {
                            $result = array('error' => 11, 'message' => 'Cette période n\'est pas possible, le congé commence plus tard qu\'il ne finit !');
                        } else {

                            if($idconge > 0) {
                                $infosConges = $db->infosConge($idconge);

                                if( $infosConges->conge_start_period == 'AM' AND  $infosConges->conge_end_period == 'PM') {
                                    $sql = "UPDATE absences SET presence = 'A' WHERE date >= :start AND date <= :end AND monteur=:monteur";
                                    $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));
                                    //echo $sql;
                                    //print_r(array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));
                                } else if ($infosConges->conge_start_period == 'PM' AND  $infosConges->conge_end_period == 'PM') {
                                    $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period AND monteur=:monteur";
                                    $db->exec($sql, array('date' => $infosConges->conge_start, 'period' => $infosConges->conge_start_period, 'monteur' => $infosConges->monteur));

                                    $sql = "UPDATE absences SET presence = 'A' WHERE date > :start AND date <= :end AND monteur=:monteur";
                                    $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));

                                } else if ($infosConges->conge_start_period == 'PM' AND  $infosConges->conge_end_period == 'AM') {
                                    $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period";
                                    $db->exec($sql, array('date' => $infosConges->conge_start, 'period' => $infosConges->conge_start_period, 'monteur' => $infosConges->monteur));

                                    $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period AND monteur=:monteur";
                                    $db->exec($sql, array('date' => $infosConges->conge_end, 'period' => $infosConges->conge_end_period, 'monteur' => $infosConges->monteur));

                                    $sql = "UPDATE absences SET presence = 'A' WHERE date > :start AND date < :end AND monteur=:monteur";
                                    $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));

                                } else if ($infosConges->conge_start_period == 'AM' AND  $infosConges->conge_end_period == 'AM') {
                                    $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period AND monteur=:monteur";
                                    $db->exec($sql, array('date' => $infosConges->conge_end, 'period' => $infosConges->conge_end_period, 'monteur' => $infosConges->monteur));

                                    $sql = "UPDATE absences SET presence = 'A' WHERE date >= :start AND date < :end AND monteur=:monteur";
                                    $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));
                                }
                            }

                            $start = $functions->dateEn($start, '/', '-');
                            $end =  $functions->dateEn($end, '/', '-');

                            $datas = array('conge_start' => $start, 'idemploye' => $idemploye, 'conge_end' => $end, 'conge_employe_id' => $employe, 'conge_type' => $type, 
                                'conge_motif' => $motif, 'conge_start_period' => strtoupper($startPeriod), 'conge_end_period' => strtoupper($endPeriod),
                                'conge_nb_jours' => $nbJours, 'conge_nb_ferie' => $nbFeries, 'conge_nb_repos' => $nbRepos, 'conge_dateadd' => Date('Y-m-d H:i:s'), 'conge_ip' => $_SERVER['REMOTE_ADDR']);

                            if($idconge <= 0) {
                                $res = $db->add('conges', $datas);
                            } else {
                                $res = $db->update('conges', $datas, array('idconge' => $idconge));
                            }

                            // on ajoute les conges aux 'absences' du portail
                            $interval = DateInterval::createFromDateString('1 day');
                            $start = new DateTime($start);
                            $endLong = ($endPeriod == 'am') ? $end.' 12:00:00' : $end.' 23:59:59';
                            $endLong = new DateTime($endLong);
                            $end = new DateTime($end);
                            $period = new DatePeriod($start, $interval, $endLong);

                            $typeConge = ($type == 'congesrtt' ? 'C' : 'M');
                            foreach($period AS $date) {

                                if($date != $start OR ($date == $start AND $startPeriod == 'am') ) {
                                    $datas = array('date' => $date->format('Y-m-d'), 'c_agence' => $agence, 'periode' => 'AM', 'monteur' => $employe, 'presence' => $typeConge);

                                    $sql = "SELECT id FROM absences WHERE date=:date AND monteur=:monteur AND periode='AM' LIMIT 1";
                                    $temp = $db->_fetch($sql, array('date' => $date->format('Y-m-d'), 'monteur' => $employe));

                                    if($temp AND isset($temp->id) AND $temp->id > 0) {
                                        $db->update('absences', $datas, array('id' => $temp->id));
                                    } else {
                                        $db->add('absences', $datas);
                                    }
                                }

                                if($date != $end OR ($date == $end AND $endPeriod == 'pm') ) {
                                    $datas = array('date' => $date->format('Y-m-d'), 'c_agence' => $agence, 'periode' => 'PM', 'monteur' => $employe, 'presence' => $typeConge);

                                    $sql = "SELECT id FROM absences WHERE date=:date AND monteur=:monteur AND periode='PM' LIMIT 1";
                                    $temp = $db->_fetch($sql, array('date' => $date->format('Y-m-d'), 'monteur' => $employe));

                                    if($temp AND isset($temp->id) AND $temp->id > 0) {
                                        $db->update('absences', $datas, array('id' => $temp->id));
                                    } else {
                                        $db->add('absences', $datas);
                                    }
                                }
                            }

                            if($res) {
                                $result = array('error' => 0);
                            } else {
                                $result = array('error' => 501, 'message' => 'Une erreur est survenue lors de l\'ajout du congé');
                            }
                        }
                    } else {
                        $result = array('error' => 10, 'message' => 'Merci de remplir tout les champs obligatoires');
                    }
                }
                
                break;
        
            case 'deleteConge' :
                $idconge = isset($post['idconge']) ? (int) $post['idconge'] : 0;
                
                if($idconge > 0) {
                    $db = new connec();
                    
                    $infosConges = $db->infosConge($idconge);

                    if( $infosConges->conge_start_period == 'AM' AND  $infosConges->conge_end_period == 'PM') {
                        $sql = "UPDATE absences SET presence = 'A' WHERE date >= :start AND date <= :end AND monteur=:monteur";
                        $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));
                        
                    } else if ($infosConges->conge_start_period == 'PM' AND  $infosConges->conge_end_period == 'PM') {
                        $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period AND monteur=:monteur";
                        $db->exec($sql, array('date' => $infosConges->conge_start, 'period' => $infosConges->conge_start_period, 'monteur' => $infosConges->monteur));

                        $sql = "UPDATE absences SET presence = 'A' WHERE date > :start AND date <= :end AND monteur=:monteur";
                        $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));

                    } else if ($infosConges->conge_start_period == 'PM' AND  $infosConges->conge_end_period == 'AM') {
                        $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period";
                        $db->exec($sql, array('date' => $infosConges->conge_start, 'period' => $infosConges->conge_start_period, 'monteur' => $infosConges->monteur));

                        $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period AND monteur=:monteur";
                        $db->exec($sql, array('date' => $infosConges->conge_end, 'period' => $infosConges->conge_end_period, 'monteur' => $infosConges->monteur));

                        $sql = "UPDATE absences SET presence = 'A' WHERE date > :start AND date < :end AND monteur=:monteur";
                        $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));

                    } else if ($infosConges->conge_start_period == 'AM' AND  $infosConges->conge_end_period == 'AM') {
                        $sql = "UPDATE absences SET presence = 'A' WHERE date = :date AND periode = :period AND monteur=:monteur";
                        $db->exec($sql, array('date' => $infosConges->conge_end, 'period' => $infosConges->conge_end_period, 'monteur' => $infosConges->monteur));

                        $sql = "UPDATE absences SET presence = 'A' WHERE date >= :start AND date < :end AND monteur=:monteur";
                        $db->exec($sql, array('start' => $infosConges->conge_start, 'end' => $infosConges->conge_end, 'monteur' => $infosConges->monteur));
                    }
                    
                    $db->delete('conges', array('idconge' => $idconge));
                    
                    $result = array('error' => 0);
                }
                
                break;
            
            case 'getEmployesAgence':
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : '';

                if($agence !== '') {

                    $dbExt = new connec_ext();
                    $employes = $dbExt->listEmployes($agence);
                    
                    $list = array();
                    foreach($employes AS $e) {
                        $nom = trim(utf8_encode($e->nom_magasinier));
                        $list[] = array('id' => trim($e->c_magasinier), 'value' => $nom );
                    }
                    $result = array('error' => 0, 'employes' => $list);

                }

                break;
                
            case 'calcJoursConges' :
                
                $start = isset($post['start']) ? htmlentities($post['start']) : '';
                $end = isset($post['end']) ? htmlentities($post['end']) : '';
                $debPeriod = isset($post['debPeriod']) ? htmlentities($post['debPeriod']) : '';
                $endPeriod = isset($post['endPeriod']) ? htmlentities($post['endPeriod']) : '';                
                $monteur = isset($post['monteur']) ? htmlentities($post['monteur']) : '';
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : '';
                
                if($start != '' AND $end != '' AND $monteur != '' AND $agence != '') {
                    $functions = new functions();
                    $db = new connec();
                    
                    $startEn = $functions->dateEn($start, '/', '-');
                    $endEn = $functions->dateEn($end, '/', '-');
                    $infos = $db->getInfosEmploye($monteur, $agence);
                    $idemploye = isset( $infos->idmonteur) ? $infos->idmonteur : 0;
                    $ranges = $db->checkIfDateRange($startEn, $endEn, $idemploye);
                    
                    $inRange = ($ranges AND count($ranges) > 0);
                    $congeOverlap = ($inRange) ? 'Du '.$functions->dateFr($ranges->conge_start, '-').' au '.$functions->dateFr($ranges->conge_end, '-') : '';
                    
                    if($debPeriod == 'am' AND $endPeriod == 'am') {
                        $start .= ' 08:00:00';
                        $end .= ' 12:00:00';
                    } else if($debPeriod == 'am' AND $endPeriod == 'pm') {
                        $start .= ' 08:00:00';
                        $end .= ' 19:00:00';
                    } else if($debPeriod == 'pm' AND $endPeriod == 'am') {
                        $start .= ' 14:00:00';
                        $end .= ' 12:00:00';
                    } else if($debPeriod == 'pm' AND $endPeriod == 'pm') {
                        $start .= ' 14:00:00';
                        $end .= ' 19:00:00';
                    }
                    
                    $start = new DateTime( $functions->dateEn($start) );
                    $end = new DateTime( $functions->dateEn($end) );
                    $sameDay = ($startEn == $endEn);
                    
                    $infosEmploye = $db->getInfosEmploye($monteur, $agence);

                    $dateDebutCycle = isset($infosEmploye->planning_date_debut) ? $infosEmploye->planning_date_debut : '';

                    // Recup des jours de repos
                    $repos = array();
                    $joursRepos = array();
                    $reposTemp = $db->getReposEmploye($monteur, $agence);
                    foreach($reposTemp AS $r) {
                        if(!isset($repos[$r->ordre])) {
                            $repos[$r->ordre] = array();
                        }
                        if(!isset($repos[$r->ordre][$r->day])) {
                            $repos[$r->ordre][$r->day] = array();
                        }
                        $repos[$r->ordre][$r->day] = array('am' => ($r->am_repos==1), 'pm' => ($r->pm_repos==1));

                        if($r->am_repos == 1 OR $r->pm_repos == 1) {
                            if(!isset($joursRepos[$r->day])) {
                                $joursRepos[$r->day] = true;
                            }
                        }
                    }
                    $totSemaine = count($repos);
                    
                    $holidays = $functions->getHolidays();

                    $interval = DateInterval::createFromDateString('1 day');
                    
                    $diff = $start->diff($end);
                    $nbJours = $diff->format('%a');
                    $nbHours = (int) $diff->format('%h');
                    $nbJours += ($nbHours == 4 OR $nbHours == 5) ? 0.5 : (($nbHours == 11 OR $nbHours == 22) ? 1 : 0);
                    
                    $nbSemainesPoses = (int) ($nbJours / 7);
                    
                    $resteJoursPoses = $nbJours % 7;
                    
                    $joursFeries = array();
                    // On pose plus d'une semaine
                    if($nbSemainesPoses > 0) {
                        $nbJours = $nbSemainesPoses * 5;
                        
                        $endTemp = clone $start;
                        $endTemp = $endTemp->add(new DateInterval('P'.( ($nbSemainesPoses * 7) - 1).'D'));
                        $period = new DatePeriod($start, $interval, $endTemp);
                        
                        foreach ( $period as $dt ) {
                            $temp = $dt->format('m-d');
                            // Verif jours fériés
                            if (in_array($temp,$holidays)) { // c'est un jour férié
                                $nbReposTemp = calcRepos($dateDebutCycle, $totSemaine, $dt, $repos, $sameDay, $debPeriod, $endPeriod);
                                
                                if($nbReposTemp === 0) { // et pas un jour de repos
                                    $joursFeries[] = $dt->format('Y-m-d');
                                    $nbJours--;
                                } else if ($nbReposTemp > 0) { // demi journée de repos
                                    $joursFeries[] = $dt->format('Y-m-d');
                                    $nbJours -= $nbReposTemp;
                                }
                            }
                        }
                        
                        // on continue le calcul après les semaines complètes
                        $start = clone $endTemp;
                        $start = $start->add(new DateInterval('P1D'));
                    } else {
                        $nbJours= 0;
                    }
                    
                    $period = new DatePeriod($start, $interval, $end);
                    
                    $diff = $start->diff($end);
                    $nbJours += $diff->format('%a');
                    $nbHours = (int) $diff->format('%h');
                    $nbJours += ($nbHours == 4 OR $nbHours == 5) ? 0.5 : (($nbHours == 11 OR $nbHours == 22) ? 1 : 0);
                    
                    $nbRepos = 0;
                    foreach ( $period as $dt ) {
                        $remove = 0;
                        $temp = $dt->format('m-d');
                        
                        // Verif dimanche
                        if($dt->format('w') == 0) {
                            $remove = 1;
                        } else {
                            $nbReposTemp = calcRepos($dateDebutCycle, $totSemaine, $dt, $repos, $sameDay, $debPeriod, $endPeriod);
                            
                            // Verif jours fériés
                            if (in_array($temp,$holidays) AND $nbReposTemp === 0) {
                                $joursFeries[] = $dt->format('Y-m-d');
                                $remove = 1;
                            }

                            // Verif repos
                            if($nbReposTemp > 0) {
                                $remove = $nbReposTemp;
                                $nbRepos += $nbReposTemp;
                            }
                        }

                        $nbJours -= $remove;
                    }
                               
                    $input = '<input type="text" name="calcNbJours" class="input-xsmall" value="'.$nbJours.'" />';
                    $conges = $input.' jour'.($nbJours > 1 ? 's' : '').' de congés';
                    $nbFeries = count($joursFeries);
                    if($nbFeries > 0 OR $nbRepos > 0) {
                        $conges.= ' ( exclu ';
                        $conges .= ($nbFeries > 0) ? $nbFeries.' jour'.($nbFeries > 1 ? 's' : '').' férié'.($nbFeries > 1 ? 's' : '') : '';
                        $conges .= ($nbRepos > 0) ? (( $nbFeries >0 ) ? ' et ' : '').$nbRepos.' jour'.($nbRepos > 1 ? 's' : '').' de repos' : ''; 
                        $conges.= ')';
                    }
                    
                    if($inRange) {
                        $conges = '<i style="color:red">Un congé est déjà programmé à cette période ! ('.$congeOverlap.')</i><br />'.$conges;
                    }
                    $result = array('error' => 0, 'txtconges' => $conges, 'conges' => $nbJours, 'feries' => $nbFeries, 'repos' => $nbRepos);

                }
                
                break;
                
            case 'calcJoursCongesOLD' :
                
                $start = isset($post['start']) ? htmlentities($post['start']) : '';
                $end = isset($post['end']) ? htmlentities($post['end']) : '';
                $debPeriod = isset($post['end']) ? htmlentities($post['debPeriod']) : '';
                $endPeriod = isset($post['end']) ? htmlentities($post['endPeriod']) : '';                
                $monteur = isset($post['monteur']) ? htmlentities($post['monteur']) : '';
                $agence = isset($post['agence']) ? htmlentities($post['agence']) : '';
                
                if($start != '' AND $end != '' AND $monteur != '' AND $agence != '') {
                    $functions = new functions();
                    
                    // si date fin = 00:00 on passe à 23h59
                    /*if( strstr($end, '00:00')) {
                        $end = str_replace('00:00', '23:59', $end);                        
                    }*/
                    
                    if($debPeriod == 'am' AND $endPeriod == 'am') {
                        $start .= ' 08:00:00';
                        $end .= ' 12:00:00';
                    } else if($debPeriod == 'am' AND $endPeriod == 'pm') {
                        $start .= ' 08:00:00';
                        $end .= ' 19:00:00';
                    } else if($debPeriod == 'pm' AND $endPeriod == 'am') {
                        $start .= ' 14:00:00';
                        $end .= ' 12:00:00';
                    } else if($debPeriod == 'pm' AND $endPeriod == 'pm') {
                        $start .= ' 14:00:00';
                        $end .= ' 19:00:00';
                    }
                    
                    $start = new DateTime( $functions->dateEn($start) );
                    $end = new DateTime( $functions->dateEn($end) );

                    $db = new connec();
                    $infosEmploye = $db->getInfosEmploye($monteur, $agence);

                    $dateDebutCycle = isset($infosEmploye->planning_date_debut) ? $infosEmploye->planning_date_debut : '';

                    if($dateDebutCycle != '') {
                        $madate = new DateTime($dateDebutCycle);
                    }
                    //if($dateDebutCycle != '') {
                        
                        //$firstMonday = clone $madate->modify(('Sunday' == $madate->format('l')) ? 'Monday last week' : 'Monday this week');
                        // Recup des jours de repos
                        $repos = array();
                        $joursRepos = array();
                        $reposTemp = $db->getReposEmploye($monteur, $agence);
                        foreach($reposTemp AS $r) {
                            if(!isset($repos[$r->ordre])) {
                                $repos[$r->ordre] = array();
                            }
                            if(!isset($repos[$r->ordre][$r->day])) {
                                $repos[$r->ordre][$r->day] = array();
                            }
                            $repos[$r->ordre][$r->day] = array('am' => ($r->am_repos==1), 'pm' => ($r->pm_repos==1));

                            if($r->am_repos == 1 OR $r->pm_repos == 1) {
                                if(!isset($joursRepos[$r->day])) {
                                    $joursRepos[$r->day] = true;
                                }
                            }
                        }

                        $totSemaine = count($repos);                    
                    //}

                    $holidays = $functions->getHolidays();

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($start, $interval, $end);
                    
                    $diff = $start->diff($end);
                    $nbJours = $diff->format('%a');
                    $nbHours = (int) $diff->format('%h');
                    $nbJours += ($nbHours == 4 OR $nbHours == 5) ? 0.5 : (($nbHours == 11 OR $nbHours == 22) ? 1 : 0);
                    
                    $nbRepos = 0;
                    $joursFeries = array();
                    foreach ( $period as $dt ) {
                        $add = 1;
                        $remove = 0;
                        $temp = $dt->format('m-d');
                        
                        // Verif jours fériés
                        if (in_array($temp,$holidays)) {
                            $joursFeries[] = $dt->format('Y-m-d');
                            $add = 0;
                            $remove = 1;
                        }

                        $day = $dt->format('N');
                        // Verif repos
                        if($dateDebutCycle != '') {
                            
                            $mondayTemp = new DateTime($dt->format('Y-m-d'));

                            $myMonday = clone $mondayTemp->modify(('Sunday' == $mondayTemp->format('l')) ? 'Monday last week' : 'Monday this week');
                            $interval = $myMonday->diff($madate)->format('%a');

                            $nbSemaines = (int) ($interval / 7);
                            $semaineActive = ($totSemaine > 0) ? ($nbSemaines % $totSemaine)+1 : 1;

                            if(isset($repos[$semaineActive]) AND isset($repos[$semaineActive][$day])) {
                                //echo 'Ok on entre : '.$dt->format('d-m-Y').'<br />';
                                if($repos[$semaineActive][$day]['am'] AND $repos[$semaineActive][$day]['pm']) {
                                    //echo 'Ok +1<br />';
                                    $add = 0;
                                    $remove = 1;
                                    $nbRepos++;
                                } else if ($repos[$semaineActive][$day]['am'] OR $repos[$semaineActive][$day]['pm']) {
                                    //  echo 'Ok +0.5<br />';
                                    $add = 0.5;
                                    $remove = 0.5;
                                    $nbRepos += 0.5;
                                }
                            }
                        } else {
                            $semaineActive = 1;
                            if(isset($repos[$semaineActive]) AND isset($repos[$semaineActive][$day])) {
                                if($repos[$semaineActive][$day]['am'] AND $repos[$semaineActive][$day]['pm']) {
                                    $add = 0;
                                    $remove = 1;
                                    $nbRepos++;
                                } else if ($repos[$semaineActive][$day]['am'] OR $repos[$semaineActive][$day]['pm']) {
                                    $add = 0.5;
                                    $remove = 0.5;
                                    $nbRepos += 0.5;
                                }
                            }
                        }


                        // Verif dimanche
                        if($dt->format('w') == 0) {
                            $add = 0;
                            $remove = 1;
                        }

                        //$nbJours += $add;
                        $nbJours -= $remove;
                    }

                    $conges = $nbJours.' jour'.($nbJours > 1 ? 's' : '').' de congés';
                    $nbFeries = count($joursFeries);
                    if($nbFeries > 0 OR $nbRepos > 0) {
                        $conges.= ' ( exclu ';
                        $conges .= ($nbFeries > 0) ? $nbFeries.' jour'.($nbFeries > 1 ? 's' : '').' férié'.($nbFeries > 1 ? 's' : '') : '';
                        $conges .= ($nbRepos > 0) ? (( $nbFeries >0 ) ? ' et ' : '').$nbRepos.' jour'.($nbRepos > 1 ? 's' : '').' de repos' : ''; 
                        $conges.= ')';
                    }
                    $result = array('error' => 0, 'txtconges' => $conges, 'conges' => $nbJours, 'feries' => $nbFeries, 'repos' => $nbRepos);

                }
                
                break;
        }
        break;
        
    case 'outils':
        
        switch($action) {
        	
        	/*
        	 * non utilisé : renvoyé sur cron/updateListEmployes
        	 */
            case 'majListEmployes':
                $db = new connec();
                $dbExt = new connec_ext();
                $functions = new functions();

                $liste = $dbExt->listEmployes();
                $add = $update = 0;
                foreach($liste AS $l) {
                    $monteur = trim($l->c_magasinier);
                    $nom = utf8_encode(trim($l->nom_magasinier));
                    $agence = trim($l->c_agence);
                    $type_plan = trim($l->c_type_plan);
                    
                    $sql = 'SELECT * FROM monteurs m WHERE c_agence=:agence AND c_magasinier=:monteur';
                    $result = $db->_fetch($sql, array('agence' => $agence, 'monteur' => $monteur));

                    if(isset($result->idmonteur) AND $result->idmonteur > 0) {
                        if($result->nom_magasinier != $nom OR $result->c_type_plan != $type_plan) {
                            $update++;
                            $db->update('monteurs', array('nom_magasinier' => $nom, 'c_type_plan' => $type_plan), array('c_agence' => $agence, 'c_magasinier' => $monteur));
                        }
                    } else {
                        $add++;
                        $db->add('monteurs', array('c_agence' => $agence, 'c_type_plan' => $type_plan, 'c_magasinier' => $monteur,'nom_magasinier' => $nom, 'planning_facul' => 1));
                    }

                }
                
                $result = array('error' => 0, 'add' => $add, 'update' => $update);
                break;
        }
        
        break;
        
    case 'cron':
        if($action == NULL) {
            $action = (isset($_GET['action']) AND $_GET['action'] != NULL) ? htmlentities($_GET['action']) : NULL;
        }
        
        switch($action) {
            
            case 'updateListEmployes' :
            	
                $db = new connec();
                $dbExt = new connec_ext();
                $functions = new functions();

                $liste = $dbExt->listEmployes(null, null);
                $add = $update = 0;
                
                foreach($liste AS $l) {
                	
                    $monteur = trim($l->c_magasinier);
                    $nom = utf8_encode(trim($l->nom_magasinier));
                    $agence = trim($l->c_agence);
                    $type_plan = trim($l->c_type_plan);
                    $actif = trim($l->c_actif) == 'O' ? 1 : 0;
					
                    /* monteurs
                    idmonteur	int(11)	Non
                    c_agence	varchar(5)	Oui 	NULL
                    c_magasinier	varchar(8)	Oui 	NULL
                    c_type_plan	varchar(5)	Oui 	NULL
                    nom_magasinier	varchar(60)	Oui 	NULL
                    planning_facul	tinyint(1)	Oui 	NULL
                    planning_date_debut	date	Oui 	NULL
                    actif	tinyint(1)	Oui 	1
                    */
                    $sql = 'SELECT * FROM monteurs m WHERE c_magasinier=:monteur';
                    $result = $db->_fetch($sql, array('monteur' => $monteur));
/* doublons
SELECT c_magasinier, count(*) FROM `monteurs` group by c_magasinier having count(*) > 1
EHAM	2
JRIT	2
LGON	2
LGUY	2
MBOU	2
XBER	2
SELECT * FROM `monteurs` where c_magasinier in ('EHAM', 'JRIT', 'LGON', 'LGUY', 'MBOU','XBER') order by c_magasinier
 */
                    if(isset($result->idmonteur) && $result->idmonteur > 0) {
                    	
                        if(    $result->actif != $actif 
                        	|| $result->nom_magasinier != $nom 
                        	|| $result->c_agence != $agence 
                        	|| $result->c_type_plan != $type_plan 
                        	) {
                        		
                            $update++;
                            $db->update('monteurs', 
                            			array(
                            				'nom_magasinier' => $nom, 
                            				'actif' => $actif, 
                            				'c_agence' => $agence, 
                            				'c_type_plan' => $type_plan
                            				),
                            			array('c_magasinier' => $monteur)
                            	);
                        }
                    } else {
                        $add++;
                        $db->add('monteurs', 
                        		 array(
                        		 	'c_agence' => $agence, 
                        		 	'actif' => $actif, 
                        		 	'c_type_plan' => $type_plan, 
                        		 	'c_magasinier' => $monteur,
                        		 	'nom_magasinier' => $nom, 
                        		 	'planning_facul' => 1
                        		 	)
                        		);
                    }

	                /* monteurs_plannings
	                idplanning	int(11)	Non
	                monteur	varchar(8)	Non
	                agence	varchar(5)	Non
	                ordre	tinyint(1)	Oui 	1
	                actif	tinyint(1)	Oui 	1
	                date_remove	date	Oui 	NULL
	                */
                    $sql = 'SELECT * FROM monteurs_plannings WHERE monteur=:monteur';
                    $liste = $db->_fetchAll($sql, array('monteur' => $monteur));

                    if ( count($liste) > 0 ) {
                    	
                    	foreach ($liste as $result) {

	                        if(    $result->actif != $actif 
	                        	|| $result->agence != $agence 
	                        	) {
	                        		
	                            $update++;
	                            $db->update('monteurs_plannings', 
	                            			array(
	                            				'actif' => $actif, 
	                            				'agence' => $agence,
	                            				'date_remove' => ($actif ? null : Date('Y-m-d'))
	                            				),
	                            			array('monteur' => $monteur)
	                            	);
	                        }
                    	}
                    }
                }
                
                $result = array('error' => 0, 'add' => $add, 'update' => $update);
                break;
            
            case 'checkValidation':
                $functions = new functions();

                $holidays = $functions->getHolidays();
                $dateTodayShort = Date('m-d');
                
                if(! in_array($dateTodayShort, $holidays) AND intval(date('w')) !== 0) { // si pas férié ni dimanche
                    $db = new connec();
                    
                    $dbExt = new connec_ext();
                    $monteurs = $dbExt->listMonteurs();
                        
                    $listMonteurs = array();
                    foreach($monteurs AS $m) {
                        if(!isset($listMonteurs[$m->c_agence])) {
                            $listMonteurs[$m->c_agence] = array();
                        }
                        $listMonteurs[$m->c_agence][trim($m->c_magasinier)] = utf8_encode(trim($m->nom_magasinier));
                        
                    }
                    
                    $sub = abs($functions->getMinDate());
                    
                    $retards = $db->getAgenceRetard($sub);

                    $retard = $retards['j1'];
                    $agence_temp = $html = '';
                    if( count($retard) > 0) {
                        foreach($retard AS $r) {
                            
                            if($agence_temp != $r->c_agence) {
                                if($agence_temp != '') { // envoi du mail
                                    $html = $functions->setTemplateCron('Saisie de présence', 'Pour aujourd\'hui, vous n\'avez rien saisie pour :', $html);
                                    $functions->sendmail(utf8_decode($sujet), utf8_decode($html), $mail, $to);
                                    $html = '';
                                    echo 'Mail to '.$mail."\n";
                                }
           
                                $mail = $r->mail;
                                $to = $r->nom_agence;
                                $sujet = 'Saisie des présences';
                            }
                            
                            $code_monteur = trim($r->monteur);
                            $monteur = (isset($listMonteurs[$r->c_agence]) AND isset($listMonteurs[$r->c_agence][$code_monteur])) ? $listMonteurs[$r->c_agence][$code_monteur] : $code_monteur;
                            $periode = ($r->periode == 'AM') ? ' le matin' : ' l\'apr&egrave;s midi';
                            $html .= '<b>'.$monteur.'</b> le '.$r->datefr.$periode.'<br />';
                            
                            $agence_temp = $r->c_agence;
                        }
                        $html = $functions->setTemplateCron('Saisie de présence', 'Pour aujourd\'hui, vous n\'avez rien saisie pour :', $html);
                        $functions->sendmail(utf8_decode($sujet), utf8_decode($html), $mail, $to);
                    }
                    $retard = $retards['j2'];
                    
                    if( count($retard) > 0) {
                        
                        $agences = $html = '';
                        foreach($retard AS $r) {
                            $agences .= '<b>'.$r->nom_agence.'</b><br />';
                        }
                        
                        // envoi mail comptable;
                        $mail = MAIL_COMPTABLE;
                        
                        $to = 'Comptabilité Groupe Garrigue';
                        $sujet = 'Saisie des présences';
                        $html = $functions->setTemplateCron('Saisie de présence', 'Des agences ont des saisies de présences en attente depuis 48H, vous retrouverez la liste ci-dessous :', $agences);
                        $functions->sendmail(utf8_decode($sujet), utf8_decode($html), $mail, $to);
                        
                        echo 'Mail to '.$mail."\n";
                    }
                    
                    $result = array();
                }
                
                break;
            
        }
        
        break;
}

echo json_encode($result);