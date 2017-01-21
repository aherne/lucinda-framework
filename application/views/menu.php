<import file="header"/>

<form action="" method="POST">
<div class="user-table">
<!-- user-table__header -->
<div class="user-table__header">
<div>PANEL</div>
<div>PLACEHOLDER</div>
</div>
<!-- /user-table__header -->

<!-- user-table__content -->
<div class="user-table__content popup-form">
<standard:foreach var="${data.panels}" key="panel_id" value="panel_name">

<div class="user-table__row">
	<div>
		${panel_name}
	</div>
	<div>
		<standard:if condition="isset(${data.menus.${panel_id}})">
		<input type="text" name="menu[${panel_id}]" value="${data.menus.${panel_id}.holder}"/>
		<standard:else>
		<input type="text" name="menu[${panel_id}]" value=""/>
		</standard:if>
	</div>
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
