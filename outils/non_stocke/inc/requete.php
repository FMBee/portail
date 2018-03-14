<?php

$sql_pieces = "
    select er.c_agence, s.c_art, a.lib_art, s.qte_en_stock, er.date_rec 
    from stock s
    join article a on a.c_art = s.c_art
    join lig_rec lr on lr.c_art = s.c_art
    join ent_rec er on er.n_br = lr.n_br
    where a.c_fam_art not in ('01','02','03','04','10','14','51')
    and a.c_marque <> 'TTOP'
    and s.qte_en_stock <> 0
    and er.c_agence = '$c_agence'
    and s.c_depot = er.c_agence
    and a.c_art not in 
    		(select distinct c_art as c_art2 from stock_art where c_depot = '$c_agence' and qte_mini <> 0 )
    group by er.c_agence, s.c_art, a.lib_art, s.qte_en_stock, er.date_rec
    having MAX(er.date_rec) between GETDATE()-37 and GETDATE()-7
    order by 5, 4 desc
";


$sql_pneus1 = "
    select er.c_agence, a.c_sfam_art, s.c_art, a.lib_art, s.qte_en_stock, er.date_rec 
    from stock s
    join lig_rec lr on lr.c_art = s.c_art
    join ent_rec er on er.n_br = lr.n_br
    join article a on a.c_art = s.c_art
    where a.c_fam_art in ('01','02','03','04')
    and s.qte_en_stock <> 0
    and er.c_agence = '$c_agence'
    and s.c_depot = c_agence
    group by er.c_agence, a.c_sfam_art, s.c_art, a.lib_art, s.qte_en_stock, er.date_rec
    having MAX(er.date_rec) between GETDATE()-37 and GETDATE()-7
    order by 6, 5 desc
";
$sql_pneus2 = "
    select a.c_art, a.c_marque, a.c_sfam_art, a.largeur, a.serie, a.diam, a.ind_charge, a.Ind_charge_jumelee as ind_charge_2,
            a.ind_vit, a.runflat, a.renforce 
    from  article a
    where a.c_art = ?
";
$sql_pneus3 = "
    select d.sfamArt, d.largeur, d.serie, d.diametre, d.indCharge, d.indVitesse, d.rof, d.xl, m.codeMarque, cd.stockMini, a.codeAgence
    from dimension d
    join categorie_dimension cd on d.id = cd.dimension_id 
    join categorie c on cd.categorie_id = c.id
    join categorie_agence ca on c.id = ca.categorie_id
    join agence a on ca.agence_id = a.id
    join marque_gamme mg on c.gamme_id = mg.gamme_id
    join marque m on mg.marque_id = m.id
    where d.sfamArt = ? and d.largeur = ? and d.serie = ? and d.diametre = ? and d.indCharge = ? and d.indVitesse = ? 
    and d.rof = ? and d.xl = ? and m.codeMarque = ? and a.codeAgence = ?
";










