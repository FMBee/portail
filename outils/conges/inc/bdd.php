<?php

class connec_ext {
    
    protected $_host = '10.106.76.111';
    protected $_port = '1433';
    protected $_user = 'sa';
    protected $_pass = 'Logiwin06';
          
    public function __construct ($bdd='winpneu') {
        try {
            if(LOCAL_MODE) {
                $this->_host = '84.55.161.189';
            }
            $this->con_ext = new PDO('dblib:host='.$this->_host.':'.$this->_port.';dbname='.$bdd.';charset=utf8', $this->_user, $this->_pass);
            $this->con_ext->exec("SET CHARACTER SET utf8");
        } catch(Exception $e) {
            echo 'Une erreur est survenue !'.$e->getMessage();
            die();
        }        
    }
    
     public function fetch($sql, $params=NULL, $fetch=PDO::FETCH_OBJ) {
        $prep = $this->con_ext->prepare($sql);
        $prep->execute($params);
        return $prep->fetch($fetch);
    }
    
    public function fetchAll($sql, $params=NULL) {
        $prep = $this->con_ext->prepare($sql);
        $prep->execute($params);
        $prep->setFetchMode(PDO::FETCH_OBJ);
        return $prep;
    }
    
    public function exec($sql, $params) {
        $prep = $this->con_ext->prepare($sql);
        return $prep->execute($params);
    }
    
    public function update($table, $champs, $where) {
        $params = array();
        
        $sql = 'UPDATE '.$table.' SET ';
        foreach($champs AS $c => $val) {
            $sql .= '`'.$c.'` = :'.$c.', ';
            $params[$c] = $val;
        }
        $sql = substr($sql, 0, strlen($sql) - 2);
        
        if( count($where) > 0) {
            $sql .= ' WHERE ';
            foreach($where AS $c => $val) {
                $sql .= '`'.$c.'` = :'.$c.' AND ';
                $params[$c] = $val;
            }
            $sql = substr($sql, 0, strlen($sql) - 4);
        }
        
        return $this->exec($sql, $params);
    }
    
    public function add($table, $champs) {
        $params = array();
        
        $sql = 'INSERT INTO '.$table.' (';
        
        foreach($champs AS $c => $val) {
            $sql .= '`'.$c.'`, ';
            $params[$c] = $val;
        }
        $sql = substr($sql, 0, strlen($sql)-2);
        $sql .= ') VALUES (';
        
        foreach($champs AS $c => $val) {
            $sql .= ':'.$c.', ';
        }
        $sql = substr($sql, 0, strlen($sql)-2);
        
        $sql .=')';

        $j = $this->exec($sql, $params);
        
        return $this->connexion->lastInsertId();
        
    }
    
    public function delete($table, $where) {
        $params = array();
        
        $sql = 'DELETE FROM '.$table.' WHERE ';
        
        foreach($where AS $c => $val) {
            $sql .= '`'.$c.'` = :'.$c.' AND ';
            $params[$c] = $val;
        }
        $sql = substr($sql, 0, strlen($sql) - 4);
        
        return $this->exec($sql, $params);
    }
    
    
    /***************** Plannings ********************************************/
    
    public function listMonteurs($agence=NULL) {

        $params = array();
        
        $sql = 'SELECT * FROM magasinier WHERE c_actif = \'O\' AND c_type_plan <> \'03\'';
        
        if($agence !== NULL) {
            $params = array('agence' => $agence);
            $sql .= ' AND c_agence = :agence';
        }
        
        return $this->fetchAll($sql, $params);
    }
    
    public function listEmployes($agence=NULL, $actif='O') {
        
        $params = array();
        
        $sql = 'SELECT m.*, a.nom_agence FROM magasinier m LEFT JOIN agence a ON a.c_agence=m.c_agence';
        
        if($actif != null) {
            $sql .= ' WHERE m.c_actif = :actif';
            $params['actif'] = $actif;
        }
        
        if($agence !== NULL) {
            $params['agence'] = $agence;
            $sql .= (($actif != null) ? ' AND' : ' WHERE').' m.c_agence = :agence';
            $sql .= ' ORDER BY m.nom_magasinier';
        } else {
            $sql .= ' ORDER BY m.c_agence, m.nom_magasinier';
        }
        
        return $this->fetchAll($sql, $params);
    }
    
    
    public function findEmpWithoutPlanning($employes, $agence=NULL) {

        $params = array();
        
        $sql = 'SELECT * FROM magasinier WHERE c_actif = \'O\'';
        
        if($employes !== NULL) {
            $sql .= ' AND c_magasinier NOT IN ('.$employes.')';
        }
        
        if($agence !== NULL) {
            $params['agence'] = $agence;
            $sql .= ' AND c_agence = :agence';
        }
        
        $sql .= ' ORDER BY nom_magasinier';
        
        return $this->fetchAll($sql, $params);
    }
    
