<?php

return array(
// Your other stuff
		'bjyauthorize' => array(
// Using the authentication identity provider, which basically reads the roles from the auth service's identity
				'unauthorized_strategy' => 'BjyAuthorize\View\UnauthorizedStrategy',
// 				'unauthorized_strategy' => 'BjyAuthorize\View\RedirectionStrategy',
				'identity_provider' => 'BjyAuthorize\Provider\Identity\ZfcUserZendDb',
				'ldap_role_key' => 'memberof',
				'role_providers' => array(
						'BjyAuthorize\Provider\Role\Config' => array(
								'guest' => array(),
								'user' => array(
									'children'=>array(
										'admin' => array(),
										'user' => array(),
									),
								),
						),
				),
				'guards' => array(
						'BjyAuthorize\Guard\Controller' => array(
								array(
										'controller' => 'Installer\Controller\Installer',
										'action' => array('configure'),
										'roles' => array('guest','user','admin'),
								),
								array(
										'controller' => 'TsvUsers\Controller\ConsoleTsvUsers',
										'action' => array('cdrau'),
										'roles' => array('guest','user','admin'),
								),
								array(
										'controller' => 'zfcuser',
										'action' => array('index'),
										'roles' => array('guest','user','admin'),
								),
								array(
										'controller' => 'zfcuser',
										'action' => array('authenticate', 'register'),
										'roles' => array('guest'),
								),
								array(
										'controller' => 'zfcuser',
										'action' => array('login',),
										'roles' => array('guest','user','admin'),
								),
								array(
										'controller' => 'zfcuser',
										'action' => array('logout'),
										'roles' => array('user','admin'),
								),
								array(
										'controller' => 'Application\Controller\Index',
										'roles' => array('user','admin','guest'),
								),
								array(
										'controller' => 'News\Controller\News',
										'roles' => array('user','admin','guest'),
								),
								array(
										'controller' => 'ZfcAdmin\Controller\AdminController',
										'roles'	=> array('admin'),
								),
								array(
										'controller' => 'TsvDirectory\Controller\TsvDirectory',
										'roles'	=> array('admin'),
								),
								array(
										'controller' => 'TsvNews\Controller\TsvNews',
										'roles'	=> array('admin'),
								),
								array(
										'controller' => 'OneRangCatalog\Controller\OneRangCatalog',
										'roles'	=> array('admin'),
								),
								array(
										'controller' => 'ZFTool\Controller\Module',
										'roles'	=> array('admin','guest','user'),
								),
								array(
										'controller' => 'ZFTool\Controller\Info',
										'roles'	=> array('admin','guest','user'),
								),
								array(
										'controller' => 'ZFTool\Controller\Config',
										'roles'	=> array('admin','guest','user'),
								),
								array(
										'controller' => 'ZFTool\Controller\Diagnostics',
										'roles'	=> array('admin','guest','user'),
								),
						),
				),
		),
);