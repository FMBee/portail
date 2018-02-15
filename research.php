<?php

	error_reporting(0);
	
	include 'multi-search.php';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Recherche</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.3/dt-1.10.16/fc-3.2.4/datatables.min.css"/>
  
  <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.3/dt-1.10.16/fc-3.2.4/datatables.min.js"></script>

</head>
<body>

<div class="container">

<!--       <div class="row" style="width:1024px;"> -->
 
 			<br />
			<div class="col-lg-10">
			  <h3 class="text-primary">
                <span><b>Portail Intranet - Garrigue</b></span>
	          </h3>
	        </div>
			<div class="col-lg-2">
	          <a href="Research help.pdf" target="_blank">
                <span class="pull-right">
                    <i class="glyphicon glyphicon-info-sign" aria-hidden="true"></i>
                    Consultez l'aide
                </span>
              </a>
			</div>

			<div class="col-lg-12">
				<div class="col-md-8">
	                <label class="form-control">Résultats de la recherche pour <b><?= strtoupper($seek) ?></b></label>
				</div>
				<div class="col-md-4 pull-right">
				
				  <form action="research.php?agence=<?= $agence ?>" id="form-search" method="post">
				  
				  	<input type="text" class="form-control" placeholder="Rechercher"
				  		   id="search" name="search" required>
				  </form>
				  
				</div>
				
	        </div>
			<div class="col-lg-12">
				<br />
			</div>
	        
	        <div class="col-lg-12">
	        
				<ul class="nav nav-tabs">
				
	<?php if ( count($results) == 0 ): ?>
	
				  <span>Aucun résultat</span>
	<?php endif; ?>
				
	<?php foreach ( $results as $result ): ?>
	
		<?php if ( !isset($prem1) ): ?>
				  <li role="presentation" class="active">
		<?php else: ?> 
				  <li role="presentation">
		<?php endif; ?>
				  	<a href="#tab-<?= $result['order'] ?>" data-toggle="tab">
				  	<?= $result['short'] .' (' .count($result['results']) .')' ?>
				  	</a>
				  </li>
		<?php $prem1 = true; ?>					

	<?php endforeach; ?>			

				</ul>
	  	 
	  			<div class="tab-content">

	<?php foreach ( $results as $result ): ?>
		
		<?php if ( !isset($prem2) ): ?>
					<div class="tab-pane fade in active"
		<?php else: ?> 
					<div class="tab-pane fade"
		<?php endif; ?>
						 id="tab-<?= $result['order'] ?>">
		
		<?php $prem2 = true; ?>					
			  	  	  <div class="panel panel-success">
			  	  	  
			  	  	   <div class="panel-heading">
			  	  	   	  <label><?= $result['label'] ?></label> 
			  	  	   </div>
			  	  	   
			  	  	   <div class="panel-boby">
 
		                   <table id="data-search-<?= $result['order'] ?>" 
		                   		width="100%" cellspacing="0" 
		                   		class="display nowrap table table-striped table-bordered table-hover" >

								<thead>
								  <tr>

		<?php foreach ( $result['results'][0] as $label => $value ): ?>
			
			<?php if ( substr($label, 0, 1) == $_hide ): ?>
			
									<th class="hidden"><?= $label ?></th>
			
			<?php elseif ( substr($label, 0, 1) == $_link ): ?>
			
									<th><?= substr($label, 1) ?></th>
			<?php else: ?>
									<th><?= $label ?></th>
			
			<?php endif; ?>
			
		<?php endforeach; ?>		                          
		                          </tr>
								</thead>
								
								<tbody>
								  <tr>
								
		<?php foreach ( $result['results'] as $ligne ): ?>
		
			<?php foreach ( $ligne as $label => $value ): ?>
									
				<?php if ( substr($label, 0, 1) == $_hide ): ?>
									
											<td class="hidden"><?= $value ?></td>
				
				<?php elseif ( substr($label, 0, 1) == $_link ): ?>
									
											<td>
						            			<a href="<?= $value ?>" target="blank">
						            			lien
						            			</a>
						            		</td>
				<?php else: ?>
											<td><?= substr($value, 0, 80) ?></td>
				<?php  endif; ?>
				
			<?php endforeach; ?>
									</tr>
		<?php endforeach; ?>
								
								</tbody>
							</table>
 					  		  	  	   
			  	  	   </div>
			  	  	  </div>
					</div>
					
	<?php endforeach; ?>			
						  			 	
	  			</div>
  	  		  </div>
	      </div>
	
<!-- 	</div>       -->
</div>

<script type="text/javascript">

	  <?php foreach ( $targets as $search ): ?>

	    <?php if ($search['mode'] == 'basic' ): ?>

		    $('#data-search-<?= $search['order'] ?>').DataTable( {
		
		    	dom: 'tip',
		        pagingType: 'simple',
		        pageLength: 10,
		        language: {
		            url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/French.json"
		        }
			});
	    <?php elseif ($search['mode'] == 'scroll' ): ?>
			
		    $('#data-search-<?= $search['order'] ?>').DataTable( {
		
		    	dom: 'tp',
		    	scrollX: true,
		        scrollY: '500px',
		        scrollCollapse: true,
		        fixedColumns: true,
		        paging: false,
		        language: {
		            url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/French.json"
		        }
			});
		<?php endif; ?>
		
	  <?php endforeach; ?>

// 		$('.table tbody tr').click(function() {

// 			$(this).parent().children('tr.danger').removeClass('danger');
// 			$(this).addClass('danger');
// 		});

//TEST
// 		$('.nav-tabs').children('li').click(function(){alert('go');});;
// 		$('.table thead tr').children('th:first').click(function(){alert('go');});;
// 		$('.table').DataTable().order([0, 'desc']).draw();
// 		$('.table').DataTable().colReorder.reset();

		$('#search').focusin(function(){

	        $(this).css("background-color", "#FFFFCC");
	        $(this).val('');
	    });
		$('#search').focus();
		
</script>

</body>
</html>