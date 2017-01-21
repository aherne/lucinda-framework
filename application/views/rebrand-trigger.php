<import file="header"/>

<form action="" method="POST">
<input type="button" value="Scan HTACCESS" data-popup="update" onClick="scan()" class="addButton">
<div class="user-table">
<!-- user-table__header -->
<div class="user-table__header">
<div>TABLE</div>
<div>PAGE</div>
<div>PARAMETER</div>
</div>
<!-- /user-table__header -->

<!-- user-table__content -->
<div class="user-table__content">
<standard:foreach var="${data.triggers}" value="info">
<div class="user-table__row">
<div>
	<select class="tables" name="tables[${info.id}]">
	<option value="0">Choose a table...</option>
	<standard:foreach var="${data.tables}" key="id" value="name">
	<standard:if condition="${id}==${info.table}">
	<option value="${id}" selected>${str_replace("_"," ",${name})}</option>
	<standard:else>
	<option value="${id}">${str_replace("_"," ",${name})}</option>
	</standard:if>
	</standard:foreach>
	</select>
</div>
<div>${info.page}</div>
<div>${info.parameter}</div>
</div>
</standard:foreach>

		<!-- user-table__row -->
		<div class="user-table__row">
			<!-- user-table__fieldset -->
			<div class="user-table__fieldset user-table__fieldset_footer locker locker-master">
				<button type="submit" class="btn btn_4"><span>save</span></button>
				<a href="#" class="btn btn_4 btn_file"></a>
			</div>
			<!-- /user-table__fieldset -->
        	<div></div>

		</div>
		<!-- /user-table__row -->
</div>
<!-- /user-table__content -->

</div>
</form>

<import file="footer"/>
