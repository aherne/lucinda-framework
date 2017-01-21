<html>
	<head>
		<title>CMS: ${data.pageTitle}</title>
    	<link rel="stylesheet" href="/public/css/default.css">
    	<link rel="stylesheet" href="/public/css/jquery-ui.css">
    	<link rel="stylesheet" href="/public/css/${data.view}.css">
		<script type="text/javascript" src="/public/js/vendors/jquery-2.2.1.min.js"></script>
		<script type="text/javascript" src="/public/js/vendors/jquery.nicescroll.min.js"></script>
		<script type="text/javascript" src="/public/js/vendors/jquery-ui.min.js"></script>
    	<script type="text/javascript" src="/public/js/ajax.js"></script>
    	<script type="text/javascript" src="/public/js/models.js"></script>
    	<script type="text/javascript" src="/public/js/${data.view}.js" defer="defer"></script>
    	
	</head>
	<body>
	
    <!-- site -->
    <div class="site">

        <!-- site__header -->
        <header class="site__header">

            <!-- site__header-layout -->
            <div class="site__header-layout">

                <!-- logo -->
                <h1 class="logo">
                    <img src="/public/img/logo.png" width="245" height="67" alt="">
                </h1>
                <!-- /logo -->

                <!-- site__header-menu -->
                <nav class="site__header-menu">
					<ul id="menu">
						<standard:foreach var="${data.menu}" key="title" value="panels">
						<li data-element="${title}" onMouseOver="showSubMenu('${title}')" onMouseOut="hideSubMenu()" class="li_main">
							<span class="menuTitle">${title} <span class="arrow">&nbsp;</span></span>
							<ul class="submenu ${title}">
								<standard:foreach var="${panels}" value="panel">
								<li class="sub_li"><a href="/${panel.url}">${panel.name}</a></li>
								</standard:foreach>
							</ul>
						</li>
						</standard:foreach>
					</ul>
				</nav>
                <!-- /site__header-menu -->

                <!-- site__header-user -->
                <div class="site__header-user">

                    <!-- site__header-user-info -->
                    <div class="site__header-user-info">
                        <span>Happy work day</span>,
                        <h2 class="site__header-user-name">${data.user.name}</h2>
                    </div>
                    <!-- /site__header-user-info -->

                    <!-- site__header-user -->
                    <button class="site__header-user-settings" onClick="goTo('index')"></button>
                    <!-- site__header-user -->

                    <!-- site__header-user -->
                    <button class="site__header-user-exit" onClick="goTo('logout')"></button>
                    <!-- site__header-user -->

                </div>
                <!-- /site__header-user -->

            </div>
            <!-- /site__header-layout -->

        </header>
        <!-- /site__header -->

        <!-- site__content -->
        <div class="site__content">
            <h2 class="site__title">
                <span class="site__content-inner" id="title">${data.pageTitle}</span>
            </h2>

            <div id="status"></div>
            <script>setStatus('${data.status.message}','${data.status.isError}')</script>
	