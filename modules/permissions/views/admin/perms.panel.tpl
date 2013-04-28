
    <table class="table table-hover table-bordered table-condensed">
        <tr>
            <th>&nbsp;</th>
            <th>Value</th>
        </tr>
        <!-- BEGIN nodes -->
        <tr>
            <td><strong>{nodes.NAME}</strong><br />{nodes.DESC}</td>
            <td width="70%"><ul class="nav nav-pills">
                <!-- BEGIN values -->
                <li class="{nodes.values.SELECTED}">
                    <label for="perm[{nodes.KEY}]_{nodes.values.COUNT}"><input type="radio" value="{nodes.values.VALUE_KEY}" id="perm[{nodes.KEY}]_{nodes.values.COUNT}" name="perm[{nodes.KEY}]"> {nodes.values.VALUE_NAME}</label>
                </li>
                <!-- END values -->
            </ul></td>
        </tr>
        <!-- END nodes -->
    </table>

