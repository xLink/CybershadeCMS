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
            <td>{user.EMAIL}</td>
            <td>{user.DATE_REGISTERED}</td>
            <td><div class="username">{user.ROLE}</div></td>
            <td>
                <span class="label label-{user.STATUS_LABEL}">{user.STATUS}</span>
            </td>
            <td>
                <div class="btn-group">
                    <a href="{user.ACTION_EDIT}" class="btn btn-small"><i class="icon-edit"></i> Edit</a>
                    <button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" class="">Ban User</a></li>
                        <li><a href="#">View Profile</a></li>
                        <li><a href="#">Manage Permissions</a></li>
                    </ul>
                </div>
            </td>
        </tr>
        <!-- END user -->
  </tbody>
</table>