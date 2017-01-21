<import file="header"/>
	<div class="user-table">
		<div class="user-table__content">
        	<div class="user-table__row">
        		<div>My name:</div>
        		<div><input type="text" id="name" class="field site__field" disabled name="name" value="${data.user.name}"></div>
        	</div>
        	<div class="user-table__row">
        		<div>My email:</div>
        		<div><input type="text" id="email" class="field site__field" disabled value="${data.user.email}"></div>
        	</div>
        </div>
    </div>
</form>
<import file="footer"/>