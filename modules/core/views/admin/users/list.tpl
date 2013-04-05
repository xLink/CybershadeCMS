<table class="table table-hover">
	<thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
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
            <td class="center">{user.EMAIL}</td>
            <td class="center">{user.DATE_REGISTERED}</td>
            <td class="center">{user.ROLE}</td>
            <td class="center">
                <span class="label label-{user.STATUS_LABEL}">{user.STATUS}</span>
            </td>
            <td class="center">
                <div class="btn-group">
                    <a href="{user.ACTION_EDIT}" class="btn btn-small"><i class="icon-edit"></i> Edit</a>
                    <button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="">Ban User</a></li>
                        <li><a href="#"></a></li>
                        <li><a href="#"></a></li>
                    </ul>
                </div>
            </td>
        </tr>
        <!-- END user -->
  </tbody>
</table>