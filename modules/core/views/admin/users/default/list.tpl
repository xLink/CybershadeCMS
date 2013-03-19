<table class="table table-striped table-bordered bootstrap-datatable datatable">
	<thead>
        <tr>
            <th>Username</th>
            <th>Date registered</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
	</thead>   
	<tbody>
        <!-- BEGIN user -->
        <tr>
            <td>{user.NAME}</td>
            <td class="center">{user.DATE_REGISTERED}</td>
            <td class="center">{user.ROLE}</td>
            <td class="center">
                <span class="label label-success">{user.STATUS}</span>
            </td>
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
        <!-- END user -->
  </tbody>
</table>