/* OLD

$sql_pieces = "

select er.c_agence, s.c_art,a.lib_art, qte_en_stock,convert(varchar(20),MAX(date_rec),103) as 'date_rec' 
from stock s, lig_rec lr, ent_rec er, article a
where lr.n_br=er.n_br
and lr.c_art=s.c_art
and s.c_art=a.c_art
and a.c_fam_art not in ('01','02','03','04','10','14','51')
and a.c_marque <> 'TTOP'
and s.qte_en_stock <> 0
and er.c_agence = '$c_agence'
and s.c_depot = er.c_agence
and a.c_art not in 
		(select distinct c_art as 'c_art2' from stock_art where c_depot = '$c_agence' and qte_mini <>0 )
and a.c_art not in 
		(select distinct c_art as 'c_art3' from lig_fac lf, ent_fac ef
		where lf.n_fac=ef.n_fac
		and ef.c_agence = '$c_agence'
		group by c_art
		having MAX(date_fac) between GETDATE()-37 and GETDATE())
and a.c_art not in 
		(select distinct c_art as 'c_art4' from lig_liv ll, ent_liv el
		where ll.n_bl=el.n_bl
		and el.c_agence = '$c_agence'
		group by c_art
		having MAX(date_liv) between GETDATE()-37 and GETDATE())
and a.c_art not in 
		(select distinct c_art as 'c_art5' from stat_genc sgc
		where sgc.c_agence = '$c_agence'
		group by c_art, n_fac
		having MAX(date_fac) between GETDATE()-37 and GETDATE())
and a.c_art not in 
		(select distinct c_art as 'c_art6' from stat_gen sg
		where sg.c_agence = '$c_agence'
		group by c_art, n_fac
		having MAX(date_fac) between GETDATE()-37 and GETDATE())
group by er.c_agence, s.c_art, a.lib_art, qte_en_stock
having MAX(date_rec) between GETDATE()-37 and GETDATE()-7
order by 5,2";

lister les articles

  - hors familles ('01','02','03','04','10','14','51')
  - hors marque 'TTOP'
  - de l'agence A
  - stockés au dépôt de A
  - dont le stock est non nul
  - non trouvés dans les stock mini <> 0
  - non trouvés dans les 37 derniers jours de facturation
  - non trouvés dans les 37 derniers jours de livraison
  - non trouvés dans les 37 derniers jours de stats (genc)
  - non trouvés dans les 37 derniers jours de stats (gen)
  - réceptionnés entre -37 et -7 jours
  
  
  
  

$sql_pneus = "

select c_agence, s.c_art,a.lib_art, qte_en_stock,convert(varchar(20),MAX(date_rec),103) as 'date_rec' 
from stock s, lig_rec lr, ent_rec er, article a
where lr.n_br=er.n_br
and lr.c_art=s.c_art
and s.c_art=a.c_art
and a.c_fam_art in ('01','02','03','04')
and s.qte_en_stock <> 0
and er.c_agence = '$c_agence'
and s.c_depot = c_agence
and a.c_art not in 
		(select distinct c_art as 'c_art2' from stock_art where c_depot = '$c_agence' and qte_mini <>0 )
and a.c_art not in 
		(select distinct c_art as 'c_art3' from lig_fac lf, ent_fac ef
		where lf.n_fac=ef.n_fac
		and ef.c_agence = '$c_agence'
		group by c_art
		having MAX(date_fac) between GETDATE()-37 and GETDATE())
and a.c_art not in 
		(select distinct c_art as 'c_art4' from lig_liv ll, ent_liv el
		where ll.n_bl=el.n_bl
		and el.c_agence = '$c_agence'
		group by c_art
		having MAX(date_liv) between GETDATE()-37 and GETDATE())
and a.c_art not in 
		(select distinct c_art as 'c_art5' from stat_genc sgc
		where sgc.c_agence = '$c_agence'
		group by c_art, n_fac
		having MAX(date_fac) between GETDATE()-37 and GETDATE())
and a.c_art not in 
		(select distinct c_art as 'c_art6' from stat_gen sg
		where sg.c_agence = '$c_agence'
		group by c_art, n_fac
		having MAX(date_fac) between GETDATE()-37 and GETDATE())
and a.c_art not in 
    (select c_art as 'c_art7' from article
    where c_fam_art = '01'
    and c_sfam_art in ('TH','CH')
    and c_marque in ('MICH','GOAR','DUNL','FULD'))
group by er.c_agence, s.c_art, a.lib_art, qte_en_stock
having MAX(date_rec) between GETDATE()-37 and GETDATE()-7
order by 5,2";

lister les articles

  - des familles ('01','02','03','04')
  - hors marques ('MICH','GOAR','DUNL','FULD') 
  		des categories ('TH','CH')
  			de la famille '01'
  - de l'agence A
  - stockés au dépôt de A
  - dont le stock est non nul
  - non trouvés dans les stock mini <> 0
  - non trouvés dans les 37 derniers jours de facturation
  - non trouvés dans les 37 derniers jours de livraison
  - non trouvés dans les 37 derniers jours de stats (genc)
  - non trouvés dans les 37 derniers jours de stats (gen)
  - réceptionnés entre -37 et -7 jours
  
*/
