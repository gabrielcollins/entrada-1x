#!/usr/local/zend/bin/php
<?php

require_once dirname(__FILE__) . "/../test-helper.php";
require_once "/Users/zuikerd/pear/share/pear/PHPUnit.php";

$acl = new Zend_Acl();

$roleGuest = new Zend_Acl_Role('guest');

$acl->addRole($roleGuest);

$acl->addRole(new Zend_Acl_Role('staff'), $roleGuest);

$acl->addRole(new Zend_Acl_Role('editor'), 'staff');

$acl->addRole(new Zend_Acl_Role('administrator'));

$acl->deny('guest', null, array('edit', 'submit', 'revise'));

$acl->allow($roleGuest, null, 'view');

$acl->allow('staff', null, array('edit', 'submit', 'revise'));

$acl->allow('editor', null, array('publish', 'archive', 'delete'));

$acl->allow('administrator');

echo $acl->isAllowed('staff', null, 'edit') ? "\nallowed\n" : "\ndenied\n";
?>