    /************************************************************************/
    
}

class connec {
    
    protected $_host = 'localhost';
    //protected $_port = '3306';
    protected $_bdd = 'portail';
    protected $_user = 'dist';
    protected $_pass = 'NGM4VDpcxh3JCHZQ';
    
    public function __construct () {
        try {
            if(LOCAL_MODE) {
                $this->_user = 'garrigue';
                $this->_pass = 'G5tPDzb8qa2DFHGe';
            }
            $this->connexion = new PDO('mysql:host='.$this->_host.';dbname='.$this->_bdd, $this->_user, $this->_pass);
            $this->connexion->exec("SET CHARACTER SET utf8");
        } catch(Exception $e) {
            echo 'Une erreur est survenue ! '.$e->getMessage();
            die();
        }
    }
    
    public function lastId() {
        return $this->connexion->lastInsertId();
    }
    
    public function _fetch($sql, $params=NULL, $fetch=PDO::FETCH_OBJ) {
        $prep = $this->connexion->prepare($sql);
        $prep->execute($params);
        return $prep->fetch($fetch);
    }
    
    public function _fetchAll($sql, $params=NULL, $fetchMode=PDO::FETCH_OBJ) {
        $prep = $this->connexion->prepare($sql);
        $prep->execute($params);
        $prep->setFetchMode($fetchMode);
        return $prep->fetchAll();
    }
    
    public function exec($sql, $params) {
        try {
            $prep = $this->connexion->prepare($sql);
            return $prep->execute($params);
        } catch(Exception $e) {
            echo 'Erreur : '.$e->getMessage();
        }
    }
    
    
    public function update($table, $champs, $where) {
        $params = array();
        
        $sql = 'UPDATE '.$table.' SET ';
        foreach($champs AS $c => $val) {
            $sql .= '`'.$c.'` = :'.$c.', ';
            $params[$c] = $val;
        }
        $sql = substr($sql, 0, strlen($sql) - 2);
        
        if( count($where) > 0) {
            $sql .= ' WHERE ';
            foreach($where AS $c => $val) {
                $sql .= '`'.$c.'` = :'.$c.' AND ';
                $params[$c] = $val;
            }
            $sql = substr($sql, 0, strlen($sql) - 4);
        }
        
        return $this->exec($sql, $params);
    }
    
    public function add($table, $champs) {
        $params = array();
        
        $sql = 'INSERT INTO '.$table.' (';
        
        foreach($champs AS $c => $val) {
            $sql .= '`'.$c.'`, ';
            $params[$c] = $val;
        }
        $sql = substr($sql, 0, strlen($sql)-2);
        $sql .= ') VALUES (';
        
        foreach($champs AS $c => $val) {
            $sql .= ':'.$c.', ';
        }
        $sql = substr($sql, 0, strlen($sql)-2);
        
        $sql .=')';

        $j = $this->exec($sql, $params);
        
        return $this->connexion->lastInsertId();
        
    }
    
    public function delete($table, $where) {
        $params = array();
        
        $sql = 'DELETE FROM '.$table.' WHERE ';
        
        foreach($where AS $c => $val) {
            $sql .= '`'.$c.'` = :'.$c.' AND ';
            $params[$c] = $val;
        }
        $sql = substr($sql, 0, strlen($sql) - 4);
        
        return $this->exec($sql, $params);
    }
    
    /***************** Divers ***********************************************/
    public function find_agence($ip) {
        $sql = "SELECT id_agence, c_agence, nom_agence FROM agences WHERE ip = :ip";

        $params['ip'] = $ip;
        
        return $this->_fetch($sql, $params);
    }
    
    public function listMonteurs() {
        $sql = "SELECT * FROM monteurs";

        return $this->_fetchAll($sql);
    }
    
