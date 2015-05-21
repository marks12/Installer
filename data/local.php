<?php
/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * @NOTE: This file is ignored from Git by default with the .gitignore included
 * in ZendSkeletonApplication. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */


$dbParams = array(
		'database'  => '%s',
		'username'  => '%s',
		'port'		=> '3306',//3306
		'password'  => '%s',
		'hostname'  => 'localhost',//localhost
		'encoding'  => 'utf8',
// buffer_results - only for mysqli buffered queries, skip for others
		'options' => array('buffer_results' => true)
);

return array(
		'service_manager' => array(
				'factories' => array(
						'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
							$adapter = new BjyProfiler\Db\Adapter\ProfilingAdapter(array(
									'driver'    => 'pdo',
									'dsn'       => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'].';charset='.$dbParams['encoding'],
									'database'  => $dbParams['database'],
									'username'  => $dbParams['username'],
									'password'  => $dbParams['password'],
									'hostname'  => $dbParams['hostname'],
							));

							if (php_sapi_name() == 'cli') {
								$logger = new Zend\Log\Logger();
								// write queries profiling info to stdout in CLI mode
								$writer = new Zend\Log\Writer\Stream('php://output');
								$logger->addWriter($writer, Zend\Log\Logger::DEBUG);
								$adapter->setProfiler(new BjyProfiler\Db\Profiler\LoggingProfiler($logger));
							} else {
								$adapter->setProfiler(new BjyProfiler\Db\Profiler\Profiler());
							}
							if (isset($dbParams['options']) && is_array($dbParams['options'])) {
								$options = $dbParams['options'];
							} else {
								$options = array();
							}
							$adapter->injectProfilingStatementPrototype($options);
							return $adapter;
						},
				),
		),
		'doctrine' => array(
				'connection' => array(
						// default connection name
						'orm_default' => array(
								'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
								'params' => array(
										'host'		=> $dbParams['hostname'],
										'port'		=> $dbParams['port'],
										'user'		=> $dbParams['username'],
										'password'	=> $dbParams['password'],
										'dbname'	=> $dbParams['database'],
										'charset'	=> $dbParams['encoding'],
										'mapping_types' => array (
												'enum' => "string",
											),
										'driverOptions' => array(
												1002 => 'SET NAMES utf8'
										),
								),
								'doctrineTypeMappings' => array('enum'=>'string'),
						),
				),
				'configuration' => array(
						// Configuration for service `doctrine.configuration.orm_default` service 'orm_default' => array(
						// Generate proxies automatically (turn off for production)
						'generate_proxies' => true,
						'types' => array('enum'=>'\Doctrine\DBAL\Types\StringType') // добавили в этот раз
				),
		),
		
);

