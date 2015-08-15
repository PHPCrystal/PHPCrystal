<?php

$this->setEnv('test');

$this->openSection('phpcrystal.phpcrystal.database');
	
	$this->set('dbname', 'phpcrystal_test_db');

$this->closeSection();