    public function getAgences() {
        $sql = "SELECT * FROM agences where ((flag <> 'D') or (flag IS NULL)) ORDER BY nom_agence";

        return $this->_fetchAll($sql);
    }
    
    public function getUser($ip) {
        $sql = 'SELECT * FROM users WHERE ip = :ip';
        $params = array('ip' => $ip);
        
        return $this->_fetch($sql, $params);
    }
    
    public function getExchangeCal($agence) {
        $sql = 'SELECT * FROM calendriers WHERE c_agence = :agence';
        $params = array('agence' => $agence);
        
        return $this->_fetch($sql, $params);
    }
    
    public function getInfosEmploye($employe, $agence=null) {
        $sql = 'SELECT * FROM monteurs WHERE c_magasinier=:employe';
        $params = array('employe' => $employe);
        
        if($agence != null) {
            $sql .= ' AND c_agence = :agence';
            $params['agence'] = $agence;
        }
        
        return $this->_fetch($sql, $params);
    }
    
    public function getInfosAgence($agence) {
        $sql = 'SELECT * FROM agences WHERE c_agence = :agence';
        $params = array('agence' => $agence);
        
        return $this->_fetch($sql, $params);
    }
    
    /************************************************************************/
    
    /***************** Evènements *******************************************/
    
    public function listEvents() {
        $sql = 'SELECT e.*, a.nom_agence, ea.idagence, DATE_FORMAT(event_start, \'%d-%m-%Y\') AS event_start_fr, DATE_FORMAT(event_end, \'%d-%m-%Y\') AS event_end_fr FROM events e
                INNER JOIN events_agences ea ON ea.idevent=e.idevent
                INNER JOIN agences a ON a.id_agence=ea.idagence
                ORDER BY e.event_start, e.event_end';

        return $this->_fetchAll($sql, null);
    }
    
