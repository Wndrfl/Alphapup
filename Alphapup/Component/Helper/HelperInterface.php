<?php
namespace Alphapup\Component\Helper;

use Alphapup\Component\View\View;

interface HelperInterface {
	public function name();
	public function setView(View $view);
}