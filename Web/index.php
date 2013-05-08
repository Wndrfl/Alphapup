<?php
require __DIR__.'/../Environment/Bootstrap.php';
require __DIR__.'/../Environment/Alphapup.php';
date_default_timezone_set('UTC');
$alphapup = new Alphapup('Dev',true);
$alphapup->boot();