    public function getEventsAgence($agence) {
        $sql = 'SELECT e.* FROM events e
                INNER JOIN events_agences ea ON ea.idevent=e.idevent
                INNER JOIN agences a ON a.id_agence=ea.idagence
                WHERE a.c_agence = :agence';
        
        $params = array('agence' => $agence);
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function getInfosEvent($event) {
        $sql = 'SELECT e.*, DATE_FORMAT(event_start, \'%d-%m-%Y\') AS event_start_fr, DATE_FORMAT(event_end, \'%d-%m-%Y\') AS event_end_fr, a.c_agence, a.id_agence, a.nom_agence FROM events e
                LEFT JOIN events_agences ea ON ea.idevent=e.idevent
                LEFT JOIN agences a ON a.id_agence=ea.idagence
                WHERE e.idevent = :event';
        
        $params = array('event' => $event);
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function getAgencesEvent($event) {
        $sql = 'SELECT * FROM events_agences WHERE idevent = :event';
        
        $params = array('event' => $event);
        
        return $this->_fetchAll($sql, $params);
    }
    /************************************************************************/
    
    /***************** Plannings ********************************************/
    
    public function planningEvents($monteur, $start=NULL, $end=NULL) {

        $params = array();
        
        $sql = 'SELECT * FROM plannings_events AS pe WHERE pe.monteur = :monteur';
        $params['monteur'] = $monteur;
        
        if($start !== NULL) {
            $params['start'] = $start;
            $sql .= ' AND pe.datestart >= :start';
        }
        if($end !== NULL) {
            $params['end'] = $end;
            $sql .= ' AND pe.dateend <= :end';
        }
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function infoEvent($event) {
        
        $sql = 'SELECT * FROM plannings_events AS pe WHERE pe.idplanningevent = :event';
        $params = array('event' => (int) $event);
        
        return $this->_fetch($sql, $params);
    }
    
    public function addEvent($monteur, $title, $start, $end, $allDay=0) {
        
        $datas = array('monteur' => $monteur, 'title' => $title, 'datestart' => $start, 'dateend' => $end, 'allDay' => $allDay);

        $id = $this->add('plannings_events', $datas);
        return $id;        
    }
    
    public function editEvent($id, $title, $start, $end, $allDay=0) {
        
        $datas = array( 'title' => $title, 'datestart' => $start, 'dateend' => $end, 'allDay' => $allDay);
        $where = array('idplanningevent' => (int) $id);
        
        $id = $this->update('plannings_events', $datas, $where);
        return $id;        
    }
    
    public function getPlanningsAgence($agence, $monteur=null) {
        
        $params = array('agence' => $agence);
        $where = '';
        if($monteur != null) {
            $where = ' AND mp.monteur=:monteur';
            $params['monteur'] = $monteur;
        }
        
        $sql = "SELECT * ,
            IF(am_repos, '0#0', TIME_FORMAT( TIMEDIFF(am_end, am_start), '%k#%i')) AS am_diff, 
            IF(pm_repos, '0#0', TIME_FORMAT( TIMEDIFF(pm_end, pm_start), '%k#%i')) AS pm_diff 
            FROM `monteurs_plannings` mp
            INNER JOIN planning_days pd ON mp.idplanning=pd.idplanning
            WHERE mp.actif=1 AND mp.agence = :agence".$where."
            ORDER BY mp.agence, mp.monteur, mp.ordre, pd.day";

        return $this->_fetchAll($sql, $params);
    }
    
    public function getReposEmploye($employe, $agence) {
        $sql = "SELECT mp.idplanning, mp.ordre, day, am_repos, pm_repos
            FROM `monteurs_plannings` mp
            INNER JOIN planning_days pd ON mp.idplanning=pd.idplanning
            WHERE mp.actif=1 AND mp.agence = :agence AND mp.monteur=:employe AND (am_repos = 1 OR pm_repos=1)
            ORDER BY mp.ordre";
        
        $params = array('agence' => $agence, 'employe' => $employe);
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function getEmployesPlanning($agence) {
        $sql = "SELECT DISTINCT monteur FROM `monteurs_plannings` 
            WHERE actif=1 AND agence = :agence";
        
        $params = array('agence' => $agence);
        
        return $this->_fetchAll($sql, $params);
    }
    /************************************************************************/
    
    /***************** Absences *********************************************/
    
    public function getFilterAnormal($agence, $start, $end) {
        $sql = "SELECT a.id, a.c_agence, a.monteur, 
        (SELECT COUNT(id)/2 FROM absences a2 WHERE a2.monteur=a.monteur AND a2.c_agence=a.c_agence AND a2.date BETWEEN :start AND :end AND a2.presence='P') AS nbPresences,
        (SELECT COUNT(id)/2 FROM absences a2 WHERE a2.monteur=a.monteur AND a2.c_agence=a.c_agence AND a2.date BETWEEN :start AND :end AND a2.presence='R') AS nbRepos
        FROM `absences` a
        WHERE a.date BETWEEN :start AND :end
        AND a.c_agence= :agence
        GROUP BY monteur
        ORDER BY a.date";
        
        $params = array('agence' => $agence, 'start' => $start, 'end' => $end);
        
        return $this->_fetchAll($sql, $params);
        
    }
    
    public function getCongesInPresence($agence, $start, $end) {
        $sql = "
        SELECT *, DATE_FORMAT(a.date, '%Y-%m-%d') AS dateen, DATE_FORMAT(a.date, '%d-%m-%Y') AS datefr FROM `absences` a WHERE a.presence='P'
        AND a.date BETWEEN :start AND :end
        AND a.date  IN (
                SELECT CAST( 
                        (conge_start+INTERVAL (H+T+U) DAY) AS datetime) d
                FROM ( SELECT 0 H
                        UNION ALL SELECT 100 UNION ALL SELECT 200 UNION ALL SELECT 300
                  ) H CROSS JOIN ( SELECT 0 T
                        UNION ALL SELECT  10 UNION ALL SELECT  20 UNION ALL SELECT  30
                        UNION ALL SELECT  40 UNION ALL SELECT  50 UNION ALL SELECT  60
                        UNION ALL SELECT  70 UNION ALL SELECT  80 UNION ALL SELECT  90
                  ) T CROSS JOIN ( SELECT 0 U
                        UNION ALL SELECT   1 UNION ALL SELECT   2 UNION ALL SELECT   3
                        UNION ALL SELECT   4 UNION ALL SELECT   5 UNION ALL SELECT   6
                        UNION ALL SELECT   7 UNION ALL SELECT   8 UNION ALL SELECT   9
                  ) U
                  , conges c
                  LEFT JOIN monteurs m ON m.idmonteur=c.idemploye 
                WHERE
                    (c.conge_start+INTERVAL (H+T+U) DAY) <= (c.conge_end)
                    AND m.c_agence=a.c_agence AND m.c_magasinier=a.monteur
        ) AND a.c_agence=:agence";
        
        $params = array('agence' => $agence, 'start' => $start, 'end' => $end);
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function getMonteurNoConge($date, $agence) {
        $sql = "SELECT m.*
            FROM monteurs m
            LEFT JOIN conges c ON m.idmonteur=c.idemploye AND (c.conge_start LIKE :date OR c.conge_end LIKE :date)
            WHERE  m.c_agence=:agence AND m.c_type_plan = '03' AND m.actif=1
            GROUP BY m.c_magasinier
            HAVING COUNT(c.idconge)=0";
        
        $params = array('agence' => $agence, 'date' => $date);
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function verifAbsences($date) {
        $sql = 'SELECT * FROM absences WHERE date = :date LIMIT 1';
        $params = array('date' => $date);
        
        return $this->_fetch($sql, $params);
    }
    
    public function completeAbsences($date, $agence, $monteur) {
        $sql = 'SELECT monteur FROM absences WHERE date = :date AND monteur = :monteur AND c_agence= :agence AND periode=\'AM\' LIMIT 1';
        $params = array('date' => $date, 'monteur' => $monteur, 'agence' => $agence);
        $exist = $this->_fetch($sql, $params);
        
        if( ! isset($exist->monteur) OR $exist->monteur == NULL) {
            $datas = array('date' => $date, 'c_agence' => $agence, 'periode' => 'AM', 'monteur' => $monteur, 'presence' => 'A');
            $this->add('absences', $datas);
        }
        
        $sql = 'SELECT monteur FROM absences WHERE date = :date AND monteur = :monteur AND c_agence= :agence AND periode=\'PM\' LIMIT 1';
        $params = array('date' => $date, 'monteur' => $monteur, 'agence' => $agence);
        $exist = $this->_fetch($sql, $params);
        
        if( ! isset($exist->monteur) OR $exist->monteur == NULL) {
            $datas = array('date' => $date, 'c_agence' => $agence, 'periode' => 'PM', 'monteur' => $monteur, 'presence' => 'A');
            $this->add('absences', $datas);
        }
    }
    
    public function completeAbsencesOLD($date, $agence, $monteur) {
        $sql = 'SELECT monteur FROM absences WHERE date = :date AND monteur = :monteur AND c_agence= :agence LIMIT 1';

        $params = array('date' => $date, 'monteur' => $monteur, 'agence' => $agence);
        
        $exist = $this->_fetch($sql, $params);
        
        if( ! isset($exist->monteur) OR $exist->monteur == NULL) {
            $datas = array('date' => $date, 'c_agence' => $agence, 'periode' => 'AM', 'monteur' => $monteur, 'presence' => 'A');
            $this->add('absences', $datas);

            $datas = array('date' => $date, 'c_agence' => $agence, 'periode' => 'PM', 'monteur' => $monteur, 'presence' => 'A');
            $this->add('absences', $datas);
        }
    }
    
    public function getPresence($periode, $date, $agence, $monteur=null, $views=null) {
        $sql = 'SELECT * FROM absences WHERE date=:date AND c_agence = :agence AND periode = :periode';
        $params = array('date' => $date, 'periode' => $periode, 'agence' => $agence);
        
        if($monteur != NULL) {
            $sql .= ' AND monteur=:monteur';
            $params['monteur'] = $monteur;
        }
        
        if($views !== null) {
            $sql.= ' AND presence IN (:presence)';
            $params['presence'] = $views;// implode($views, ',');
        }
        
        if($monteur != null) {
            $sql .= ' LIMIT 1';
            return $this->_fetch($sql, $params);
        } else {
            return $this->_fetchAll($sql, $params);
        }
    }
    
    
    public function getPresenceMonth($start, $end, $agence, $views=null, $filters=null, $employe=null) {
        $sql = 'SELECT *, DATE_FORMAT(date, \'%Y-%m-%d\') AS dateen, DATE_FORMAT(date, \'%d-%m-%Y\') AS datefr 
                FROM absences 
                WHERE date BETWEEN :start AND :end AND c_agence = :agence';
        $params = array('start' => $start, 'end' => $end, 'agence' => $agence);
                
        if($views !== null AND is_array($views)) {
            $views = '\''.implode('\',\'', $views).'\'';
            $sql.= ' AND presence IN ('.$views.')';
        }
        
        if(count($filters) >0) {
            if(in_array('valid', $filters) AND !in_array('invalid', $filters)) {
                $sql.= ' AND valider = 1';
            } else if(!in_array('valid', $filters) AND in_array('invalid', $filters)) {
                $sql.= ' AND valider = 0';
            }
        }
        
        if($employe !== null) {
            $sql.= ' AND monteur = :monteur';
            $params['monteur'] = $employe;
        }
        
        $sql .= ' ORDER BY date ASC, monteur, periode';
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function autoRepos($periode, $date, $agence, $monteur) {
        $infos = $this->getPresence($periode, $date, $agence, $monteur);
        $etat = (isset($infos->saisie)) ? (int) $infos->saisie : 0;
        if($etat === 0) {
            $datas = array('presence' => 'R', 'saisie' => 1);
            $where = array('date' => $date, 'c_agence' => $agence, 'periode' => $periode, 'monteur' => $monteur);
            $this->update('absences', $datas, $where);
        }
    }
    
    public function getAgenceRetard($sub, $agence=null) {
    
        $params = null;
        $sql_agence = '';
        if($agence != null) {
            $sql_agence = ' AND a.c_agence=:agence';
            $params = array('agence' => $agence);
        }
        
        $sql ='SELECT a.id, a.c_agence, ag.mail, ag.nom_agence, a.monteur, a.date, a.saisie, DATE_FORMAT(a.date, \'%d/%m\') AS datefr, a.periode, a.presence, a.valider 
                FROM `absences` a INNER JOIN agences ag ON ag.c_agence=a.c_agence
               WHERE a.c_type_plan=\'03\' AND a.saisie = 0 AND a.date = DATE_SUB(\''.Date('Y-m-d').'\', INTERVAL '.($sub-1).' DAY)
               '.$sql_agence.'
               AND a.monteur NOT IN (SELECT m.c_magasinier FROM monteurs m WHERE m.planning_facul=1)
               ORDER BY a.c_agence, a.monteur, a.date';
        
        
        /*$sql2 ='SELECT a.c_agence, ag.mail, ag.nom_agence, a.monteur, DATE_FORMAT(a.date, \'%d/%m\') AS datefr, a.periode FROM `absences` a INNER JOIN agences ag ON ag.c_agence=a.c_agence
                WHERE a.saisie = 0 AND a.date <= DATE_SUB(\''.Date('Y-m-d').'\', INTERVAL '.$sub.' DAY)
                AND a.monteur NOT IN (SELECT m.c_magasinier FROM monteurs m WHERE m.planning_facul=1)
                ORDER BY a.c_agence, a.monteur, a.date';*/
        $sql2 ='SELECT DISTINCT a.c_agence, ag.mail, ag.nom_agence FROM `absences` a INNER JOIN agences ag ON ag.c_agence=a.c_agence
                WHERE a.c_type_plan=\'03\' AND a.saisie = 0 AND a.date = DATE_SUB(\''.Date('Y-m-d').'\', INTERVAL '.($sub-1).' DAY)
                AND a.monteur NOT IN (SELECT m.c_magasinier FROM monteurs m WHERE m.planning_facul=1)
                ORDER BY ag.nom_agence';    
        
        $retard = array();
        $retard['j1'] = $this->_fetchAll($sql, $params);
        $retard['j2'] = $this->_fetchAll($sql2);
      
        return $retard;
    }
    
    /************************************************************************/
        
    /***************** Horaires *********************************************/
    
    public function getHoraire($monteur, $agence) {
        $sql = 'SELECT * FROM monteurs_horaires WHERE agence = :agence AND monteur = :monteur LIMIT 1';
        $params = array('agence' => $agence, 'monteur' => $monteur);
        
        return $this->_fetch($sql, $params);
    }
    
    public function getAllHoraires($agence, $monteur=null, $day=NULL, $planning=null) {
        $sql = "SELECT * , 
                IF(am_repos, '0#0', TIME_FORMAT( TIMEDIFF(am_end, am_start), '%k#%i')) AS am_diff, 
                IF(pm_repos, '0#0', TIME_FORMAT( TIMEDIFF(pm_end, pm_start), '%k#%i')) AS pm_diff 
                FROM monteurs m
                INNER JOIN monteurs_plannings mp ON m.c_magasinier=mp.monteur
                INNER JOIN planning_days pd ON pd.idplanning=mp.idplanning
                WHERE m.c_agence = :agence";
        
        $params = array('agence' => $agence);
        if($monteur != NULL) {
            $sql .= ' AND m.c_magasinier=:monteur AND mp.idplanning=:planning';
            $params['monteur'] = $monteur;
            $params['planning'] = $planning;
        }
        
        if($day > 0) {
            $sql .= ' AND pd.day=:day';
            $params['day'] = (int) $day;
            return $this->_fetch($sql, $params);
            
        } else {
            $sql .= ' ORDER BY pd.day';
            return $this->_fetchAll($sql, $params);
            
        }
    }
    
    public function getAllHorairesOLD($agence, $monteur=null, $day=NULL) {
        $sql = "SELECT * , 
                IF(am_repos, '0#0', TIME_FORMAT( TIMEDIFF(am_end, am_start), '%k#%i')) AS am_diff, 
                IF(pm_repos, '0#0', TIME_FORMAT( TIMEDIFF(pm_end, pm_start), '%k#%i')) AS pm_diff 
                FROM monteurs_horaires
                WHERE agence = :agence";
        $params = array('agence' => $agence);
        
        if($monteur != NULL) {
            $sql .= ' AND monteur = :monteur';
            $params['monteur'] = $monteur;
        }
        
        if($day > 0) {
            $sql .= ' AND day = :day';
            $params['day'] = (int) $day;
            return $this->_fetch($sql, $params);
            
        } else {
            $sql .= ' ORDER BY day';
            return $this->_fetchAll($sql, $params);
            
        }
        
    }
    
    public function addDefaultHoraire($monteur, $agence) {
        
        $table = 'monteurs_horaires';
        $datas = array('monteur' => $monteur, 'agence' => $agence);
        
        for($i=1; $i<7; $i++) {
            $datas['day'] = $i;
            $this->add($table, $datas);
        }
    }
    
    public function convertPlanning($monteur, $agence) {

        $sql = 'INSERT INTO monteurs_plannings (monteur, agence) VALUES (:monteur, :agence)';
        $this->exec($sql, array('monteur' => $monteur, 'agence' => $agence));
        $id = $this->lastId();
        
        $sql = 'INSERT INTO planning_days (idplanning, day, am_start, am_end, am_repos, pm_start, pm_end, pm_repos) 
            SELECT :planning, day, am_start, am_end, am_repos, pm_start, pm_end, pm_repos FROM monteurs_horaires
            WHERE monteur=:monteur AND agence=:agence';
        
        $id = $this->exec($sql, array('planning' => $id, 'monteur' => $monteur, 'agence' => $agence));
        
        return true;
    }
    
    public function addDefaultPlanning($monteur, $agence) {
        $params = array('monteur' => $monteur, 'agence' => $agence);
        
        $sql = 'SELECT MAX(ordre)+1 AS ordre FROM `monteurs_plannings` WHERE agence=:agence AND monteur=:monteur';
        $result = $this->_fetch($sql, $params);
        $ordre = (isset($result->ordre)) ? $result->ordre : 1;
        
        $sql = 'INSERT INTO monteurs_plannings (monteur, agence, ordre) VALUES (:monteur, :agence, :ordre)';
        $params['ordre'] = $ordre;
        $this->exec($sql, $params);
        $id = $this->lastId();
        
        $table = 'planning_days';
        $datas = array('idplanning' => $id);
        
        for($i=1; $i<7; $i++) {
            $datas['day'] = $i;
            $this->add($table, $datas);
        }
        
        return true;
    }
    
    public function delPlanning($planning) {

        $sql = 'SELECT * FROM `monteurs_plannings` WHERE idplanning=:idplanning';
        $result = $this->_fetch($sql, array('idplanning' => $planning));
        
        $table = 'monteurs_plannings';
        $datas = array('idplanning' => $planning);
        
        //$this->delete($table, $datas);
        $this->update($table, array('actif' => 0, 'date_remove' => Date('Y-m-d')), $datas);
        
        $params = array('monteur' => $result->monteur, 'agence' => $result->agence, 'ordre' => $result->ordre);
        $sql = 'UPDATE monteurs_plannings SET ordre=ordre-1 WHERE ordre > :ordre AND monteur=:monteur AND agence=:agence AND actif=1';
        $this->exec($sql, $params);
        
        return true;
    }

    public function getDiffRecup($agence, $start=null, $end=null) {
        $params = array('agence' => $agence);
        if($start != null AND $end != null) {
            $where = ' AND a.date >= :start AND a.date <= :end ';
            $params['start'] = $start;
            $params['end'] = $end;
        }
        
        $sql = "
        SELECT DISTINCT a.presence, a.periode, DATE_FORMAT(date, '%d-%m-%Y') AS datefr, a.c_agence, a.monteur, m.nom_magasinier AS nomEmploye, 
        ( SELECT count(a2.presence) FROM absences a2 WHERE a2.c_agence=a.c_agence AND a2.monteur=a.monteur AND a2.presence='DJR') AS nbRecup,
        ( SELECT count(a2.presence) FROM absences a2 WHERE a2.c_agence=a.c_agence AND a2.monteur=a.monteur AND a2.presence='DJS') AS nbSup
        FROM absences a
        LEFT JOIN monteurs m ON m.c_magasinier=a.monteur
        WHERE a.presence IN('DJR', 'DJS') AND m.actif=1
        AND a.c_agence=:agence".$where."
        HAVING nbRecup!=nbSup";
        
        return $this->_fetchAll($sql, $params);
    }

    /************************************************************************/
    
    
    /***************** Congés ***********************************************/
    public function getConges($start, $end, $agence=null, $employe=null, $type=null) {
        $sql = "SELECT c.*, DATE_FORMAT(conge_start, '%d-%m-%Y') AS conge_startfr, DATE_FORMAT(conge_end, '%d-%m-%Y') AS conge_endfr, 
                m.nom_magasinier AS nomEmploye, m.c_agence, m.c_magasinier AS monteur, m.c_magasinier
                FROM conges c INNER JOIN monteurs m ON m.idmonteur=c.idemploye
                WHERE m.actif=1 AND ( c.conge_start >= :start OR c.conge_end >= :start OR c.conge_end <= :end)";
        $params = array('start' => $start, 'end' => $end);
        
        if($agence != null) {
            $sql .= ' AND m.c_agence = :agence';
            $params['agence'] = $agence;
        }
        
        if($employe > 0) {
            $sql .= ' AND c.idemploye=:employe';
            $params['employe'] = $employe;
        }
        if($type != null) {
            $sql .= ' AND m.c_type_plan=:type';
            $params['type'] = $type;
        }
        $sql .= ' ORDER BY conge_start, idemploye';
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function infosConge($idconge) {
        $sql = "SELECT c.*, a.nom_agence, a.c_agence, DATE_FORMAT(conge_start, '%d-%m-%Y') AS conge_startfr, DATE_FORMAT(conge_end, '%d-%m-%Y') AS conge_endfr, 
                m.nom_magasinier AS nomEmploye, m.c_magasinier AS monteur
                FROM conges c INNER JOIN monteurs m ON m.idmonteur=c.idemploye
                LEFT JOIN agences a ON a.c_agence=m.c_agence
                WHERE c.idconge=:conge";
        $params = array('conge' => $idconge);

        return $this->_fetch($sql, $params);
    }
        
    public function getRangesCongesEmploye($start, $end, $employe) {
        $sql = "SELECT conge_start, conge_end
                FROM conges c 
                WHERE c.idemploye=:employe AND (c.conge_start >= :start OR c.conge_end <= :end)
                ORDER BY conge_start, conge_end";
        $params = array('employe' => $employe, 'start' => $start, 'end' => $end);
        
        return $this->_fetchAll($sql, $params);
    }
    
    public function checkIfDateRange($start, $end, $employe) {
        $sql = "SELECT * FROM conges 
            WHERE idemploye = :employe
            AND IF( :start BETWEEN conge_start AND conge_end OR :end BETWEEN conge_start AND conge_end, 1,0) = 1 ORDER BY conge_start DESC LIMIT 1";
        $params = array('employe' => $employe, 'start' => $start, 'end' => $end);
        
        return $this->_fetch($sql, $params);
    }
    
    /************************************************************************/
}
