<table class="table table-striped">
  <thead>
	  <tr>
		  <th></th>
		  <th>Link Name</th>
		  <th>Link Path</th>                                 
		  <th>Actions</th>                                          
	  </tr>
  </thead>   
  <tbody id="sortable">
  	<!-- BEGIN link -->
        <tr class="ui-state-default" data-id="{link.ID}">
            <td style="text-align: center;"><i class="fa-icon-resize-vertical"></i></td>
            <td>{link.LABEL}</td>
            <td class="center">{link.URL}</td>
            <td class="center">
                <a class="btn btn-success" href="#">
                	<i class="icon-zoom-in icon-white"></i>  
                </a>
                <a class="btn btn-info" href="#">
                	<i class="icon-edit icon-white"></i>  
                </a>
                <a class="btn btn-danger" href="#">
                	<i class="icon-trash icon-white"></i> 
                </a>
            </td>
        </tr>                               
        <!-- END link -->
  </tbody>
</table>

<script type="text/javascript">
// Damn jquery, this doesn't work..
	(function(){
		$( "#sortable" ).sortable({
			update: function( event, ui ) {
				console.log( ui.item );
			}    
		});
	    $( "#sortable" ).disableSelection();
	});
</script>