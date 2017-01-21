<html>
    <head>
        <title>CMS: Login</title>
    	<link rel="stylesheet" href="/public/css/login.css">
    </head>
	<body>         
		<standard:if condition="${data.status.isError}==true">
        <div id="status" class="error">${data.status.message}</div>
        <standard:else/>
        <div id="status" class="success">${data.status.message}</div>
    	</standard:if>
    	<div id="login_container">
    		<div id="logo">
            	<img src="/public/img/logo.png" width="245" height="67">
            </div>
            
            <form action="" method="POST">
       
            	<input type="email" name="email" required placeholder="Email" class ="inputElement email"/><br/>
            	<input type="password" name="password" required placeholder="Password" class="inputElement password"/><br/>
            	<div><input type="checkbox" name="remember_me" value="1" class="rememberMeCheck"/> <span class="rememberMeText">Remember me</span></div><br/>
            	<input type="submit" value="Login" class="button"/>
            </form>
        </div>
	</body>
</html>