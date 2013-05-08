<?php
require __DIR__.'/../Environment/Bootstrap.php';
require __DIR__.'/../Environment/Alphapup.php';

$alphapup = new Alphapup('Dev',true);
$alphapup->boot();