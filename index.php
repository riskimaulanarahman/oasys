<?php
session_start();
$_SESSION['previous_location'] = 'oasys';
if (preg_match('/MSIE\s(?P<v>\d+)/i', @$_SERVER['HTTP_USER_AGENT'], $B) || preg_match('/Trident/i', @$_SERVER['HTTP_USER_AGENT'], $B)) {
    header("location:/outdatedbrowser");
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
	<title>OASys :: Online Approval System ::. </title>
	<link rel='shortcut icon' href='sign.ico'>
	<meta charset='utf-8'>
	<meta http-equiv="Cache-control" content="no-cache">
	<meta http-equiv='Expires' content='0'>
	<meta http-equiv='Pragma' content='no-cache'>
	<meta content='IE=edge,chrome=1' http-equiv='X-UA-Compatible'>
	<meta name='description' content='Online Approval System' />
	<meta name='keywords' content='online, approval, system, paperless' />
	<meta http-equiv='Content-Type' content='utf-8'>
    <!--
    =========================================================
    * ArchitectUI HTML Theme Dashboard - v1.0.0
    =========================================================
    * Product Page: https://dashboardpack.com
    * Copyright 2019 DashboardPack (https://dashboardpack.com)
    * Licensed under MIT (https://github.com/DashboardPack/architectui-html-theme-free/blob/master/LICENSE)
    =========================================================
    * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
    -->
	<link rel='stylesheet' href='css/style.css' type='text/css'>
	<link href="css/main.css?v=1.0005" rel="stylesheet">
	
	<link rel='stylesheet' href='css/cssload.css' type='text/css'>
	<link rel='stylesheet' href='css/quill.core.css' type='text/css'>
	<link rel='stylesheet' href='css/dx.spa.css' type='text/css'>
	<link rel='stylesheet' href='css/dx.common.css' type='text/css'>
	<link rel='stylesheet' href='css/dx.light.compact.css' type='text/css'>
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/dateschedule.css" rel="stylesheet">
    <!-- tooltip -->
	<link rel='stylesheet' href='css/introjs.min.css' type='text/css'>
	<!-- <link rel='stylesheet' href='css/landing.css' type='text/css'> -->

 </head>
<body class='main page login' ng-app='kduApp' data-ng-controller='mainCtrl as main' style="padding: 0 !important;">
<div ng-hide="isLogin" data-ng-controller='LoginController as main'>

    

    <div class="app-container app-theme-white body-tabs-shadow">
        <div class="app-container">
            <!-- <div class="h-100 bg-plum-plate bg-animation"> -->
            <div class="h-100 bg-animation" style="background-image: url('assets/images/bg1.png') !important; background-repeat: no-repeat !important; background-size: 100% 100% !important;">
                <div class="d-flex h-100 justify-content-center align-items-center">
                    <div class="mx-auto app-login-box col-md-8">
                    <div class="app-logo-inverse mx-auto mb-3"></div>
                        <div class="modal-dialog w-100 mx-auto">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="h5 modal-title text-center">
                                        <h4 class="mt-2">
                                            <div ng-if="loading" class="ball-scale-multiple">
                                                    <div style="background-color: rgb(58, 196, 125);"></div>
                                                    <div style="background-color: rgb(58, 196, 125);"></div>
                                                    <div style="background-color: rgb(58, 196, 125);"></div>
                                            </div>
                                            <div style="font-weight: 900;">Online Approval System</div>
                                        </h4>
                                    </div>
                                    <form name="form" method="post" ng-submit="form.$valid && login()" novalidate>
                                        <div class="form-row">
                                            
                                            <div class="col-md-12">
                                                <div ng-if="error" class="alert alert-danger" ng-bind="error"></div>
                                                <div class="position-relative form-group" ng-class="{ 'has-error': form.$submitted && form.username.$invalid }">
                                                    <input name="username" id="username" placeholder="Username" type="text" class="form-control" ng-model="username" required >
                                                    <div ng-messages="form.$submitted && form.username.$error" class="help-block">
                                                        <div ng-message="required">Username is required</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <!-- <div class="position-relative form-group"> -->
                                                    
                                                    <!-- Show password <input type='checkbox' id='check' /> -->
                                                
                                                    
                                                <!-- </div> -->
                                                <div class="input-group">
                                                    <input name="password" id="password" placeholder="Password" type="password" class="form-control" ng-model="password" required>
                                                    <div class="input-group-append">
                                                        <button id="check" class="btn btn-secondary" style="margin-top:0px !important; margin-bottom:0px !important;"><i id="iconid" class="fa fa-eye"></i></button>
                                                        <button id="nocheck" class="btn btn-secondary" style="margin-top:0px !important; margin-bottom:0px !important;"><i id="iconid" class="fa fa-eye-slash"></i></button>
                                                    </div>
                                                    <div ng-messages="form.$submitted && form.password.$error" class="help-block">
                                                        <div ng-message="required">Password is required</div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            
                                            
                                        </div>
                                        <div class="divider"></div>
                                        <div class="float-right">
                                            <button type="submit" ng-disabled="loading" class="btn btn-success btn-lg" ng-click="login()" >Login</button>
                                        </div>
                                    </div>
                                    <!-- <div class="modal-footer clearfix"> -->
                                        
                                    </form>
                                    
                                <!-- </div> -->
                                <!-- <div class="divider"></div> -->
                                <!-- <div class="col-md-6"> -->
                                    <!-- <a href="/oasys/internalhiring" class="btn-icon btn-shadow btn-outline-2x btn btn-outline-primary" ><i class="fa fa-users btn-icon-wrapper"> </i>Internal Hiring</a> -->
                                <!-- </div> -->

                            </div>
                        </div>
                        <div class="text-center text-white opacity-8 mt-3">KF Planning © <span id="versionapp">v-</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
	<!-- <div class="wrapper">
      <div class='row'>
        <div class='col-lg-12'>
          <div class='brand text-center'>
            <h1>
              <div class='logo-icon'>
                <i class='icon-lock fa fa-lock'></i>
              </div>
            </h1>
          </div>
        </div>
      </div>
      <div class='row'>
        <div class='col-lg-12'>
		 <form name="form" method="post" ng-submit="form.$valid && login()" novalidate>
            <fieldset class='text-center'>
              <legend>Login to your account</legend>
              <div class="form-group" ng-class="{ 'has-error': form.$submitted && form.username.$invalid }">
					<label for="username">Username</label>
					<input type="text" name="username" class="form-control" ng-model="username" required />
					<div ng-messages="form.$submitted && form.username.$error" class="help-block">
						<div ng-message="required">Username is required</div>
					</div>
				</div>
				<div class="form-group" ng-class="{ 'has-error': form.$submitted && form.password.$invalid }">
					<label for="password">Password</label>
					<input type="password" name="password" class="form-control" ng-model="password" required />
					<div ng-messages="form.$submitted && form.password.$error" class="help-block">
						<div ng-message="required">Password is required</div>
					</div>
				</div>
              <div class='form-group text-center'>
				<button type="submit" ng-disabled="loading" class="btn btn-primary" ng-click="login()" >Login</button>
				<img ng-if="loading" src="data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==" />
                <br>
              </div>
			  <div ng-if="error" class="alert alert-danger" ng-bind="error"></div>
            </fieldset>
          </form>
        </div>
	  </div>
	</div>	 -->
</div>
<div ng-show="isLogin">
    <div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">
        <div class="app-header header-shadow">
            <div class="app-header__logo">
                <!-- <div class=""><i class="fas fa-file-signature" style="font-size:28px;"></i> OASys</div> -->
                <div class="logo-src"></div>
                <div class="header__pane ml-auto">
                    <div>
                        <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                            <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="app-header__mobile-menu">
                <div>
                    <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                        <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                    </button>
                </div>
            </div>
            <div class="app-header__menu">
                <span>
                    <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                        <span class="btn-icon-wrapper">
                            <i class="fa fa-ellipsis-v fa-w-6"></i>
                        </span>
                    </button>
                </span>
            </div>    
			<div class="app-header__content">

                <div class="app-header-right">
					<div class="header-dots">
						<div class="dropdown">
                        <button type="button" aria-haspopup="true" aria-expanded="false" data-toggle="dropdown" class="p-0 mr-2 btn btn-link">
                            <span class="icon-wrapper icon-wrapper-alt rounded-circle">
                                <span class="icon-wrapper-bg bg-primary"></span>
                                <i class="nav-link-icon fa fa-eye fa-fw" style="font-size:24px;"></i>
                                <span class="badge badge-dot badge-dot-sm badge-success">Online Users</span>
                            </span>
                        </button>
                        <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu-xl rm-pointers dropdown-menu dropdown-menu-right">
                            <div class="dropdown-menu-header mb-0">
                                <div class="dropdown-menu-header-inner bg-deep-blue">
                                    <div class="menu-header-content text-dark">
                                        <h5 class="menu-header-title">Online Users</h5>
                                        <div tabindex="-1"> currently <span class='badge' ng-bind="users.length"></span> users online</div>
                                    </div>
                                </div>
                            </div>
							<ul class="tabs-animated-shadow tabs-animated nav nav-justified tabs-shadow-bordered p-3">
                            </ul>
                            <div class="tab-content">
                                
                                <div class="tab-pane active" id="tab-events-header" role="tabpanel">
                                    <div class="scroll-area-sm">
                                        <div class="scrollbar-container">
                                            <div class="p-3">
                                                <div class="vertical-without-time vertical-timeline vertical-timeline--animate vertical-timeline--one-column">
													<div ng-repeat="user in users" class="vertical-timeline-item vertical-timeline-element">
                                                        <div><span class="vertical-timeline-element-icon bounce-in"><i ng-class="(user.min<15) ? 'badge badge-dot badge-dot-xl badge-success' : ((user.min<30)?'badge badge-dot badge-dot-xl badge-warning':'badge badge-dot badge-dot-xl badge-danger')" > </i></span>
                                                            <div class="vertical-timeline-element-content bounce-in"><p><b><span ng-bind="user.displayname" class="text-info"></span></b> <i class="fa fa-clock-o fa-fw"></i> <span class="text-success"><em ng-bind="user.min"></em> m <em ng-bind="user.secs"></em> s ago</span></p>
															</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
							<ul class="nav flex-column">
                                <li class="nav-item-divider nav-item"></li>
                                <li class="nav-item-btn text-center nav-item">
                                    <button class="btn-shadow btn-wide btn-pill btn btn-focus btn-sm">View All Users</button>
                                </li>
                            </ul>
                        </div>
                    </div>
					</div>
                    <div class="header-btn-lg pr-0">
                        <div class="widget-content p-0">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="btn-group">
                                        <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                            <i class="fa fa-user" style="font-size:24px;"></i>
                                            <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                        </a>

										<div tabindex="-1" role="menu" aria-hidden="true" class="rm-pointers dropdown-menu-lg dropdown-menu dropdown-menu-right">
											<div class="dropdown-menu-header">
												<div class="dropdown-menu-header-inner bg-info">
													<div class="menu-header-image opacity-2" style="background-image: url('https://demo.dashboardpack.com/architectui-html-pro/assets/images/dropdown-header/city3.jpg');"></div>
													<div class="menu-header-content text-left">
														<div class="widget-content p-0">
															<div class="widget-content-wrapper">
																<div class="widget-content-left mr-3">
																	<!-- <img width="42" class="rounded-circle" src="" alt=""> -->
																</div>
																<div class="widget-content-left">
																	<div class="widget-heading"><strong ng-bind="curUser.firstname"></strong> <strong ng-bind="curUser.lastname"></strong>
																	</div>
																	<div class="widget-subheading opacity-8" ng-bind="curUser.email">
																	</div>
                                                                    <div class="widget-subheading opacity-8">
                                                                        SAPID : <span id="sapid"></span>
																	</div>
																</div>
																<div class="widget-content-right mr-2">
																	<button ng-click="logout()" class="btn-pill btn-shadow btn-shine btn btn-focus">Logout
																	</button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											
											<ul class="nav flex-column">
												<li class="nav-item-divider mb-0 nav-item"></li>
											</ul>
											<div class="grid-menu grid-menu-2col">
												<div class="no-gutters row">
													<div class="col-sm-6">
														<button ng-click="editProfile()" class="btn-icon-vertical btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-info">
															<i class="fas fa-edit icon-gradient bg-plum-plate btn-icon-wrapper mb-2"></i>
															Edit Profile
														</button>
													</div>
													<div class="col-sm-6">
														<button ng-click="appConfig()" class="btn-icon-vertical btn-transition btn-transition-alt pt-2 pb-2 btn btn-outline-success">
															<i class="fas fa-cog icon-gradient bg-deep-blue btn-icon-wrapper mb-2"></i>
															<b>Account Setting</b>
														</button>
													</div>
												</div>
											</div>
											<ul class="nav flex-column">
												<li class="nav-item-divider nav-item">
												</li>
												<li class="nav-item-btn text-center nav-item">
													
												</li>
											</ul>
										</div>
                                    </div>
                                </div>
                                <div class="widget-content-left  ml-3 header-user-info">
                                    <div class="widget-heading">
                                        
                                    </div>
                                    <div class="widget-subheading">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
            </div>
        </div>        
		<div class="ui-theme-settings">
            <button type="button" id="TooltipDemo" class="btn-open-options btn btn-warning">
                <i class="fa fa-cog fa-w-16 fa-spin fa-2x"></i>
            </button>
            <div class="theme-settings__inner">
                <div class="scrollbar-container">
                    <div class="theme-settings__options-wrapper">
                        <h3 class="themeoptions-heading">Layout Options
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <div class="switch has-switch switch-container-class" data-class="fixed-header">
                                                    <div class="switch-animate switch-on">
                                                        <input type="checkbox" checked data-toggle="toggle" data-onstyle="success">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Fixed Header
                                                </div>
                                                <div class="widget-subheading">Makes the header top fixed, always visible!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <div class="switch has-switch switch-container-class" data-class="fixed-sidebar">
                                                    <div class="switch-animate switch-on">
                                                        <input type="checkbox" checked data-toggle="toggle" data-onstyle="success">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Fixed Sidebar
                                                </div>
                                                <div class="widget-subheading">Makes the sidebar left fixed, always visible!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="widget-content p-0">
                                        <div class="widget-content-wrapper">
                                            <div class="widget-content-left mr-3">
                                                <div class="switch has-switch switch-container-class" data-class="fixed-footer">
                                                    <div class="switch-animate switch-off">
                                                        <input type="checkbox" data-toggle="toggle" data-onstyle="success">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="widget-content-left">
                                                <div class="widget-heading">Fixed Footer
                                                </div>
                                                <div class="widget-subheading">Makes the app footer bottom fixed, always visible!
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h3 class="themeoptions-heading">
                            <div>
                                Header Options
                            </div>
                            <button type="button" class="btn-pill btn-shadow btn-wide ml-auto btn btn-focus btn-sm switch-header-cs-class" data-class="">
                                Restore Default
                            </button>
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h5 class="pb-2">Choose Color Scheme
                                    </h5>
                                    <div class="theme-settings-swatches">
                                        <div class="swatch-holder bg-primary switch-header-cs-class" data-class="bg-primary header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-secondary switch-header-cs-class" data-class="bg-secondary header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-success switch-header-cs-class" data-class="bg-success header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-info switch-header-cs-class" data-class="bg-info header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-warning switch-header-cs-class" data-class="bg-warning header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-danger switch-header-cs-class" data-class="bg-danger header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-light switch-header-cs-class" data-class="bg-light header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-dark switch-header-cs-class" data-class="bg-dark header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-focus switch-header-cs-class" data-class="bg-focus header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-alternate switch-header-cs-class" data-class="bg-alternate header-text-light">
                                        </div>
                                        <div class="divider">
                                        </div>
                                        <div class="swatch-holder bg-vicious-stance switch-header-cs-class" data-class="bg-vicious-stance header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-midnight-bloom switch-header-cs-class" data-class="bg-midnight-bloom header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-night-sky switch-header-cs-class" data-class="bg-night-sky header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-slick-carbon switch-header-cs-class" data-class="bg-slick-carbon header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-asteroid switch-header-cs-class" data-class="bg-asteroid header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-royal switch-header-cs-class" data-class="bg-royal header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-warm-flame switch-header-cs-class" data-class="bg-warm-flame header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-night-fade switch-header-cs-class" data-class="bg-night-fade header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-sunny-morning switch-header-cs-class" data-class="bg-sunny-morning header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-tempting-azure switch-header-cs-class" data-class="bg-tempting-azure header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-amy-crisp switch-header-cs-class" data-class="bg-amy-crisp header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-heavy-rain switch-header-cs-class" data-class="bg-heavy-rain header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-mean-fruit switch-header-cs-class" data-class="bg-mean-fruit header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-malibu-beach switch-header-cs-class" data-class="bg-malibu-beach header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-deep-blue switch-header-cs-class" data-class="bg-deep-blue header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-ripe-malin switch-header-cs-class" data-class="bg-ripe-malin header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-arielle-smile switch-header-cs-class" data-class="bg-arielle-smile header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-plum-plate switch-header-cs-class" data-class="bg-plum-plate header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-fisher switch-header-cs-class" data-class="bg-happy-fisher header-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-happy-itmeo switch-header-cs-class" data-class="bg-happy-itmeo header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-mixed-hopes switch-header-cs-class" data-class="bg-mixed-hopes header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-strong-bliss switch-header-cs-class" data-class="bg-strong-bliss header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-grow-early switch-header-cs-class" data-class="bg-grow-early header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-love-kiss switch-header-cs-class" data-class="bg-love-kiss header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-premium-dark switch-header-cs-class" data-class="bg-premium-dark header-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-green switch-header-cs-class" data-class="bg-happy-green header-text-light">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h3 class="themeoptions-heading">
                            <div>Sidebar Options</div>
                            <button type="button" class="btn-pill btn-shadow btn-wide ml-auto btn btn-focus btn-sm switch-sidebar-cs-class" data-class="">
                                Restore Default
                            </button>
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h5 class="pb-2">Choose Color Scheme
                                    </h5>
                                    <div class="theme-settings-swatches">
                                        <div class="swatch-holder bg-primary switch-sidebar-cs-class" data-class="bg-primary sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-secondary switch-sidebar-cs-class" data-class="bg-secondary sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-success switch-sidebar-cs-class" data-class="bg-success sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-info switch-sidebar-cs-class" data-class="bg-info sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-warning switch-sidebar-cs-class" data-class="bg-warning sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-danger switch-sidebar-cs-class" data-class="bg-danger sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-light switch-sidebar-cs-class" data-class="bg-light sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-dark switch-sidebar-cs-class" data-class="bg-dark sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-focus switch-sidebar-cs-class" data-class="bg-focus sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-alternate switch-sidebar-cs-class" data-class="bg-alternate sidebar-text-light">
                                        </div>
                                        <div class="divider">
                                        </div>
                                        <div class="swatch-holder bg-vicious-stance switch-sidebar-cs-class" data-class="bg-vicious-stance sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-midnight-bloom switch-sidebar-cs-class" data-class="bg-midnight-bloom sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-night-sky switch-sidebar-cs-class" data-class="bg-night-sky sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-slick-carbon switch-sidebar-cs-class" data-class="bg-slick-carbon sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-asteroid switch-sidebar-cs-class" data-class="bg-asteroid sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-royal switch-sidebar-cs-class" data-class="bg-royal sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-warm-flame switch-sidebar-cs-class" data-class="bg-warm-flame sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-night-fade switch-sidebar-cs-class" data-class="bg-night-fade sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-sunny-morning switch-sidebar-cs-class" data-class="bg-sunny-morning sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-tempting-azure switch-sidebar-cs-class" data-class="bg-tempting-azure sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-amy-crisp switch-sidebar-cs-class" data-class="bg-amy-crisp sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-heavy-rain switch-sidebar-cs-class" data-class="bg-heavy-rain sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-mean-fruit switch-sidebar-cs-class" data-class="bg-mean-fruit sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-malibu-beach switch-sidebar-cs-class" data-class="bg-malibu-beach sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-deep-blue switch-sidebar-cs-class" data-class="bg-deep-blue sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-ripe-malin switch-sidebar-cs-class" data-class="bg-ripe-malin sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-arielle-smile switch-sidebar-cs-class" data-class="bg-arielle-smile sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-plum-plate switch-sidebar-cs-class" data-class="bg-plum-plate sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-fisher switch-sidebar-cs-class" data-class="bg-happy-fisher sidebar-text-dark">
                                        </div>
                                        <div class="swatch-holder bg-happy-itmeo switch-sidebar-cs-class" data-class="bg-happy-itmeo sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-mixed-hopes switch-sidebar-cs-class" data-class="bg-mixed-hopes sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-strong-bliss switch-sidebar-cs-class" data-class="bg-strong-bliss sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-grow-early switch-sidebar-cs-class" data-class="bg-grow-early sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-love-kiss switch-sidebar-cs-class" data-class="bg-love-kiss sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-premium-dark switch-sidebar-cs-class" data-class="bg-premium-dark sidebar-text-light">
                                        </div>
                                        <div class="swatch-holder bg-happy-green switch-sidebar-cs-class" data-class="bg-happy-green sidebar-text-light">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h3 class="themeoptions-heading">
                            <div>Main Content Options</div>
                            <button type="button" class="btn-pill btn-shadow btn-wide ml-auto active btn btn-focus btn-sm">Restore Default
                            </button>
                        </h3>
                        <div class="p-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <h5 class="pb-2">Page Section Tabs
                                    </h5>
                                    <div class="theme-settings-swatches">
                                        <div role="group" class="mt-2 btn-group">
                                            <button type="button" class="btn-wide btn-shadow btn-primary btn btn-secondary switch-theme-class" data-class="body-tabs-line">
                                                Line
                                            </button>
                                            <button type="button" class="btn-wide btn-shadow btn-primary active btn btn-secondary switch-theme-class" data-class="body-tabs-shadow">
                                                Shadow
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
		<div class="app-main">
			<div class="app-sidebar sidebar-shadow">
				<div class="app-header__logo">
					<div class="logo-src"></div>
					<div class="header__pane ml-auto">
						<div>
							<button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
								<span class="hamburger-box">
									<span class="hamburger-inner"></span>
								</span>
							</button>
						</div>
					</div>
				</div>
				<div class="app-header__mobile-menu">
					<div>
						<button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
							<span class="hamburger-box">
								<span class="hamburger-inner"></span>
							</span>
						</button>
					</div>
				</div>
				<div class="app-header__menu">
					<span>
						<button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
							<span class="btn-icon-wrapper">
								<i class="fa fa-ellipsis-v fa-w-6"></i>
							</span>
						</button>
					</span>
				</div>    
				<div class="scrollbar-sidebar">
					<div class="app-sidebar__inner">
						<ul class="vertical-nav-menu">
							<li class="app-sidebar__heading">Dashboard</li>
							<li ><a href="#!" ><i class='metismenu-icon pe-7s-rocket icon-gradient bg-premium-dark'></i> Dashboard</a></li>
							<li ng-show="isAdmin" class="app-sidebar__heading">Admin Area</li>
							<li ng-show="isAdmin">
								<a href="#"><i class='metismenu-icon pe-7s-users icon-gradient bg-premium-dark'></i>Admin Area<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul>
									<li class="nav-item"><a href="#!/user"  ><i class='fa fa-user'></i> Manage User</a></li>
									<li class="nav-item"><a href="#!/role" class="nav-link" ><i class='fas fa-user-tag'></i> Manage Role</a></li>
									<li class="nav-item"><a href="#!/module" class="nav-link" ><i class='fa fa-toolbox'></i> Manage Module</a></li>
									<li class="nav-item"><a href="#!/useraccess" class="nav-link" ><i class='fa fa-user-lock'></i> Manage User Access</a></li>
								</ul>
							</li>
							<li class="app-sidebar__heading active">Personal Task</li>
							<li ng-class="{'mm-active': (isActive('/dayoff') || isActive('/doapproval'))}">
								<a href="#" ><i class='metismenu-icon pe-7s-umbrella icon-gradient bg-premium-dark'></i>Weekend/PH Coverage<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul ng-class="{'mm-show': (isActive('/dayoff') || isActive('/doapproval'))}">
									<li class="nav-item"><a href="" ng-click="myDayoff()" class="nav-link" ><i class='fa fa-calendar-alt'></i> My Request</a></li>
									<li class="nav-item"><a href="" ng-click="dayoffApproval()" class="nav-link" ><i class='fas fa-marker'></i> My Approval</a></li>
								</ul>
							</li>
							<li ng-class="{'mm-active': (isActive('/spkl') || isActive('/spklapproval') || isActive('/spkltms') || isActive('/spkltmsapproval'))}">
								<a href="#"><i class='metismenu-icon pe-7s-alarm icon-gradient bg-premium-dark'></i>SPKL<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul  ng-class="{'mm-show': (isActive('/spkl') || isActive('/spklapproval') || isActive('/spkltms') || isActive('/spkltmsapproval'))}">
									<li class="nav-item"><a href="" ng-click="mySPKL()" class="nav-link" ><i class='fa fa-calendar-alt'></i> OT Instruction Request</a></li>
									<li class="nav-item"><a href="" ng-click="SPKLApproval()" class="nav-link" ><i class='fas fa-marker'></i> OT Instruction Approval</a></li>
									<li class="nav-item"><a href="" ng-click="myTimesheet()" class="nav-link" ><i class='fa fa-calendar-alt'></i> OT Timesheet Request</a></li>
									<li class="nav-item"><a href="" ng-click="SPKLTMSApproval()" class="nav-link" ><i class='fas fa-marker'></i> OT Timesheet Approval</a></li>
								</ul>
							</li>
							<li ng-class="{'mm-active': (isActive('/tr') || isActive('/trapproval'))}">
								<a href="#" ><i class='metismenu-icon pe-7s-plane icon-gradient bg-premium-dark'></i>Travel Request<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul ng-class="{'mm-show': (isActive('/tr') || isActive('/trapproval'))}">
									<li class="nav-item"><a href="" ng-click="myTR()" class="nav-link" ><i class='fa fa-calendar-alt'></i> My Request</a></li>
									<li class="nav-item"><a href="" ng-click="TRApproval()" class="nav-link" ><i class='fas fa-marker'></i> My Approval</a></li>
								</ul>
							</li>
                            
							<li ng-class="{'mm-active': (isActive('/rfc') || isActive('/rfcapproval'))}" >
								<a href="#"  ><i class='metismenu-icon pe-7s-note2 icon-gradient bg-premium-dark'></i>RFC<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul ng-class="{'mm-show': (isActive('/rfc') || isActive('/rfcapproval'))}">
									<li class="nav-item"><a href="" ng-click="myRFC()" class="nav-link" ><i class='fa fa-calendar-alt'></i> My Request</a></li>
									<li class="nav-item"><a href="" ng-click="RFCApproval()" class="nav-link" ><i class='fas fa-marker'></i> My Approval</a></li>
								</ul>
							</li>

                            <li ng-class="{'mm-active': (isActive('/mmf') || isActive('/mmf30') || isActive('/mmfdetail') || isActive('/mmfapproval') || isActive('/mmf30approval'))}">
								<a href="#"><i class='metismenu-icon pe-7s-tools icon-gradient bg-premium-dark'></i>MMF<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
                                <ul ng-class="{'mm-show': (isActive('/mmf') || isActive('/mmf30') || isActive('/mmfapproval') || isActive('/mmf30approval'))}">
                                
									<!-- <li class="nav-item"><a href="" ng-click="myMMF()" class="nav-link" ><i class='fa fa-calendar-alt'></i> My Request</a></li> -->

                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='fa fa-calendar-alt'></i> My Request
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="myMMF()">
                                                    <i class="metismenu-icon"></i>MMF 28 (Service)
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="myMMF30()">
                                                    <i class="metismenu-icon"></i>MMF 30 (Material)
                                                </a>
                                            </li>
                                            
                                        </ul>
                                    </li>
                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='fas fa-marker'></i> My Approval
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="mmfApproval()">
                                                    <i class="metismenu-icon"></i>MMF 28 (Service)
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="mmf30Approval()">
                                                    <i class="metismenu-icon"></i>MMF 30 (Material)
                                                </a>
                                            </li>
                                            
                                        </ul>
                                    </li>
									<!-- <li class="nav-item"><a href="" ng-click="mmfApproval()" class="nav-link" ><i class='fas fa-marker'></i> My Approval</a></li> -->
								</ul>
                            </li>
                            
                            <li ng-class="{'mm-active': (isActive('/iteie') || isActive('/itimail') || isActive('/itsharefolder') || isActive('/iteieapproval') || isActive('/itimailapproval') || isActive('/itsharefolderapproval'))}">
                            <!-- <li ng-class=""> -->
								<a href="#"><i class='metismenu-icon pe-7s-science icon-gradient bg-premium-dark'></i>IT Approval<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul ng-class="{'mm-show': (isActive('/iteie') || isActive('/itimail') || isActive('/itsharefolder') || isActive('/iteieapproval') || isActive('/itimailapproval') || isActive('/itsharefolderapproval') )}">
                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='fa fa-calendar-alt'></i> My Request
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="myITEIE()">
                                                    <i class="metismenu-icon"></i>Active Directory
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="myITIMAIL()">
                                                    <i class="metismenu-icon"></i>IT Form
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="myITSHAREF()">
                                                    <i class="metismenu-icon"></i>Shared Folder
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='fas fa-marker'></i> My Approval
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                        <li>
                                                <a href="" ng-click="iteieApproval()">
                                                    <i class="metismenu-icon"></i>Active Directory
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="itimailApproval()">
                                                    <i class="metismenu-icon"></i>IT Form
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="itsharefApproval()">
                                                    <i class="metismenu-icon"></i>Shared Folder
                                                </a>
                                            </li>
                                            
                                        </ul>
                                    </li>
								</ul>
							</li>

                            <li ng-class="{'mm-active': (isActive('/advance') || isActive('/advanceapproval') )}">
								<a href="#"><i class='metismenu-icon pe-7s-cash'></i>Advance<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul  ng-class="{'mm-show': (isActive('/advance') || isActive('/advanceapproval') )}">
									<li class="nav-item"><a href="" ng-click="myAdvance()" class="nav-link" ><i class='fa fa-calendar-alt'></i> Adv Related Request</a></li>
									<li class="nav-item"><a href="" ng-click="advanceApproval()" class="nav-link" ><i class='fas fa-marker'></i> Adv Related Approval</a></li>
								</ul>
							</li>

                            <li ng-class="{'mm-active': (isActive('/advancepayment') || isActive('/advancepaymentapproval') || isActive('/advexpense') || isActive('/advexpenseapproval') )}">
								<a href="#"><i class='metismenu-icon pe-7s-cash'></i>Payment<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul  ng-class="{'mm-show': (isActive('/advancepayment') ||  isActive('/advancepaymentapproval') || isActive('/advexpense') || isActive('/advexpenseapproval') )}">
									<li class="nav-item"><a href="" ng-click="myAdvpayment()" class="nav-link" ><i class='fa fa-calendar-alt'></i> Payment Request</a></li>
									<li class="nav-item"><a href="" ng-click="advpaymentApproval()" class="nav-link" ><i class='fas fa-marker'></i> Payment Approval</a></li>
                                    <li class="nav-item"><a href="" ng-click="myAdvexpense()" class="nav-link" ><i class='fa fa-calendar-alt'></i> Expense Request</a></li>
									<li class="nav-item"><a href="" ng-click="advexpenseApproval()" class="nav-link" ><i class='fas fa-marker'></i> Expense Approval</a></li>
								</ul>
							</li>
                            <li class="app-sidebar__heading active" style="color: red;">Coming Soon</li>
                            
                            
							<li class="app-sidebar__heading">Data Master</li>
							<li ng-class="{'mm-active': $route.current.activeTab == 'company'}">
								<a href="#"><i class='metismenu-icon pe-7s-menu icon-gradient bg-premium-dark'></i>Data Master<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul>
									<li class="nav-item"><a href="" ng-click="dataCompany()"  class="nav-link" ><i class='fa fa-building'></i> Data Company</a></li>
									<li class="nav-item"><a href="" ng-click="dataDepartment()"  class="nav-link" ><i class='fa fa-id-badge'></i> Data Department</a></li>
									<li class="nav-item"><a href="" ng-click="dataDivision()"  class="nav-link" ><i class="fa fa-address-card"></i> Data Division</a></li>
									<li class="nav-item"><a href="" ng-click="dataDesignation()"  class="nav-link" ><i class='fa fa-id-card'></i> Data Designation</a></li>
									<li class="nav-item"><a href="" ng-click="dataEmployee()"  class="nav-link" ><i class='fa fa-address-book'></i> Data Employee</a></li>
									<li class="nav-item"><a href="" ng-click="dataInternalhiringmaster()"  class="nav-link" ><i class='fa fa-address-book'></i> Data Internal Hiring</a></li>
									<li class="nav-item"><a href="" ng-click="dataApprover()"  class="nav-link" ><i class='fa fa-id-card'></i> Data Approver</a></li>
									<li class="nav-item"><a href="" ng-click="dataHoliday()"  class="nav-link" ><i class='fa fa-calendar-alt'></i> Data Holiday</a></li>
									<li class="nav-item"><a href="" ng-click="dataRFCActivity()"  class="nav-link" ><i class='fa fa-table'></i> RFC - Activity</a></li>
									<li class="nav-item"><a href="" ng-click="dataSKRate()"  class="nav-link" ><i class='fa fa-table'></i> RFC - SK Rate</a></li>
									<li class="nav-item"><a href="" ng-click="dataContractor()"  class="nav-link" ><i class='fa fa-table'></i> RFC - Contractor</a></li>
								</ul>
							</li>
							<li class="app-sidebar__heading">Report / Summary Data</li>
							<li>
								<a href="#"><i class='metismenu-icon pe-7s-display2 icon-gradient bg-premium-dark'></i>Report<i class="metismenu-state-icon fas pe-7s-angle-down caret-left"></i></a>
								<ul>
									<li class="nav-item"><a href="" ng-click="dataLeave()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Employee Leave</a></li>
									<li class="nav-item"><a href="" ng-click="dataDayoff()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Employee Weekend Cov.</a></li>
									<li class="nav-item"><a href="" ng-click="detailDayoff()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Detail Approved WPHC</a></li>
									<li class="nav-item"><a href="" ng-click="dataTR()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> TR Report</a></li>
                                    <li class="nav-item"><a href="" ng-click="dataRFC()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> RFC Record</a></li>
									<li class="nav-item"><a href="" ng-click="dataSPKL()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Data SPKL</a></li>
									<li class="nav-item"><a href="" ng-click="detailSPKL()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Detail OT Timesheet</a></li>
                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> MMF
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="dataMMF()">
                                                    <i class="metismenu-icon"></i>MMF 28 Report
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="dataMMF30()">
                                                    <i class="metismenu-icon"></i>MMF 30 Report
                                                </a>
                                            </li>
                                            
                                        </ul>
                                    </li>
                                    <li ng-class="{'mm-active': (isActive('/iteiereport') || isActive('/itimailreport') || isActive('/itsharefolderreport')) }">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> IT
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul ng-class="{'mm-show': (isActive('/iteiereport') || isActive('/itimailreport') || isActive('/itsharefolderreport') )}" class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="dataITEIE()">
                                                    <i class="metismenu-icon"></i>Active Directory
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="dataITIMAIL()">
                                                    <i class="metismenu-icon"></i>IT Form
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="dataITSHAREF()">
                                                    <i class="metismenu-icon"></i>Share Folder
                                                </a>
                                            </li>
                                            
                                        </ul>
                                    </li>
                            
									<li class="nav-item"><a href="" ng-click="dataAdvance()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Advance</a></li>

                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Payment
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="dataAdvPayment()">
                                                    <i class="metismenu-icon"></i>Payment Request
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="dataAdvExpense()">
                                                    <i class="metismenu-icon"></i>Expense Claim
                                                </a>
                                            </li>
                                        
                                        </ul>
                                    </li>
                                    <li class="">
                                        <a href="#" aria-expanded="false">
                                            <i class="metismenu-icon"></i><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Internal Hiring
                                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                        </a>
                                        <ul class="mm-collapse" style="height: 7.04px;">
                                            <li>
                                                <a href="" ng-click="dataInternalhiringmaster()">
                                                    <i class="metismenu-icon"></i>Requirement
                                                </a>
                                            </li>
                                            <li>
                                                <a href="" ng-click="dataInternalhiringreport()">
                                                    <i class="metismenu-icon"></i>Applyment
                                                </a>
                                            </li>
                                        
                                        </ul>
                                    </li>
									<!-- <li class="nav-item"><a href="" ng-click="dataInternalhiringreport()"  class="nav-link" ><i class='pe-7s-news-paper icon-gradient bg-premium-dark'></i> Internal Hiring</a></li> -->

									
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>    
			
			<div class="app-main__outer">
				<div class="app-main__inner" class="col-lg-12 col-xl-12 card mb-12 " > 
				<div ng-view></div>
					<!--<div ng:include="template"  ></div>-->               
				</div>
				<div class="app-wrapper-footer" >
					<div class="app-footer">
						<div class="app-footer__inner">
							<div class="app-footer-left">
								<ul class="nav">
									<li class="nav-item">
										<a href="javascript:void(0);" class="nav-link">
											<span>Developed by KF Strategic Planning</span>
										</a>
									</li>
								</ul>
							</div>
							<div class="app-footer-right">
								<ul class="nav">
									<li class="nav-item">

									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
        </div>
    </div>
	</div>

	<script language="JavaScript" src="js/lib/jquery-3.3.1.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/polyfill.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/modernizr.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/jszip.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/angular.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/angular-route.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/angular-ui-router.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/ocLazyLoad.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/ngStorage.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/angular-messages.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/quill.min.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/dx.all.js" type="text/javascript"></script>
	<script language="JavaScript" src="js/lib/FileSaver.min.js" type="text/javascript"></script>
    <!-- <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/globalize/0.1.1/globalize.min.js"></script> -->
    <!-- <script src="https://unpkg.com/devextreme-aspnet-data/js/dx.aspnet.data.js"></script> -->
    
	<script language="JavaScript" src="js/app.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/directive.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/services.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/filter.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/factory.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/controllers/maincontroller.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/controllers/login.js?v=5.50" type="text/javascript"></script>
	<script language="JavaScript" src="js/script.js?v=5.50" type="text/javascript"></script>
    <!-- <script type="text/javascript" src="https://demo.dashboardpack.com/architectui-html-pro/assets/scripts/main.d810cf0ae7f39f28f336.js"></script> -->
    
	<script type="text/javascript" src="assets/scripts/main.js"></script>
	<!-- <script type="text/javascript" src="assets/scripts/particles.js"></script> -->
	<!-- <script type="text/javascript" src="assets/scripts/landing.js"></script> -->
    <script language="JavaScript" src="js/lib/intro.min.js" type="text/javascript"></script>

    <script>
    
    </script>
</body>
</html>
<script type='text/javascript'>
    $(document).ready(function(){
        $('#nocheck').hide();
        $('#check').click(function(e){
            e.preventDefault();
            // $("#iconid").toggle();
            // $('#check').attr('id', 'nocheck');
            $('#password').attr('type', 'text');
            $('#nocheck').show();
            $('#check').hide();

            // $('#iconid').attr('class', 'fa fa-eye-slash');
            // alert($(this).is(':checked'));
            // $(this).is(':checked') ? $('#password').attr('type', 'text') : $('#password').attr('type', 'password');
        });
        $('#nocheck').click(function(e){
            e.preventDefault();
            // $('#nocheck').attr('id', 'check');
            $('#password').attr('type', 'password');
            $('#nocheck').hide();
            $('#check').show();
            // $('#iconid').attr('class', 'fa fa-eye');
        });
    });
</script>