<table class="table table-striped table-bordered">
	<thead>
        <tr>
            <th>Module Name</th>
            <th>Version</th>
            <th>Hash</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
	</thead>   
	<tbody>
        <!-- BEGIN module -->
        <tr>
            <td>{module.NAME}</td>
            <td class="center">{module.VERSION}</td>
            <td class="center">{module.HASH}</td>
            <td class="center">
                <span class="label label-{module.STATUS_ICON}">{module.STATUS}</span>
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
        <!-- END module -->
  </tbody>
</table>