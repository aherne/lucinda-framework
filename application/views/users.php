<import file="header"/>

<input type="button" value="Synchronize" onClick="synchronize()" class="addButton">
<script>var users = ${json_encode(${data.users})};</script>
<div class="user-table">
    <!-- user-table__header -->
    <div class="user-table__header">
        <div>ID</div>
        <div>NAME</div>
        <div>EMAIL</div>
        <div>DEPARTMENTS</div>
    </div>
    <!-- /user-table__header -->

    <!-- user-table__content -->
    <div class="user-table__content">
    	<standard:foreach var="${data.users}" value="userInfo">
        	<div class="user-table__row">
        		<div>${userInfo.user.id}</div>
        		<div>${userInfo.user.name}</div>
        		<div>${userInfo.user.email}</div>
        		<div>
            		<standard:foreach var="${userInfo.departments}" key="departmentId" value="departmentInfo">
            		${data.departments.${departmentId}}: ${data.levels.${departmentInfo.level}} : group#${departmentInfo.group}<br/>
            		</standard:foreach>
        		</div>
        	</div>
        </standard:foreach>
    </div>
    <!-- /user-table__content -->

</div>
<!-- /popup -->
<import file="footer"/>