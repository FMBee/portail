
<!-- Modal -->
    <div class="modal fade" id="searchModal" role="dialog">
      <div class="modal-dialog" style="width:1024px;">
 
	<!-- Modal content-->
		 <div class="modal-content">
		 
			<div class="modal-header">
			
			  <button type="button" class="close" data-dismiss="modal">&times;</button>
			  <h4 class="modal-title">
                <label>Résultats de la recherche pour [<b><?= $seek ?></b>]</label>
	          </h4>
	        </div>
	        
	        <div class="modal-body">
	        
  	  		  <div class="col-lg-12">

				<ul class="nav nav-tabs">
				
	<?php if ( count($results) == 0 ): ?>
	
				  <span>Aucun résultat</span>
	<?php endif; ?>
				
	<?php foreach ( $results as $result ): ?>
	
				  <li role="presentation">
				  	<a href="#tab-<?= $result['order'] ?>" data-toggle="tab">
				  	<?= $result['short'] .' (' .count($result['results']) .')' ?>
				  	</a>
				  </li>

	<?php endforeach; ?>			

				</ul>
	  	 
	  			<div class="tab-content">

	<?php foreach ( $results as $result ): ?>
		
		<?php if (! isset($prem) ): ?>
					<div class="tab-pane fade in active"
		<?php else: ?> 
					<div class="tab-pane fade"
		<?php endif; ?>
						 id="tab-<?= $result['order'] ?>">
		
		<?php $prem = true; ?>					
			  	  	  <div class="panel panel-success">
			  	  	  
			  	  	   <div class="panel-heading">
			  	  	   	  <label><?= $result['label'] ?></label>
			  	  	   </div>
			  	  	   
			  	  	   <div class="panel-boby">
 
		                 <div class="dataTable_wrapper">

		                   <table id="data-search-<?= $result['order'] ?>" width="100%" 
		                   		class="nowrap table table-striped table-bordered table-hover" >

								<thead>
								  <tr>

		<?php foreach ( $result['results'][0] as $label => $value ): ?>
			
			<?php if ( substr($label, 0, 1) == '_' ): ?>
			
									<th class="hidden"><?= $label ?></th>
			
			<?php elseif ( substr($label, 0, 1) == ':' ): ?>
			
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
									
				<?php if ( substr($label, 0, 1) == '_' ): ?>
									
											<td class="hidden"><?= $value ?></td>
				
				<?php elseif ( substr($label, 0, 1) == ':' ): ?>
									
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
					</div>
					
	<?php endforeach; ?>			
						  			 	
	  			</div>
  	  		  </div>
						        
	        </div>
	        
	        <div class="modal-footer">
	          <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
	        </div>
	      </div>
	      
	   </div>
	</div>
	
	
